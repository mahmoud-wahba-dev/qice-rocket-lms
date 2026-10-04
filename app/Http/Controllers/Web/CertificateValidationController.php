<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificateValidationController extends Controller
{
    public function index(Request $request)
    {
        $getSeoMetas = getSeoMetas('certificate_validation');
        $pageTitle = !empty($getSeoMetas['title']) ? $getSeoMetas['title'] : trans('site.certificate_validation_page_title');
        $pageDescription = !empty($getSeoMetas['description']) ? $getSeoMetas['description'] : trans('site.certificate_validation_page_title');
        $pageRobot = getPageRobot('certificate_validation');

        $rawId = trim((string) $request->query('certificate_id', ''));
        $prefilledId = '';
        $prefilledResult = null;
        $autoChecked = false;

        if ($rawId !== '') {
            $parsedId = MakeCertificate::parseCertificateId($rawId);
            $autoChecked = true;

            if (!empty($parsedId)) {
                $cert = Certificate::with(['student', 'webinar', 'quiz.webinar', 'bundle'])->find($parsedId);

                if (!empty($cert)) {
                    $prefilledResult = $this->buildResultPayload($cert);
                    $prefilledId = $prefilledResult['number'];
                } else {
                    $prefilledResult = ['valid' => false];
                    $prefilledId = $rawId;
                }
            } else {
                $prefilledResult = ['valid' => false];
                $prefilledId = $rawId;
            }
        }

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'prefilledId' => $prefilledId,
            'prefilledResult' => $prefilledResult,
            'autoChecked' => $autoChecked,
            'sampleCertificateUrl' => asset('assets/panel_v1/img/certificate.jpg'),
        ];

        if (view()->exists('landing_v1.pages.certificate-validation')) {
            return view('landing_v1.pages.certificate-validation', $data);
        }

        return view('design_1.web.certificate_validation.index', $data);
    }

    public function checkValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificate_id' => 'required|string|max:64',
            'captcha' => 'required|captcha',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $parsedId = MakeCertificate::parseCertificateId($request->input('certificate_id'));
        $certificate = null;

        if (!empty($parsedId)) {
            $certificate = Certificate::with(['student', 'webinar', 'quiz.webinar', 'bundle'])->find($parsedId);
        }

        $payload = !empty($certificate)
            ? $this->buildResultPayload($certificate)
            : ['valid' => false];

        $viewName = view()->exists('landing_v1.pages.certificate-validation-result')
            ? 'landing_v1.pages.certificate-validation-result'
            : 'design_1.web.certificate_validation.status';

        return response()->json([
            'code' => 200,
            'valid' => !empty($payload['valid']),
            'number' => $payload['number'] ?? null,
            'html' => (string) view($viewName, $payload),
        ]);
    }

    private function buildResultPayload(Certificate $certificate): array
    {
        $make = new MakeCertificate();
        $qiec = $make->buildQiecCertificateData($certificate);

        $typeLabels = [
            'quiz' => 'اختبار',
            'bundle' => 'حزمة',
            'course' => 'إتمام دورة',
        ];
        $type = (string) ($certificate->type ?? 'course');

        return [
            'valid' => true,
            'number' => $qiec['certificate_number'] ?? $certificate->formatted_number,
            'trainee' => $qiec['trainee_name_ar'] ?: ($qiec['trainee_name_en'] ?: ($certificate->student->full_name ?? '—')),
            'course' => $qiec['course_title_ar'] ?: ($qiec['course_title_en'] ?: '—'),
            'hours' => $qiec['hours'] ?? null,
            'issue_date' => $qiec['issue_date'] ?? dateTimeFormat($certificate->created_at, 'd/m/Y'),
            'start_date' => $qiec['start_date'] ?? null,
            'end_date' => $qiec['end_date'] ?? null,
            'type' => $type,
            'type_label' => $typeLabels[$type] ?? 'شهادة',
            'verify_url' => url('/certificate_validation?certificate_id=' . urlencode($qiec['certificate_number'] ?? $certificate->id)),
        ];
    }
}
