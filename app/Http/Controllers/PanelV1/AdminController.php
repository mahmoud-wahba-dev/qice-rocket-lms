<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function home(Request $request)
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

        return view('panel_v1.admin.pages.home', [
            'pageTitle' => 'لوحة الإدارة',
            'authUser' => $user,
        ]);
    }
}
