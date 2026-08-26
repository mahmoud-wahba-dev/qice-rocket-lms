<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->isUser()) {
            abort(403);
        }

        return view('panel_v1.student.pages.home', [
            'pageTitle' => 'لوحة المتدرب',
            'authUser' => $user,
        ]);
    }
}
