<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use App\Models\Translation\WebinarExtraDescriptionTranslation;
use App\Models\WebinarExtraDescription;
use Illuminate\Http\Request;

class WebinarExtraDescriptionController extends AdminController
{
    /**
     * Show all extra descriptions for a webinar/upcoming course (learning_materials, requirements, company_logos).
     * Parity: Admin\WebinarExtraDescriptionController
     */
    public function index(Request $request, string $scope, int $itemId)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $items = WebinarExtraDescription::where("{$scope}_id", $itemId)
            ->with('translations')
            ->orderBy('type')
            ->orderBy('order')
            ->get()
            ->groupBy('type');

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.education.extra-descriptions',
            'الأوصاف الإضافية',
            array_merge(AdminMockData::shell('education', 'courses'), [
                'extraItems' => $items,
                'extraScope' => $scope,
                'extraItemId' => $itemId,
                'extraTypes' => WebinarExtraDescription::$types,
                'formAction' => route('panel.v1.admin.education.extra-descriptions.store', ['scope' => $scope, 'itemId' => $itemId]),
            ])
        );
    }

    /**
     * Store new extra description.
     * Parity: Admin\WebinarExtraDescriptionController::store
     */
    public function store(Request $request, string $scope, int $itemId)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'type' => 'required|in:' . implode(',', WebinarExtraDescription::$types),
            'value' => 'required|string',
            'locale' => 'required|in:ar,en',
        ]);

        $data = $request->all();
        $data['locale'] = mb_strtolower($data['locale']);

        $order = WebinarExtraDescription::where("{$scope}_id", $itemId)
            ->where('type', $data['type'])
            ->count() + 1;

        $record = WebinarExtraDescription::create([
            'creator_id' => $user->id,
            'webinar_id' => $scope === 'webinar' ? $itemId : null,
            'upcoming_course_id' => $scope === 'upcoming_course' ? $itemId : null,
            'type' => $data['type'],
            'order' => $order,
            'created_at' => time(),
        ]);

        WebinarExtraDescriptionTranslation::updateOrCreate([
            'webinar_extra_description_id' => $record->id,
            'locale' => $data['locale'],
        ], ['value' => $data['value']]);

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إضافة الوصف', 'type' => 'success']);
    }

    /**
     * Update extra description value (translation).
     * Parity: Admin\WebinarExtraDescriptionController::update
     */
    public function update(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'value' => 'required|string',
            'locale' => 'required|in:ar,en',
        ]);

        $data = $request->all();
        $data['locale'] = mb_strtolower($data['locale']);

        $record = WebinarExtraDescription::findOrFail($id);

        WebinarExtraDescriptionTranslation::updateOrCreate([
            'webinar_extra_description_id' => $record->id,
            'locale' => $data['locale'],
        ], ['value' => $data['value']]);

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم التحديث', 'type' => 'success']);
    }

    /**
     * Delete extra description.
     * Parity: Admin\WebinarExtraDescriptionController::destroy
     */
    public function destroy(Request $request, int $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        WebinarExtraDescription::where('id', $id)->delete();

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم الحذف', 'type' => 'success']);
    }
}
