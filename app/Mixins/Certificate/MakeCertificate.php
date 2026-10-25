<?php

namespace App\Mixins\Certificate;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\UserMeta;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class MakeCertificate
{
    /** @var bool */
    private $inlineCertificate = false;

    public function showCertificateByType($certificate, bool $inline = false)
    {
        $this->inlineCertificate = $inline;

        // Prefer QIEC bilingual JPG template when available.
        if ($this->qiecTemplateAvailable()) {
            return $this->renderQiecCertificate($certificate);
        }

        if ($certificate->type == "quiz") {
            $quizResult = $certificate->quizzesResult;

            if (!empty($quizResult)) {
                return $this->makeQuizCertificate($quizResult);
            }

            abort(404);
        } else if ($certificate->type == "course") {
            return $this->makeCourseCertificate($certificate);
        } else if ($certificate->type == "bundle") {
            return $this->makeBundleCertificate($certificate);
        }
    }

    public static function formatCertificateNumber(int $id): string
    {
        $prefix = trim((string) (getCertificateMainSettings('certificate_id') ?: 'QEC'));
        if ($prefix === '' || strtoupper($prefix) === 'CR') {
            $prefix = 'QEC';
        }

        return strtoupper($prefix) . '-' . str_pad((string) intdiv($id, 1000), 4, '0', STR_PAD_LEFT)
            . '-' . str_pad((string) ($id % 1000), 3, '0', STR_PAD_LEFT);
    }

    public static function parseCertificateId($raw): ?int
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        // Strip brackets / decorative wrappers printed on the certificate: [ QEC-0000-027 ]
        $raw = trim($raw, " \t\n\r\0\x0B[]");
        $raw = preg_replace('/\s+/', '', $raw) ?: $raw;
        $raw = strtoupper($raw);

        if (ctype_digit($raw)) {
            return (int) $raw;
        }

        // QEC-0000-028 or CR-0000-028
        if (preg_match('/^[A-Z]+-(\d+)-(\d+)$/', $raw, $m)) {
            return ((int) $m[1]) * 1000 + (int) $m[2];
        }

