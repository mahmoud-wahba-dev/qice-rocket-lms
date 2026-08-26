<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        return view('panel_v1.admin.pages.home', [
            'pageTitle' => 'لوحة الإدارة',
            'authUser' => $user,
        ]);
    }
}
