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

    public function qiecTemplateAvailable(): bool
    {
        return is_file(public_path('assets/panel_v1/img/certificate.jpg'));
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
            'officer_name' => $defaultOfficer,
            'director_name' => $defaultDirector,
            'certificate_id' => (int) $certificate->id,
            'type' => (string) ($certificate->type ?? 'course'),
        ];

        // Per-certificate form overrides
        foreach ([
            'trainee_name_ar', 'trainee_name_en', 'course_title_ar', 'course_title_en',
            'hours', 'start_date', 'end_date', 'issue_date',
            'accreditation_number', 'officer_name', 'director_name',
        ] as $key) {
            if (isset($meta[$key]) && trim((string) $meta[$key]) !== '') {
                $data[$key] = is_numeric($meta[$key]) && $key === 'hours'
                    ? str_pad((string) ((int) $meta[$key]), 2, '0', STR_PAD_LEFT)
                    : (string) $meta[$key];
            }
        }
        $data['trainee_name'] = $data['trainee_name_ar'] ?: $data['trainee_name_en'];
        $data['course_title'] = $data['course_title_ar'] ?: $data['course_title_en'];

        // QR for HTML template — encode QEC number so validation page can parse it
        $data['qr_html'] = '';
        try {
            $url = url('/certificate_validation?certificate_id=' . urlencode($data['certificate_number']));
            try {
                $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(120)->margin(0)->generate($url);
                $data['qr_html'] = '<img src="data:image/png;base64,' . base64_encode($png) . '" width="78" height="78" alt="QR" />';
            } catch (\Throwable $e) {
                $svg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(120)->margin(0)->generate($url);
                $data['qr_html'] = '<img src="data:image/svg+xml;base64,' . base64_encode($svg) . '" width="78" height="78" alt="QR" />';
            }
        } catch (\Throwable $e) {
            $data['qr_html'] = '';
        }

        return $data;
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
            $bgPath = public_path('assets/panel_v1/img/certificate.jpg');
            $html = (string) view('panel_v1.certificates.qiec-template', [
                'data' => $data,
                'backgroundUrl' => $this->pathToFileUrl($bgPath),
                'width' => 1280,
                'height' => 905,
            ]);

            return $this->localQiecCertificateDownload($certificate, $html);
        }
    }

    /**
     * Draw dynamic fields onto certificate.jpg (1280×905) — one image, one page.
     */
    private function composeQiecCertificatePng(array $data): string
    {
        $bgPath = public_path('assets/panel_v1/img/certificate.jpg');
        $img = Image::make($bgPath)->resize(1280, 905);

        $paper = '#FAF9F4';
        $ink = '#0A3D38';

        $fill = function (int $x, int $y, int $w, int $h) use ($img, $paper) {
            $img->rectangle($x, $y, $x + $w, $y + $h, function ($draw) use ($paper) {
                $draw->background($paper);
            });
        };

        $fontEn = public_path('assets/default/fonts/Montserrat-Medium.ttf');
        if (!is_file($fontEn)) {
            $fontEn = public_path('assets/default/fonts/vazir/Vazir-Medium.ttf');
        }
        $fontAr = public_path('assets/default/fonts/vazir/Vazir-Medium.ttf');
        if (!is_file($fontAr)) {
            $fontAr = $fontEn;
        }

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

        $write = function (string $text, int $x, int $y, int $size, string $align = 'left', ?bool $rtl = null) use ($img, $fontEn, $fontAr, $shapeAr, $hasAr, $ink) {
            $text = trim($text);
            if ($text === '') {
                return;
            }
            $rtl = $rtl ?? $hasAr($text);
            if ($rtl) {
                $text = $shapeAr($text);
            }
            $img->text($text, $x, $y, function ($font) use ($size, $align, $rtl, $fontEn, $fontAr, $ink) {
                $file = $rtl ? $fontAr : $fontEn;
                if (is_file($file)) {
                    $font->file($file);
                }
                $font->size($size);
                $font->color($ink);
                $font->align($align);
                $font->valign('top');
            });
        };

        $num = (string) ($data['certificate_number'] ?? '');
        $nameEn = (string) ($data['trainee_name_en'] ?? '');
        $nameAr = (string) ($data['trainee_name_ar'] ?? '');
        $courseEn = (string) ($data['course_title_en'] ?? '');
        $courseAr = (string) ($data['course_title_ar'] ?? '');
        $hours = str_pad((string) ((int) ($data['hours'] ?? 0)), 2, '0', STR_PAD_LEFT);
        $start = (string) ($data['start_date'] ?? '');
        $end = (string) ($data['end_date'] ?? '');
        $issue = (string) ($data['issue_date'] ?? '');
        $acc = (string) ($data['accreditation_number'] ?? '');
        $officer = (string) ($data['officer_name'] ?? '');
        $director = (string) ($data['director_name'] ?? '');

        // --- Certificate numbers: replace [ QEC-0000-000 ] only (keep labels) ---
        $fill(168, 278, 160, 28);
        $write('[ ' . $num . ' ]', 248, 284, 13, 'center', false);
        $fill(990, 278, 130, 28);
        $write('[ ' . $num . ' ]', 1055, 284, 13, 'center', false);

        // --- Trainee names (above tan underlines) ---
        $fill(72, 430, 463, 36);
        $fill(759, 430, 464, 36);
        $write($nameEn, 303, 438, 20, 'center');
        $write($nameAr, 991, 438, 20, 'center', true);

        // --- Course titles ---
        $fill(72, 520, 463, 40);
        $fill(759, 520, 464, 40);
        $write($courseEn, 303, 530, 17, 'center');
        $write($courseAr, 991, 530, 17, 'center', true);

        // --- Meta lines: wipe full line + rewrite label + value (keeps one page on JPG) ---
        // EN hours: Duration: [ 00 ] training hours
        $fill(70, 585, 280, 26);
        $write('Duration: [ ' . $hours . ' ] training hours', 70, 590, 13, 'left', false);
        // AR hours: عدد الساعات التدريبية: [ 00 ] ساعة
        $fill(960, 585, 270, 26);
        $write('عدد الساعات التدريبية: [ ' . $hours . ' ] ساعة', 1230, 590, 13, 'right', true);

        // EN dates
        $fill(70, 615, 420, 26);
        $write('Course dates: [ ' . $start . ' ] - [ ' . $end . ' ]', 70, 620, 12, 'left', false);
        // AR dates
        $fill(850, 615, 380, 26);
        $write('فترة الدورة: [ ' . $start . ' ] - [ ' . $end . ' ]', 1230, 620, 12, 'right', true);

        // EN issue
        $fill(70, 645, 300, 26);
        $write('Issue date: [ ' . $issue . ' ]', 70, 650, 12, 'left', false);
        // AR issue
        $fill(960, 645, 270, 26);
        $write('تاريخ الإصدار: [ ' . $issue . ' ]', 1230, 650, 12, 'right', true);

        // EN accreditation
        $fill(70, 675, 360, 26);
        $write('Accreditation Number: [ ' . $acc . ' ]', 70, 680, 12, 'left', false);
        // AR accreditation
        $fill(960, 675, 270, 26);
        $write('رقم الاعتماد: [ ' . $acc . ' ]', 1230, 680, 12, 'right', true);

        // --- Signature names (between line and [ Name ] label) ---
        $fill(71, 820, 249, 22);
        $fill(960, 820, 248, 22);
        $write($officer, 195, 822, 14, 'center');
        $write($director, 1084, 822, 14, 'center');

        // QR bottom-left (validation)
        try {
            $url = url('/certificate_validation?certificate_id=' . urlencode((string) ($data['certificate_number'] ?? $data['certificate_id'] ?? '')));
            $qrPng = null;
            try {
                $qrPng = (string) QrCode::format('png')->size(140)->margin(0)->generate($url);
            } catch (\Throwable $e) {
            }
            if (!empty($qrPng)) {
                $qr = Image::make($qrPng)->resize(72, 72);
                $img->insert($qr, 'top-left', 40, 800);
            }
        } catch (\Throwable $e) {
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

        $url = url('/certificate_validation');
        if (!empty($certificate?->id)) {
            $url = url('/certificate_validation?certificate_id=' . urlencode(self::formatCertificateNumber((int) $certificate->id)));
        }

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