        if (preg_match('/(\d+)$/', $raw, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Absolute URL for certificate QR / deep links (must be phone-reachable).
     * Local APP_URL hosts are upgraded to production so scans open a real page.
     */
    public static function certificateValidationUrl(?string $certificateNumber = null): string
    {
        $base = rtrim((string) config('app.certificate_public_url', config('app.url')), '/');
        $host = (string) (parse_url($base, PHP_URL_HOST) ?: '');

        // Only rewrite unresolvable-from-phone hosts. Keep LAN IPs / explicit public URLs as-is.
        $isUnreachableFromPhone = $host === ''
            || preg_match('/^(localhost|127\.0\.0\.1|.*\.local)$/i', $host);

        if ($isUnreachableFromPhone) {
            $base = 'https://training.qiec.sa';
        }

        $path = '/certificate_validation';
        if ($certificateNumber !== null && trim($certificateNumber) !== '') {
            $path .= '?certificate_id=' . urlencode(trim($certificateNumber));
        }

        return $base . $path;
    }

    /**
     * Canonical QIEC bilingual template (tracked under resources/ — public/assets is gitignored).
     */
    public function qiecTemplatePath(): ?string
    {
        $candidates = [
            resource_path('certificate/certficate_qr.jpg'), // canonical QR template (filename as provided)
            resource_path('certificate/certificate_qr.jpg'),
            public_path('assets/panel_v1/img/certificate_qr.jpg'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function qiecTemplateAvailable(): bool
    {
        return $this->qiecTemplatePath() !== null;
    }

    /**
     * Keep a public copy for <img> previews (deploy may only ship resources/).
     */
    public function ensurePublicQiecTemplate(): ?string
    {
        $src = $this->qiecTemplatePath();
        if ($src === null) {
            return null;
        }

        $dst = public_path('assets/panel_v1/img/certificate_qr.jpg');
        if (!is_file($dst) || filesize($dst) !== filesize($src)) {
            $dir = dirname($dst);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @copy($src, $dst);
        }

        // Drop legacy non-QR template copies if present.
        foreach ([
            public_path('assets/panel_v1/img/certificate_new.jpg'),
            resource_path('certificate/certificate_new.jpg'),
        ] as $legacy) {
            if (is_file($legacy)) {
                @unlink($legacy);
            }
        }

        return is_file($dst) ? $dst : $src;
    }

    public function qiecTemplatePublicUrl(): ?string
    {
        if ($this->ensurePublicQiecTemplate() === null) {
            return null;
        }

        return asset('assets/panel_v1/img/certificate_qr.jpg');
    }

    public function buildQiecCertificateData($certificate): array
    {
        $certificate->loadMissing(['student.userMetas', 'webinar.translations', 'webinar.teacher', 'quiz.webinar.translations', 'quiz.webinar.teacher', 'bundle.translations', 'bundle.teacher']);

        $meta = [];
        if (!empty($certificate->meta)) {
            $meta = is_array($certificate->meta) ? $certificate->meta : (json_decode((string) $certificate->meta, true) ?: []);
        }

        $student = $certificate->student;
        $traineeName = (string) ($student->full_name ?? '');
        $course = null;
        $durationMinutes = null;

        if (($certificate->type ?? '') === 'course') {
            $course = $certificate->webinar;
        } elseif (($certificate->type ?? '') === 'quiz') {
            $course = $certificate->quiz->webinar ?? null;
        } elseif (($certificate->type ?? '') === 'bundle') {
            $course = $certificate->bundle;
        }

        $titleAr = '';
        $titleEn = '';
        if (!empty($course)) {
            try {
                $titleAr = (string) (($course->translate('ar')->title ?? null) ?: ($course->title ?? ''));
            } catch (\Throwable $e) {
                $titleAr = (string) ($course->title ?? '');
            }
            try {
                $titleEn = (string) (($course->translate('en')->title ?? null) ?: $titleAr);
            } catch (\Throwable $e) {
                $titleEn = $titleAr;
            }
            $durationMinutes = $course->duration ?? null;
        }
        if ($titleAr === '' && !empty($certificate->quiz)) {
            $titleAr = (string) ($certificate->quiz->title ?? 'اختبار');
            $titleEn = $titleAr;
        }

        $hours = '00';
        if (!empty($durationMinutes) && (int) $durationMinutes > 0) {
            $hours = (string) max(1, (int) ceil(((int) $durationMinutes) / 60));
        }

        $issueTs = (int) ($certificate->created_at ?? time());
        if ($issueTs <= 0 || $issueTs > 2000000000) {
            $issueTs = time();
        }
        $issueDate = date('d/m/Y', $issueTs);

        $startDate = $issueDate;
        $endDate = $issueDate;
        if (!empty($course?->start_date)) {
            $startDate = date('d/m/Y', (int) $course->start_date);
            $endTs = (int) $course->start_date + ((int) ($durationMinutes ?: 0) * 60);
            $endDate = date('d/m/Y', $endTs > (int) $course->start_date ? $endTs : (int) $course->start_date);
        }

        $settings = function_exists('getCertificateMainSettings') ? (getCertificateMainSettings() ?: []) : [];
        if (!is_array($settings)) {
            $settings = [];
        }
        $cachedDefaults = cache()->get('qiec_certificate_defaults', []);
        if (!is_array($cachedDefaults)) {
            $cachedDefaults = [];
        }

        $defaultOfficer = (string) ($cachedDefaults['officer_name'] ?? $settings['officer_name'] ?? $settings['certificate_officer_name'] ?? 'مسؤول التدريب والاعتماد');
        $defaultDirector = (string) ($cachedDefaults['director_name'] ?? $settings['director_name'] ?? $settings['certificate_director_name'] ?? 'مدير المركز');
        $defaultAcc = (string) ($cachedDefaults['accreditation_number'] ?? $settings['accreditation_number'] ?? $settings['certificate_accreditation'] ?? 'QIEC-ACC-001');
        $defaultAccreditedByAr = (string) ($cachedDefaults['accredited_by_ar'] ?? $settings['accredited_by_ar'] ?? 'مركز الجودة والتميز');
        $defaultAccreditedByEn = (string) ($cachedDefaults['accredited_by_en'] ?? $settings['accredited_by_en'] ?? 'Quality & Excellence Center');

        $data = [
            'certificate_number' => self::formatCertificateNumber((int) $certificate->id),
            'trainee_name' => $traineeName,
            'trainee_name_ar' => $traineeName,
            'trainee_name_en' => $traineeName,
            'course_title' => $titleAr ?: $titleEn,
            'course_title_ar' => $titleAr ?: $titleEn,
            'course_title_en' => $titleEn ?: $titleAr,
            'hours' => str_pad($hours, 2, '0', STR_PAD_LEFT),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'issue_date' => $issueDate,
            'accreditation_number' => $defaultAcc,
            'accredited_by_ar' => $defaultAccreditedByAr,
            'accredited_by_en' => $defaultAccreditedByEn,
            'officer_name' => $defaultOfficer,
            'director_name' => $defaultDirector,
            'certificate_id' => (int) $certificate->id,
            'type' => (string) ($certificate->type ?? 'course'),
        ];

        // Per-certificate form overrides
        foreach ([
            'trainee_name_ar', 'trainee_name_en', 'course_title_ar', 'course_title_en',
            'hours', 'start_date', 'end_date', 'issue_date',
            'accreditation_number', 'accredited_by_ar', 'accredited_by_en', 'officer_name', 'director_name',
        ] as $key) {
            if (isset($meta[$key]) && trim((string) $meta[$key]) !== '') {
                $data[$key] = is_numeric($meta[$key]) && $key === 'hours'
                    ? str_pad((string) ((int) $meta[$key]), 2, '0', STR_PAD_LEFT)
                    : (string) $meta[$key];
            }
        }
        $data['trainee_name'] = $data['trainee_name_ar'] ?: $data['trainee_name_en'];
        $data['course_title'] = $data['course_title_ar'] ?: $data['course_title_en'];

        // QR — public validation URL (not localhost) so phone scans open the live page
        $data['qr_url'] = self::certificateValidationUrl($data['certificate_number']);
        $data['qr_png'] = $this->buildQiecQrPngBinary($data['qr_url'], 200);
        $data['qr_html'] = !empty($data['qr_png'])
            ? '<img src="data:image/png;base64,' . base64_encode($data['qr_png']) . '" width="96" height="96" alt="QR" />'
            : '';

        return $data;
    }

    /**
     * Build a scannable PNG QR for $url.
     * Prefers SimpleQr+Imagick; otherwise Bacon matrix rendered with GD (no quiet-zone trim).
     */
    private function buildQiecQrPngBinary(string $url, int $size = 200): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        try {
            $png = (string) QrCode::format('png')->size($size)->margin(1)->generate($url);
            if ($png !== '' && strncmp($png, "\x89PNG", 4) === 0) {
                return $png;
            }
        } catch (\Throwable $e) {
            // Imagick often missing on XAMPP — fall through to GD
        }

        try {
            $qrCode = \BaconQrCode\Encoder\Encoder::encode(
                $url,
                \BaconQrCode\Common\ErrorCorrectionLevel::M()
            );
            $matrix = $qrCode->getMatrix();
            $modules = $matrix->getWidth();
            if ($modules < 1) {
                return null;
            }

            // Keep Bacon's full matrix (includes quiet zone) — trimming breaks scanners
            $scale = max(2, (int) floor($size / $modules));
            $imgW = $scale * $modules;
            $im = imagecreatetruecolor($imgW, $imgW);
            if ($im === false) {
                return null;
            }
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, $imgW - 1, $imgW - 1, $white);

            for ($y = 0; $y < $modules; $y++) {
                for ($x = 0; $x < $modules; $x++) {
                    // Bacon ByteMatrix: 1 = dark module
                    if ((int) $matrix->get($x, $y) === 1) {
                        imagefilledrectangle(
                            $im,
                            $x * $scale,
                            $y * $scale,
                            ($x + 1) * $scale - 1,
                            ($y + 1) * $scale - 1,
                            $black
                        );
                    }
                }
            }

            ob_start();
            imagepng($im);
            $png = ob_get_clean();
            imagedestroy($im);

            return is_string($png) && $png !== '' ? $png : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function renderQiecCertificate($certificate)
    {
        $data = $this->buildQiecCertificateData($certificate);

        try {
            $pngBinary = $this->composeQiecCertificatePng($data);
            $fileBase = 'QIEC-certificate-' . ($certificate->id ?? 'x');
            $dataUri = 'data:image/png;base64,' . base64_encode($pngBinary);

            // One landscape page = the filled certificate.jpg image only (points ≈ 1280×905 @ 96dpi).
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
                @page{margin:0;size:960pt 679pt;}
                *{margin:0;padding:0;}
                html,body{width:960pt;height:679pt;overflow:hidden;background:#fff;}
                img{display:block;width:960pt;height:679pt;border:0;}
            </style></head><body><img src="' . $dataUri . '" alt="certificate"></body></html>';

            $pdf = Pdf::loadHTML($html)
                ->setPaper([0, 0, 960, 679])
                ->setOption('isRemoteEnabled', true)
                ->setOption('chroot', public_path());

            if ($this->inlineCertificate) {
                return $pdf->stream($fileBase . '.pdf');
            }

            return $pdf->download($fileBase . '.pdf');
        } catch (\Throwable $e) {
            // Fallback: HTML overlay on the same JPG, still one page.
            $bgPath = $this->qiecTemplatePath();
            if ($bgPath === null) {
                throw $e;
            }
            // Embed as data-URI so DomPDF does not depend on public/assets deploy.
            $backgroundUrl = 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($bgPath));
            $html = (string) view('panel_v1.certificates.qiec-template', [
                'data' => $data,
                'backgroundUrl' => $backgroundUrl,
                'width' => 1280,
                'height' => 905,
            ]);

            return $this->localQiecCertificateDownload($certificate, $html);
        }
    }

    /**
     * Draw dynamic fields onto certficate_qr.jpg (1280×905) — pixel-perfect matching of template.
     * Director signature is printed on the template; only dynamic values + QR are overlaid.
     */
    private function composeQiecCertificatePng(array $data): string
    {
        $bgPath = $this->qiecTemplatePath();
        if ($bgPath === null) {
            throw new \RuntimeException('QIEC certificate template image is missing.');
        }
        $img = Image::make($bgPath)->resize(1280, 905);

        // Value slot fill color. Uncomment red line to debug value boxes again.
        // $paper = '#FF0000';
        $paper = '#FAF9F4';
        $ink = '#103E36';

        $fill = function (int $x, int $y, int $w, int $h) use ($img, $paper) {
            $img->rectangle($x, $y, $x + $w, $y + $h, function ($draw) use ($paper) {
                $draw->background($paper);
            });
        };

        $fontEn = public_path('assets/default/fonts/Montserrat-Medium.ttf');
        $fontArBold = public_path('assets/default/fonts/vazir/Vazir-Bold.ttf');
        $fontArMed = public_path('assets/default/fonts/vazir/Vazir-Medium.ttf');
        if (!is_file($fontArBold)) {
            $fontArBold = public_path('assets/admin/fonts/vazir/Vazir-Bold.ttf');
        }
        if (!is_file($fontArMed)) {
            $fontArMed = $fontArBold;
        }
        if (!is_file($fontEn)) {
            $fontEn = $fontArMed;
        }

        // Cream wipe for leftover template ink (keeps TEMP red only on real value boxes)
        $wipeCream = function (int $x, int $y, int $w, int $h) use ($img) {
            $img->rectangle($x, $y, $x + $w, $y + $h, function ($draw) {
                $draw->background('#FAF9F4');
            });
        };

        $textWidth = function (string $text, int $size) use ($fontEn): int {
            if ($text === '' || !is_file($fontEn) || !function_exists('imagettfbbox')) {
                return max(40, (int) (strlen($text) * $size * 0.55));
            }
            $bbox = @imagettfbbox($size, 0, $fontEn, $text);
            if (!is_array($bbox)) {
                return max(40, (int) (strlen($text) * $size * 0.55));
            }

            return max(20, abs((int) $bbox[2] - (int) $bbox[0]));
        };

        $arabic = null;
        try {
            if (class_exists(\I18N_Arabic::class) || class_exists('I18N_Arabic')) {
                $arabic = new \I18N_Arabic('Glyphs');
            }
        } catch (\Throwable $e) {
            $arabic = null;
        }

        $hasAr = static function (string $text): bool {
            return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
        };

        $shapeAr = function (string $text) use ($arabic, $hasAr): string {
            $text = trim($text);
            if ($text === '' || empty($arabic) || !$hasAr($text)) {
                return $text;
            }
            try {
                return (string) $arabic->utf8Glyphs($text);
            } catch (\Throwable $e) {
                return $text;
            }
        };

        // Latin-only writer — never run Arabic glyph shaping (avoids ][ reversal)
        $writeLtr = function (string $text, int $x, int $y, int $size, string $align = 'left') use ($img, $fontEn, $ink) {
            $text = trim($text);
            if ($text === '') {
                return;
            }
            $img->text($text, $x, $y, function ($font) use ($size, $align, $fontEn, $ink) {
                if (is_file($fontEn)) {
                    $font->file($fontEn);
                }
                $font->size($size);
                $font->color($ink);
                $font->align($align);
                $font->valign('top');
            });
        };

        // Arabic-only writer — shape Arabic alone (never mix with [ ] Latin brackets)
        $writeAr = function (string $text, int $x, int $y, int $size, string $align = 'right', bool $bold = true) use ($img, $fontArBold, $fontArMed, $shapeAr, $ink) {
            $text = trim($text);
            if ($text === '') {
                return;
            }
            $text = $shapeAr($text);
            $fontFile = $bold ? $fontArBold : $fontArMed;
            $img->text($text, $x, $y, function ($font) use ($size, $align, $fontFile, $ink) {
                if (is_file($fontFile)) {
                    $font->file($fontFile);
                }
                $font->size($size);
                $font->color($ink);
                $font->align($align);
                $font->valign('top');
            });
        };

        $write = function (string $text, int $x, int $y, int $size, string $align = 'left', bool $bold = false) use ($writeLtr, $writeAr, $hasAr) {
            if ($hasAr($text)) {
                $writeAr($text, $x, $y, $size, $align, $bold);
            } else {
                $writeLtr($text, $x, $y, $size, $align);
            }
        };

        $fitSize = function (string $text, int $defaultSize, int $maxChars, int $minSize = 11): int {
            $len = mb_strlen(trim($text));
            if ($len <= $maxChars) {
                return $defaultSize;
            }
            $ratio = $maxChars / $len;
            return max($minSize, (int) round($defaultSize * $ratio));
        };

        $num = (string) ($data['certificate_number'] ?? '');
        $nameEn = (string) ($data['trainee_name_en'] ?? '');
        $nameAr = (string) ($data['trainee_name_ar'] ?? '');
        $courseEn = (string) ($data['course_title_en'] ?? '');
        $courseAr = (string) ($data['course_title_ar'] ?? '');
        $hours = str_pad((string) ((int) ($data['hours'] ?? 0)), 2, '0', STR_PAD_LEFT);
        $acc = (string) ($data['accreditation_number'] ?? '');
        $accreditedByEn = (string) ($data['accredited_by_en'] ?? 'Quality & Excellence Center');
        $accreditedByAr = (string) ($data['accredited_by_ar'] ?? 'مركز الجودة والتميز');

        // Dates → "12  /  10  /  2026" (extra horizontal spacing around slashes)
        $spaceDate = static function (string $value): string {
            $value = trim($value);
            if ($value === '') {
                return '';
            }
            if (preg_match('/^(\d{1,4})\s*[\/.\-]\s*(\d{1,4})\s*[\/.\-]\s*(\d{1,4})$/', $value, $m)) {
                return $m[1] . '  /  ' . $m[2] . '  /  ' . $m[3];
            }

            return $value;
        };
        $start = $spaceDate((string) ($data['start_date'] ?? ''));
        $end = $spaceDate((string) ($data['end_date'] ?? ''));
        $issue = $spaceDate((string) ($data['issue_date'] ?? ''));

        // Latin bracket — never pass through Arabic shaping
        $bracket = static function (string $value): string {
            $value = trim($value);
            return $value === '' ? '' : '[ ' . $value . ' ]';
        };

        // 1. Certificate numbers — same baseline both sides; Latin LTR only
        $fill(186, 284, 138, 22);
        $writeLtr($bracket($num), 188, 288, 11, 'left');

        // AR: placeholder left of "رقم الشهادة" — left-align Latin so brackets stay [ ]
        $fill(990, 284, 120, 22);
        $writeLtr($bracket($num), 992, 288, 11, 'left');

        // 2. Trainee names
        $fill(70, 428, 350, 32);
        $nameEnDraw = $hasAr($nameEn) ? $nameEn : strtoupper($nameEn);
        $write($nameEnDraw, 74, 434, $fitSize($nameEnDraw, 18, 26, 12), 'left', true);

        $fill(848, 424, 380, 34);
        $writeAr($nameAr, 1205, 432, $fitSize($nameAr, 18, 26, 12), 'right', true);

        // 3. Course titles
        $fill(70, 524, 200, 30);
        $write($courseEn, 74, 530, $fitSize($courseEn, 14, 22, 10), 'left', true);

        $fill(960, 524, 268, 32);
        $writeAr($courseAr, 1205, 530, $fitSize($courseAr, 14, 28, 10), 'right', true);

        // 4. Meta rows EN (left) — Latin LTR
        $fill(148, 590, 40, 20);
        $writeLtr($bracket($hours), 150, 594, 11, 'left');

        // Keep "Course dates:" intact; wider wipe for spaced dates
        $fill(178, 616, 360, 28);
        $writeLtr($bracket($start) . '  –  ' . $bracket($end), 182, 624, 11, 'left');

        // Keep "Issue date:" intact
        $fill(158, 648, 200, 28);
        $writeLtr($bracket($issue), 162, 656, 11, 'left');

        // Accredited by EN — stop well before QR (~576); no brackets (saves width)
        $fill(248, 676, 320, 34); // ends ~568
        $writeLtr($accreditedByEn, 252, 688, $fitSize($accreditedByEn, 12, 48, 10), 'left');

        $fill(250, 714, 148, 24);
        $writeLtr($acc, 254, 720, 11, 'left');

        // 5. Meta rows AR (right)
        // Hours — right-align flush to end of "عدد الساعات التدريبية:" (colon ~1055);
        // keep printed "ساعة" to the left; red $paper is TEMP test
        $arHours = $bracket($hours);
        $fill(1008, 586, 44, 26); // ends 1052 — wipe [00] slot only
        $writeLtr($arHours, 1052, 594, 11, 'right');

        // AR meta rows — even ~32px rhythm (JPG centers ~629/661/693/724); wipes must not overlap
        // 1) فترة الدورة — wipe full JPG [date] slot (incl. both brackets), then redraw
        $arDateLine = $bracket($start) . ' – ' . $bracket($end);
        $arDateSize = 10;
        $arDateRight = 1135; // flush left of "فترة الدورة:"
        $arDateW = (int) round($textWidth($arDateLine, $arDateSize) * 1.05) + 6;
        $wipeCream(820, 612, $arDateRight - 820, 28); // clear JPG [ ] ghosts through colon
        $fill($arDateRight - $arDateW, 614, $arDateW, 24);
        $writeLtr($arDateLine, $arDateRight, 622, $arDateSize, 'right');

        // 2) تاريخ الإصدار — wipe full JPG [date] slot (incl. both brackets), then redraw
        $arIssue = $bracket($issue);
        $arIssueSize = 11;
        $arIssueRight = 1118; // flush left of "تاريخ الإصدار:"
        $arIssueW = (int) round($textWidth($arIssue, $arIssueSize) * 1.05) + 6;
        $wipeCream(960, 644, $arIssueRight - 960, 28); // clear JPG [ ] ghosts through colon
        $fill($arIssueRight - $arIssueW, 646, $arIssueW, 24);
        $writeLtr($arIssue, $arIssueRight, 654, $arIssueSize, 'right');

        // 3) جهة الاعتماد — label includes colon in same Vazir draw (aligned baseline)
        $accreditedByAr = preg_replace('/^\s*الدورة\s+معتمدة\s+من\s*:?\s*/u', '', $accreditedByAr) ?: $accreditedByAr;
        $wipeCream(820, 676, 420, 28); // 676–704
        $arByLabelRight = 1215;
        $writeAr('الدورة معتمدة من:', $arByLabelRight, 684, 12, 'right', true);
        $arByLabelLeft = 1090;
        for ($sx = 900; $sx <= $arByLabelRight; $sx++) {
            for ($sy = 682; $sy <= 698; $sy++) {
                $sc = $img->pickColor($sx, $sy, 'array');
                if (($sc[0] + $sc[1] + $sc[2]) < 300) {
                    $arByLabelLeft = $sx;
                    break 2;
                }
            }
        }
        // Gap after colon so alef (ا) of the org name does not look like `|` next to `:`
        $arByValRight = $arByLabelLeft - 16;
        $arByValSize = $fitSize($accreditedByAr, 12, 36, 10);
        $writeAr($accreditedByAr, $arByValRight, 686, $arByValSize, 'right', false);

        // 4) رقم الاعتماد — label includes colon in same Vazir draw
        $wipeCream(960, 708, 280, 34); // 708–742
        $arAccLabelRight = 1215;
        $writeAr('رقم الاعتماد:', $arAccLabelRight, 718, 13, 'right', true);
        $arAccLabelLeft = 1150;
        for ($sx = 1050; $sx <= $arAccLabelRight; $sx++) {
            for ($sy = 716; $sy <= 730; $sy++) {
                $sc = $img->pickColor($sx, $sy, 'array');
                if (($sc[0] + $sc[1] + $sc[2]) < 300) {
                    $arAccLabelLeft = $sx;
                    break 2;
                }
            }
        }
        $arAccIdRight = $arAccLabelLeft - 14;
        $arAccIdSize = 11;
        $arAccIdW = $textWidth($acc, $arAccIdSize) + 4;
        $fill($arAccIdRight - $arAccIdW, 716, $arAccIdW, 22);
        $writeLtr($acc, $arAccIdRight, 718, $arAccIdSize, 'right');

        // 6. QR — true center of dashed box on template (581–696 × 590–702)
        $boxL = 581;
        $boxT = 590;
        $boxR = 696;
        $boxB = 702;
        $boxW = $boxR - $boxL;
        $boxH = $boxB - $boxT;
        $qrSize = 84;
        $qrX = (int) round(($boxL + $boxR - $qrSize) / 2);
        $qrY = (int) round(($boxT + $boxB - $qrSize) / 2);
        // Clear interior only — keep dashed border intact
        $fill($boxL + 6, $boxT + 6, $boxW - 12, $boxH - 12);

        $qrBinary = $data['qr_png'] ?? null;
        if (empty($qrBinary) && !empty($data['qr_url'])) {
            $qrBinary = $this->buildQiecQrPngBinary((string) $data['qr_url'], 200);
        }
        if (!empty($qrBinary)) {
            try {
                $qrImg = Image::make($qrBinary)->resize($qrSize, $qrSize);
                $img->insert($qrImg, 'top-left', $qrX, $qrY);
            } catch (\Throwable $e) {
                // leave paper fill if QR insert fails
            }
        }

        return (string) $img->encode('png');
    }


    private function localQiecCertificateDownload($certificate, string $html)
    {
        $fileBase = 'QIEC-certificate-' . ($certificate->id ?? 'x');
        $html = $this->prepareCertificateHtmlForPdf($html, 1280, 905);

        try {
            $pdf = Pdf::loadHTML($html)
                ->setPaper([0, 0, 1280, 905])
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('isFontSubsettingEnabled', true)
                ->setOption('defaultFont', 'DejaVu Sans')
                ->setOption('chroot', public_path());

            if ($this->inlineCertificate) {
                return $pdf->stream($fileBase . '.pdf');
            }

            return $pdf->download($fileBase . '.pdf');
        } catch (\Throwable $e) {
            return response($html)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('Content-Disposition', ($this->inlineCertificate ? 'inline' : 'attachment') . '; filename="' . $fileBase . '.html"');
        }
    }

    public function makeQuizCertificate($quizResult)
    {
        $template = CertificateTemplate::where('status', 'publish')
            ->where('type', 'quiz')
            ->first();

        if (!empty($template)) {
            $quiz = $quizResult->quiz;
            $user = $quizResult->user;

            $userCertificate = $this->saveQuizCertificate($user, $quiz, $quizResult);

            $body = $this->makeBody(
                $template,
                $userCertificate,
                $user,
                $template->body,
                $this->resolveCourseTitle($quiz->webinar ?? null) ?: ($quiz->webinar ? $quiz->webinar->title : '-'),
                $quizResult->user_grade,
                $quiz->webinar->teacher->id,
                $quiz->webinar->teacher->full_name,
                $quiz->webinar->duration);

            $data = [
                'body' => $body
            ];

            $html = (string)view()->make('admin.certificates.create_template.show_certificate', $data);
            $apiResponse = $this->sendToApi($userCertificate, $html);
            if ($apiResponse instanceof \Illuminate\Http\Response
                || $apiResponse instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
                || $apiResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                return $apiResponse;
            }

            // Local fallback when certificate image API is unavailable
            return $this->localCertificateDownload($userCertificate, $html);
        }

        abort(404);
    }

    public function saveQuizCertificate($user, $quiz, $quizResult)
    {
        $certificate = Certificate::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->where('quiz_result_id', $quizResult->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $data = [
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'quiz_result_id' => $quizResult->id,
            'user_grade' => $quizResult->user_grade,
            'type' => 'quiz',
            'created_at' => $quizResult->created_at,
        ];

        if (!empty($certificate)) {
            $certificate->update($data);
        } else {
            $certificate = Certificate::create($data);

            $notifyOptions = [
                '[c.title]' => $quiz->webinar ? $quiz->webinar->title : '-',
            ];
            sendNotification('new_certificate', $notifyOptions, $user->id);
        }

        return $certificate;
    }

    private function makeBody($template, $userCertificate, $user, $body, $courseTitle = null, $userGrade = null, $teacherId = null, $teacherFullName = null, $duration = null)
    {
        $platformName = getGeneralSettings("site_name") ?: 'QIEC';

        $replacements = [
            '[student]' => (string) ($user->full_name ?? ''),
            '[student_name]' => (string) ($user->full_name ?? ''),
            '[platform_name]' => (string) $platformName,
            '[course]' => (string) ($courseTitle ?? ''),
            '[course_name]' => (string) ($courseTitle ?? ''),
            '[grade]' => (string) ($userGrade ?? ''),
            '[certificate_id]' => (string) ($userCertificate->id ?? ''),
            '[date]' => dateTimeFormat($userCertificate->created_at, 'j M Y | H:i') ?: date('Y/m/d'),
            '[instructor_name]' => (string) ($teacherFullName ?? ''),
            '[duration]' => (string) ($duration ?? ''),
        ];

        foreach ($replacements as $search => $replace) {
            $body = str_replace($search, $replace, $body);
        }

        $qrCode = $this->makeQrCode($template, $userCertificate);

        if (!empty($qrCode)) {
            $body = str_replace('[qr_code]', $qrCode, $body);
        }

        $instructorSignatureImg = '';
        if (!empty($teacherId)) {
            $instructorSignature = UserMeta::query()->where('user_id', $teacherId)
                ->where('name', 'signature')
                ->first();
            $instructorSignatureImg = (!empty($instructorSignature) and !empty($instructorSignature->value)) ? url($instructorSignature->value) : null;

            if (!empty($instructorSignatureImg)) {
                $instructorSignatureImg = "<img src='{$instructorSignatureImg}' style='max-width: 100%; max-height: 100%'/>";
            } else {
                $instructorSignatureImg = '';
            }
        }

        $body = str_replace('[instructor_signature]', $instructorSignatureImg, $body);

        $userCertificateAdditional = $user->userMetas->where('name', 'certificate_additional')->first();
        $userCertificateAdditionalValue = !empty($userCertificateAdditional) ? (string) $userCertificateAdditional->value : '';
        $body = str_replace('[user_certificate_additional]', $userCertificateAdditionalValue, $body);

        return $body;
    }

    /**
     * Prefer a readable course title (ar then en) for certificates.
     */
    private function resolveCourseTitle($course): string
    {
        if (empty($course)) {
            return '';
        }

        foreach (['ar', 'en', app()->getLocale()] as $locale) {
            try {
                $translated = $course->translate($locale);
                if (!empty($translated?->title)) {
                    return (string) $translated->title;
                }
            } catch (\Throwable $e) {
            }
        }

        return (string) ($course->title ?? '');
    }

    /**
     * Local downloadable certificate when HTML→Image API is unavailable.
     */
    private function localCertificateDownload($certificate, string $html)
    {
        $fileBase = 'certificate-' . ($certificate->id ?? 'course');
        $html = $this->prepareCertificateHtmlForPdf($html);

        try {
            $pdf = Pdf::loadHTML($html)
                ->setPaper([0, 0, 930, 600])
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('isFontSubsettingEnabled', true)
                ->setOption('defaultFont', 'DejaVu Sans')
                ->setOption('chroot', public_path());

            if ($this->inlineCertificate) {
                return $pdf->stream($fileBase . '.pdf');
            }

            return $pdf->download($fileBase . '.pdf');
        } catch (\Throwable $e) {
            return response($html)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('Content-Disposition', ($this->inlineCertificate ? 'inline' : 'attachment') . '; filename="' . $fileBase . '.html"');
        }
    }

    /**
     * Make certificate HTML DomPDF-safe: local/data-uri images, Arabic glyphs, Unicode fonts.
     */
    private function prepareCertificateHtmlForPdf(string $html, int $width = 930, int $height = 600): string
    {
        $html = $this->rewriteCertificateAssetUrls($html);

        // Arabic shaping for course/student names (skip tags/attributes)
        try {
            if (class_exists(\I18N_Arabic::class) || class_exists('I18N_Arabic')) {
                $arabic = new \I18N_Arabic('Glyphs');
                $html = preg_replace_callback(
                    '/>([^<]*[\x{0600}-\x{06FF}][^<]*)</u',
                    function ($matches) use ($arabic) {
                        $text = $matches[1];
                        if (trim($text) === '') {
                            return $matches[0];
                        }

                        return '>' . $arabic->utf8Glyphs($text) . '<';
                    },
                    $html
                ) ?? $html;
            }
        } catch (\Throwable $e) {
        }

        $vazir = public_path('assets/default/fonts/vazir/Vazir-Medium.ttf');
        $extraCss = 'body{margin:0;padding:0;}'
            . '.certificate-template-container{width:' . $width . 'px;height:' . $height . 'px;position:relative;background-repeat:no-repeat;background-size:100% 100%;border:0;}'
            . '.certificate-template-container .draggable-element{position:absolute !important;display:block;white-space:pre-wrap;}'
            . '.certificate-template-container img{max-width:100%;max-height:100%;}'
            . '.sheet{width:' . $width . 'px;height:' . $height . 'px;}';

        if (is_file($vazir)) {
            $fontUrl = $this->pathToFileUrl($vazir);
            $extraCss = '@font-face{font-family:"VazirCert";src:url("' . $fontUrl . '") format("truetype");}'
                . '*{font-family:"VazirCert", DejaVu Sans, sans-serif !important;}'
                . $extraCss;
        } else {
            $extraCss = '*{font-family:DejaVu Sans, sans-serif !important;}' . $extraCss;
        }

        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', '<style>' . $extraCss . '</style></head>', $html, 1) ?? $html;
        } else {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $extraCss . '</style></head><body>' . $html . '</body></html>';
        }

        return $html;
    }

    private function rewriteCertificateAssetUrls(string $html): string
    {
        // background-image / css url(...)
        $html = preg_replace_callback('/url\((["\']?)([^)\'"]+)\1\)/i', function ($m) {
            $dataUri = $this->assetToDataUri($m[2]);

            return $dataUri ? ('url("' . $dataUri . '")') : $m[0];
        }, $html) ?? $html;

        // <img src="...">
        $html = preg_replace_callback('/(<img\b[^>]*\bsrc=)(["\'])([^"\']+)\2/i', function ($m) {
            $dataUri = $this->assetToDataUri($m[3]);

            return $dataUri ? ($m[1] . $m[2] . $dataUri . $m[2]) : $m[0];
        }, $html) ?? $html;

        return $html;
    }

    private function assetToDataUri(string $src): ?string
    {
        $src = html_entity_decode(trim($src), ENT_QUOTES);
        if ($src === '' || str_starts_with($src, 'data:')) {
            return $src !== '' ? $src : null;
        }

        $path = $this->resolvePublicAssetPath($src);
        if (!$path || !is_file($path)) {
            return null;
        }

        $mime = @mime_content_type($path) ?: 'image/png';
        if (!str_starts_with($mime, 'image/')) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function resolvePublicAssetPath(string $src): ?string
    {
        $src = trim($src);
        if (preg_match('#^https?://[^/]+(/store/.+)$#i', $src, $m)) {
            $src = $m[1];
        }

        if (str_starts_with($src, 'file://')) {
            $path = urldecode(preg_replace('#^file:///#i', '', $src));
            if (PHP_OS_FAMILY === 'Windows' && preg_match('#^[A-Za-z]:/#', $path) === 0 && preg_match('#^[A-Za-z]:\\\\#', $path) === 0) {
                // keep as-is
            }
            return is_file($path) ? $path : null;
        }

        if (str_starts_with($src, '/')) {
            $path = public_path($src);
            return is_file($path) ? $path : null;
        }

        if (is_file($src)) {
            return $src;
        }

        $path = public_path('/' . ltrim($src, '/'));
        return is_file($path) ? $path : null;
    }

    private function pathToFileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        if (!str_starts_with($normalized, '/')) {
            $normalized = '/' . $normalized;
        }

        return 'file://' . $normalized;
    }

    private function makeQrCode($template, $certificate = null)
    {
        $size = 128;
        $elements = $template->elements;

        if (!empty($elements) and !empty($elements['qr_code']) and !empty($elements['qr_code']['image_size'])) {
            $size = (int) $elements['qr_code']['image_size'];
        }

        $url = self::certificateValidationUrl(
            !empty($certificate?->id) ? self::formatCertificateNumber((int) $certificate->id) : null
        );

        try {
            $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size($size)->generate($url);

            return '<img src="data:image/png;base64,' . base64_encode($png) . '" width="' . $size . '" height="' . $size . '" alt="QR" />';
        } catch (\Throwable $e) {
            return QrCode::size($size)->generate($url);
        }
    }

    private function makeImage($certificateTemplate, $body)
    {
        $img = Image::make(public_path($certificateTemplate->image));


        if ($certificateTemplate->rtl) {
            $Arabic = new \I18N_Arabic('Glyphs');
            $body = $Arabic->utf8Glyphs($body);
        }

        $img->text($body, $certificateTemplate->position_x, $certificateTemplate->position_y, function ($font) use ($certificateTemplate) {
            $font->file($certificateTemplate->rtl ? public_path('assets/default/fonts/vazir/Vazir-Medium.ttf') : public_path('assets/default/fonts/Montserrat-Medium.ttf'));
            $font->size($certificateTemplate->font_size);
            $font->color($certificateTemplate->text_color);
            $font->align($certificateTemplate->rtl ? 'right' : 'left');
        });

        return $img;
    }

    public function makeCourseCertificate($certificate)
    {

        $template = CertificateTemplate::where('status', 'publish')
            ->where('type', 'course')
            ->first();

        $course = $certificate->webinar;

        if (!empty($template) and !empty($course)) {
            $user = $certificate->student;

            $userCertificate = $this->saveCourseCertificate($user, $course);
            $locale = app()->getLocale();
            $body = (!empty($template->translate($locale)) and !empty($template->translate($locale)->body)) ? $template->translate($locale)->body : $template->body;

            $body = $this->makeBody(
                $template,
                $userCertificate,
                $user,
                $body,
                $this->resolveCourseTitle($course),
                null,
                $course->teacher->id,
                $course->teacher->full_name,
                $course->duration);

            $data = [
                'body' => $body
            ];

            $html = (string)view()->make('admin.certificates.create_template.show_certificate', $data);
            $apiResponse = $this->sendToApi($userCertificate, $html);
            if ($apiResponse instanceof \Illuminate\Http\Response
                || $apiResponse instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
                || $apiResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                return $apiResponse;
            }

            // Local fallback when certificate image API is unavailable
            return $this->localCertificateDownload($userCertificate, $html);
        }

        $toastData = [
            'title' => trans('public.request_failed'),
            'msg' => trans('update.no_certificate_template_is_defined_for_courses'),
            'status' => 'error'
        ];

        return redirect()->back()->with(['toast' => $toastData]);
    }

    public function makeBundleCertificate($certificate)
    {

        $template = CertificateTemplate::where('status', 'publish')
            ->where('type', 'bundle')
            ->first();

        $bundle = $certificate->bundle;

        if (!empty($template) and !empty($bundle)) {
            $user = $certificate->student;

            $userCertificate = $this->saveBundleCertificate($user, $bundle);
            $locale = app()->getLocale();
            $body = (!empty($template->translate($locale)) and !empty($template->translate($locale)->body)) ? $template->translate($locale)->body : $template->body;

            $body = $this->makeBody(
                $template,
                $userCertificate,
                $user,
                $body,
                $this->resolveCourseTitle($bundle),
                null,
                $bundle->teacher->id,
                $bundle->teacher->full_name,
                $bundle->duration);

            $data = [
                'body' => $body
            ];

            $html = (string)view()->make('admin.certificates.create_template.show_certificate', $data);
            $apiResponse = $this->sendToApi($userCertificate, $html);
            if ($apiResponse instanceof \Illuminate\Http\Response
                || $apiResponse instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
                || $apiResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                return $apiResponse;
            }

            return $this->localCertificateDownload($userCertificate, $html);
        }

        $toastData = [
            'title' => trans('public.request_failed'),
            'msg' => trans('update.no_certificate_template_is_defined_for_bundles'),
            'status' => 'error'
        ];

        return redirect()->back()->with(['toast' => $toastData]);
    }

    private function sendToApi($certificate, $html)
    {
        $userId = getCertificateMainSettings("certificate_api_user_id");
        $APIKey = getCertificateMainSettings("certificate_api_key");

        $data = [
            'html' => $html,
            'viewport_width' => CertificateTemplate::$templateWidth,
            'viewport_height' => CertificateTemplate::$templateHeight,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://hcti.io/v1/image?width=400");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        curl_setopt($ch, CURLOPT_POST, 1);

        // Retrieve your user_id and api_key from https://htmlcsstoimage.com/dashboard
        curl_setopt($ch, CURLOPT_USERPWD, "{$userId}" . ":" . "{$APIKey}");

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $res = json_decode($result, true);

if (!empty($res['url'])) {
            $url = $res['url'] . ".png";
            $image = file_get_contents($url);
            $storage = Storage::disk('public');
            $path = auth()->id() . '/certificates';
            if (!$storage->exists($path)) {
                $storage->makeDirectory($path);
            }
            $fileName = "certificate_{$certificate->id}.png";
            $path = $path . '/' . $fileName;
            $storage->put($path, $image);
            $url = public_path($storage->url($path));
            $headers = array(
                'Content-Type: image/jpeg',
            );

            return response()->download($url, "certificate.png", $headers);
        }

        // API unavailable / plan limit — let caller render local HTML fallback
        return null;
    }

    public function saveCourseCertificate($user, $course)
    {
        $certificate = Certificate::where('webinar_id', $course->id)
            ->where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $data = [
            'webinar_id' => $course->id,
            'student_id' => $user->id,
            'type' => 'course',
            'created_at' => time()
        ];

        if (!empty($certificate)) {
            $certificate->update($data);
        } else {
            $certificate = Certificate::create($data);

            $notifyOptions = [
                '[c.title]' => $course->title,
            ];
            sendNotification('new_certificate', $notifyOptions, $user->id);
        }

        return $certificate;
    }

    public function saveBundleCertificate($user, $bundle)
    {
        $certificate = Certificate::where('bundle_id', $bundle->id)
            ->where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $data = [
            'bundle_id' => $bundle->id,
            'student_id' => $user->id,
            'type' => 'bundle',
            'created_at' => time()
        ];

        if (!empty($certificate)) {
            $certificate->update($data);
        } else {
            $certificate = Certificate::create($data);

            $notifyOptions = [
                '[c.title]' => $bundle->title,
            ];
            sendNotification('new_certificate', $notifyOptions, $user->id);
        }

        return $certificate;
    }

}
