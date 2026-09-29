<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Web\traits\LearningPageForumTrait;
use App\Http\Controllers\Web\traits\LearningPageItemInfoTrait;
use App\Http\Controllers\Web\traits\LearningPageMixinsTrait;
use App\Http\Controllers\Web\traits\LearningPageNoticeboardsTrait;
use App\Http\Controllers\Web\traits\LearningPagePersonalNoteTrait;
use App\Models\Certificate;
use App\Models\CourseLearningLastView;
use App\Models\CourseNoticeboard;
use Illuminate\Http\Request;

class LearningPageController extends Controller
{
    use LearningPageMixinsTrait, LearningPageAssignmentTrait, LearningPageItemInfoTrait,
        LearningPageNoticeboardsTrait, LearningPageForumTrait, LearningPagePersonalNoteTrait;

    public function index(Request $request, $slug)
    {
        // Old /course/learning/{slug} → panel_v1 player (free + paid)
        $params = array_filter([
            'type' => $request->get('type'),
            'item' => $request->get('item'),
            'student' => $request->get('student'),
        ], fn ($v) => $v !== null && $v !== '');

        $url = '/v1/student/courses/' . $slug . '/watch';
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return redirect()->to($url);
    }
}
