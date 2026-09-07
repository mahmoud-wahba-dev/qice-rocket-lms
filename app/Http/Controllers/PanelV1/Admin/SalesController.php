<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use Illuminate\Http\Request;

class SalesController extends AdminController
{
    public function home(Request $request)
    {
        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.sales.sales-list',
            'قائمة المبيعات',
            AdminMockData::salesList()
        );
    }

    public function section(Request $request, string $section)
    {
        $data = array_merge(
            AdminMockData::shell('sales', $section),
            AdminMockData::stubMeta('sales', $section)
        );

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.sales.stub',
            $data['stubTitle'],
            $data
        );
    }
}
