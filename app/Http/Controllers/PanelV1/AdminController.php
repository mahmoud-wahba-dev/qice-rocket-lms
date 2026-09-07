<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function home(Request $request)
    {
        $user = $this->resolveAdmin($request);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return redirect()->route('panel.v1.admin.education.home');
    }

    /**
     * @return \App\User|RedirectResponse
     */
    protected function resolveAdmin(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isAdmin()) {
            if ($user->isUser()) {
                return redirect()->route('panel.v1.student.home');
            }

            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
            }

            return redirect('/panel');
        }

        return $user;
    }

    protected function renderAdmin(Request $request, string $view, string $pageTitle, array $data = [])
    {
        $user = $this->resolveAdmin($request);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return view($view, array_merge($data, [
            'pageTitle' => $pageTitle,
            'authUser' => $user,
        ]));
    }
}
