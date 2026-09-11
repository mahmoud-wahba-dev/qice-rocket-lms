<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class EducationController extends AdminController
{
    public function home(Request $request)
    {
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.home',
            'لوحة التعليم والأكاديميات',
            AdminMockData::educationHome()
        );
    }

    public function section(Request $request, string $section)
    {
        $data = array_merge(
            AdminMockData::shell('education', $section),
            AdminMockData::stubMeta('education', $section)
        );

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.stub',
            $data['stubTitle'],
            $data
        );
    }
}
