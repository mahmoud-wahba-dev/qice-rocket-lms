<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class SystemController extends AdminController
{
    public function home(Request $request)
    {
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.users',
            'المستخدمين',
            AdminMockData::systemUsers()
        );
    }

    public function section(Request $request, string $section)
    {
        $data = array_merge(
            AdminMockData::shell('system', $section),
            AdminMockData::stubMeta('system', $section)
        );

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.stub',
            $data['stubTitle'],
            $data
        );
    }
}
