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
    private function prepareCertificateHtmlForPdf(string $html): string
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
            . '.certificate-template-container{width:930px;height:600px;position:relative;background-repeat:no-repeat;background-size:100% 100%;border:0;}'
            . '.certificate-template-container .draggable-element{position:absolute !important;display:block;white-space:pre-wrap;}'
            . '.certificate-template-container img{max-width:100%;max-height:100%;}';

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
            $url = url('/certificate_validation?certificate_id=' . $certificate->id);
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
