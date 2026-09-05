<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function home(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.home', [
            'pageTitle' => 'لوحة المتدرب',
            'authUser' => $user,
        ]);
    }

    public function notifications(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.notifications', [
            'pageTitle' => 'الاشعارات',
            'authUser' => $user,
        ]);
    }

    public function purchases(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.purchases', [
            'pageTitle' => 'عمليات الشراء الخاصة بي',
            'authUser' => $user,
        ]);
    }

    public function support(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.support', [
            'pageTitle' => 'الدعم',
            'authUser' => $user,
        ]);
    }

    public function settings(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('panel_v1.student.pages.settings', [
            'pageTitle' => 'اعدادات الحساب',
            'authUser' => $user,
        ]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Stub: real DB mark-all can reuse legacy NotificationsController later.
        return redirect()
            ->route('panel.v1.student.notifications')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم وضع علامة مقروء على جميع الإشعارات',
                'type' => 'success',
            ]);
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
