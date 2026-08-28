<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function home(Request $request)
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

        return view('panel_v1.instructor.pages.home', [
            'pageTitle' => 'لوحة المدرب',
            'authUser' => $user,
        ]);
    }
}
