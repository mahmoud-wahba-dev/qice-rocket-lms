<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PanelV1\Support\InstructorMockData;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function home(Request $request)
    {
        return $this->render($request, 'panel_v1.instructor.pages.home', 'لوحة المدرب', InstructorMockData::home());
    }

    public function courses(Request $request)
    {
        return $this->render($request, 'panel_v1.instructor.pages.courses', 'إدارة الدورات', InstructorMockData::courses());
    }

    public function coursePerformance(Request $request, string $slug)
    {
        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-performance',
            'لوحة أداء الدورة',
            InstructorMockData::coursePerformance($slug)
        );
    }

    public function courseAssignments(Request $request, string $slug)
    {
        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-assignments',
            'متطلبات الدورات',
            InstructorMockData::courseAssignments($slug)
        );
    }

    public function assignments(Request $request)
    {
        return $this->render($request, 'panel_v1.instructor.pages.assignments', 'إدارة الواجبات والتكليفات', InstructorMockData::assignments());
    }

    public function assignmentReview(Request $request, int $id)
    {
        return $this->render(
            $request,
            'panel_v1.instructor.pages.assignment-review',
            'تقييم التكليف',
            InstructorMockData::assignmentReview($id)
        );
    }

    public function consultations(Request $request)
    {
        return $this->render($request, 'panel_v1.instructor.pages.consultations', 'الجلسات الاستشارية', InstructorMockData::consultations());
    }

    private function render(Request $request, string $view, string $pageTitle, array $data = [])
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view($view, array_merge($data, [
            'pageTitle' => $pageTitle,
            'authUser' => $user,
        ]));
    }

    /**
     * @return \App\User|\Illuminate\Http\RedirectResponse
     */
    private function resolveInstructor(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isTeacher()) {
            if ($user->isUser()) {
                return redirect()->route('panel.v1.student.home');
            }

            if ($user->isAdmin()) {
                return redirect()->route('panel.v1.admin.home');
            }

            return redirect('/panel');
        }

        return $user;
    }
}
