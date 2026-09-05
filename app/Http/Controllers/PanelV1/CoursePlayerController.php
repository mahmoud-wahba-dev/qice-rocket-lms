<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PanelV1\Support\CoursePlayerMockData;
use Illuminate\Http\Request;

class CoursePlayerController extends Controller
{
    public function watch(Request $request, string $slug)
    {
        return $this->render($request, $slug, 'panel_v1.student.course-player.pages.watch', 'مشاهدة الدورة');
    }

    public function forum(Request $request, string $slug)
    {
        return $this->render($request, $slug, 'panel_v1.student.course-player.pages.forum', 'منتدى الدورة');
    }

    public function assignment(Request $request, string $slug)
    {
        return $this->render($request, $slug, 'panel_v1.student.course-player.pages.assignment', 'تقديم إجابة التكليف');
    }

    public function quiz(Request $request, string $slug)
    {
        return $this->render($request, $slug, 'panel_v1.student.course-player.pages.quiz-start', 'بدء الاختبار');
    }

    public function quizTake(Request $request, string $slug)
    {
        return $this->render($request, $slug, 'panel_v1.student.course-player.pages.quiz-take', 'الاختبار');
    }

    private function render(Request $request, string $slug, string $view, string $pageTitle)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $mock = CoursePlayerMockData::forSlug($slug);

        return view($view, array_merge($mock, [
            'pageTitle' => $pageTitle,
            'authUser' => $user,
        ]));
    }

    /**
     * @return \App\User|\Illuminate\Http\RedirectResponse
     */
    private function resolveStudent(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isUser()) {
            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
            }

            if ($user->isAdmin()) {
                return redirect()->route('panel.v1.admin.home');
            }

            return redirect('/panel');
        }

        return $user;
    }
}
