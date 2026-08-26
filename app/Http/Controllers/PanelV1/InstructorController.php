<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->isTeacher()) {
            abort(403);
        }

        return view('panel_v1.instructor.pages.home', [
            'pageTitle' => 'لوحة المدرب',
            'authUser' => $user,
        ]);
    }
}
