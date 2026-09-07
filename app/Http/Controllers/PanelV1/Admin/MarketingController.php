<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class MarketingController extends AdminController
{
    public function home(Request $request)
    {
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.marketing.content-appearance',
            'إدارة المحتوى والمظهر',
            AdminMockData::marketingContent()
        );
    }

    public function section(Request $request, string $section)
    {
        $data = array_merge(
            AdminMockData::shell('marketing', $section),
            AdminMockData::stubMeta('marketing', $section)
        );

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.marketing.stub',
            $data['stubTitle'],
            $data
        );
    }
}
