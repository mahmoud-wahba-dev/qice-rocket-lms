<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        $prefilledId = $request->query('certificate_id');
        $prefilledResult = null;
        if (!empty($prefilledId) && is_numeric($prefilledId)) {
            $cert = Certificate::where('id', (int) $prefilledId)->first();
            if (!empty($cert)) {
                $webinarTitle = '-';
                if ($cert->type == 'quiz' && !empty($cert->quiz) && !empty($cert->quiz->webinar)) {
                    $webinarTitle = $cert->quiz->webinar->title;
                } elseif ($cert->type == 'course' && !empty($cert->webinar)) {
                    $webinarTitle = $cert->webinar->title;
                } elseif ($cert->type == 'bundle' && !empty($cert->bundle)) {
                    $webinarTitle = $cert->bundle->title;
                }
                $prefilledResult = ['certificate' => $cert, 'webinarTitle' => $webinarTitle];
            } else {
                $prefilledResult = [];
            }
        }

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'prefilledId' => $prefilledId,
            'prefilledResult' => $prefilledResult,
        ];

        // landing_v1 is the active theme — compatible with general QIEC theme
        if (view()->exists('landing_v1.pages.certificate-validation')) {
            return view('landing_v1.pages.certificate-validation', $data);
        }
        return view('design_1.web.certificate_validation.index', $data);
    }

    public function checkValidate(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'certificate_id' => 'required|numeric',
            'captcha' => 'required|captcha',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $certificateId = $data['certificate_id'];

        $certificate = Certificate::where('id', $certificateId)->first();

        $result = [];

        if (!empty($certificate)) {
            $webinarTitle = "-";

            if ($certificate->type == 'quiz' and !empty($certificate->quiz) and !empty($certificate->quiz->webinar)) {
                $webinarTitle = $certificate->quiz->webinar->title;
            } else if ($certificate->type == "course" and !empty($certificate->webinar)) {
                $webinarTitle = $certificate->webinar->title;
            } elseif ($certificate->type == 'bundle' && !empty($certificate->bundle)) {
                $webinarTitle = $certificate->bundle->title;
            }

            $result = [
                'certificate' => $certificate,
                'webinarTitle' => $webinarTitle,
            ];
        }

        // Prefer landing_v1 result partial (Tailwind) — fallback to legacy design_1
        $viewName = view()->exists('landing_v1.pages.certificate-validation-result')
            ? 'landing_v1.pages.certificate-validation-result'
            : 'design_1.web.certificate_validation.status';
        $html = (string)view()->make($viewName, $result);

        return response()->json([
            'code' => 200,
            'html' => $html
        ]);
    }
}
