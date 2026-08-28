<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        // Wrong role → send them to their panel instead of a broken design_1 403 page
        if (!$user->isUser()) {
            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
            }

            if ($user->isAdmin()) {
                return redirect()->route('panel.v1.admin.home');
            }

            return redirect('/panel');
        }

        return view('panel_v1.student.pages.home', [
            'pageTitle' => 'لوحة المتدرب',
            'authUser' => $user,
        ]);
    }
}
