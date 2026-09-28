<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\Admin\SettingsController as LegacySettingsController;
use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use App\Models\NotificationTemplate;
use App\Models\OfflineBank;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Panel V1 settings UI only — all save/CRUD delegates to legacy Admin\SettingsController
 * (same Setting rows, same offline_banks logic). No new settings business rules.
 */
class SettingsController extends AdminController
{
    private function legacy(): LegacySettingsController
    {
        return app(LegacySettingsController::class);
    }

    /** Ensure Auth::user() matches panel admin so legacy authorize() passes */
    private function bindAuthUser($user): void
    {
        auth()->setUser($user);
        if (method_exists(request(), 'setUserResolver')) {
            request()->setUserResolver(fn () => $user);
        }
    }

    public function general(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        // Same data load as Admin\SettingsController::page('general')
        $settings = Setting::where('page', 'general')->get()->keyBy('name');
        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value, true);
        }

        $social = null;
        $socialKey = null;
        if ($request->filled('social')) {
            $socials = $settings->get(Setting::$socialsName);
            $values = !empty($socials) ? (is_array($socials->value) ? $socials->value : []) : [];
            $key = $request->get('social');
            if (!empty($values[$key])) {
                $social = (object) $values[$key];
                $socialKey = $key;
            }
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.settings.general',
            trans('admin/main.main_general') . ' — ' . trans('admin/main.settings'),
            array_merge(AdminMockData::shell('system', 'settings'), [
                'pageTitleText' => trans('admin/main.main_general') . ' ' . trans('admin/main.settings'),
                'settings' => $settings,
                'social' => $social,
                'socialKey' => $socialKey,
                'activeTab' => $request->get('tab', !empty($social) ? 'socials' : 'basic'),
                'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
            ])
        );
    }

    public function financial(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        // Same data load as Admin\SettingsController::page('financial')
        $settings = Setting::where('page', 'financial')->get()->keyBy('name');
        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value, true);
        }

        $tab = $request->get('tab', 'basic');
        $data = [
            'pageTitleText' => trans('admin/main.financial_settings'),
            'settings' => $settings,
            'activeTab' => $tab,
            'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
        ];

        if ($tab === 'offline_banks') {
            $data['offlineBanks'] = OfflineBank::query()->orderBy('created_at', 'desc')->get();
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.settings.financial',
            trans('admin/main.financial_settings'),
            array_merge(AdminMockData::shell('system', 'settings'), $data)
        );
    }

    public function personalization(Request $request, string $name = 'panel_sidebar')
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        if ($name !== 'panel_sidebar') {
            abort(404);
        }

        // Same load as Admin\SettingsController::personalizationPage
        $settings = Setting::where('name', $name)->first();
        $values = null;
        if (!empty($settings) && !empty($settings->value)) {
            $values = json_decode($settings->value, true);
            $values['locale'] = mb_strtoupper($settings->locale ?? getDefaultLocale());
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.settings.personalization',
            trans('admin/main.panel_sidebar'),
            array_merge(AdminMockData::shell('system', 'settings'), [
                'pageTitleText' => trans('admin/main.panel_sidebar'),
                'values' => $values,
                'name' => $name,
                'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
            ])
        );
    }

    public function notifications(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        $settings = Setting::where('page', 'notifications')->get()->keyBy('name');
        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value, true);
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.settings.notifications',
            trans('admin/main.notifications'),
            array_merge(AdminMockData::shell('system', 'settings'), [
                'pageTitleText' => trans('admin/main.notifications'),
                'settings' => $settings,
                'notificationTemplates' => NotificationTemplate::all(),
                'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
            ])
        );
    }

    public function seo(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        $settings = Setting::where('page', 'seo')->get()->keyBy('name');
        foreach ($settings as $setting) {
            $setting->value = json_decode($setting->value, true);
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.settings.seo',
            trans('admin/main.seo_metas'),
            array_merge(AdminMockData::shell('system', 'settings'), [
                'pageTitleText' => trans('admin/main.seo_metas'),
                'settings' => $settings,
                'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
            ])
        );
    }

    /** @see LegacySettingsController::store */
    public function store(Request $request, $name = null)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        if (!empty($request->get('name'))) {
            $name = $request->get('name');
        }

        return $this->legacy()->store($request, $name ?: 'general');
    }

    /** @see LegacySettingsController::storeSeoMetas */
    public function storeSeoMetas(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        return $this->legacy()->storeSeoMetas($request);
    }

    /** @see LegacySettingsController::notificationsMetas */
    public function storeNotifications(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        return $this->legacy()->notificationsMetas($request);
    }

    /** @see LegacySettingsController::storeSocials */
    public function storeSocials(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        $this->legacy()->storeSocials($request);

        return redirect()
            ->route('panel.v1.admin.system.settings.general', ['tab' => 'socials'])
            ->with('toast', [
                'title' => trans('public.request_success'),
                'msg' => 'تم حفظ الشبكة الاجتماعية بنجاح',
                'type' => 'success',
            ]);
    }

    /** @see LegacySettingsController::deleteSocials */
    public function deleteSocial(Request $request, string $key)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        $this->legacy()->deleteSocials($key, $request->get('locale'));

        return redirect()
            ->route('panel.v1.admin.system.settings.general', ['tab' => 'socials'])
            ->with('toast', [
                'title' => trans('public.request_success'),
                'msg' => 'تم حذف الشبكة الاجتماعية بنجاح',
                'type' => 'success',
            ]);
    }

    /** @see DeviceLimitSettings::resetUsersLoginCount */
    public function resetUsersLoginCount(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        return $this->legacy()->resetUsersLoginCount($request);
    }

    /** UI modal only — store/update/delete use legacy trait methods on Admin controller */
    public function offlineBankForm()
    {
        $html = (string) view('panel_v1.admin.pages.system.settings.financial.offline-bank-modal', [
            'locale' => mb_strtolower(app()->getLocale()),
            'storeUrl' => route('panel.v1.admin.system.settings.offline-banks.store'),
        ]);

        return response()->json(['code' => 200, 'html' => $html]);
    }

    public function offlineBankEdit(Request $request, $id)
    {
        $bank = OfflineBank::query()->findOrFail($id);
        $html = (string) view('panel_v1.admin.pages.system.settings.financial.offline-bank-modal', [
            'editBank' => $bank,
            'locale' => mb_strtolower($request->get('locale', app()->getLocale())),
            'storeUrl' => route('panel.v1.admin.system.settings.offline-banks.update', ['id' => $bank->id]),
        ]);

        return response()->json(['code' => 200, 'html' => $html]);
    }

    public function financialOfflineBankStore(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        return $this->legacy()->financialOfflineBankStore($request);
    }

    public function financialOfflineBankUpdate(Request $request, $id)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof RedirectResponse) {
            return $user;
        }
        $this->bindAuthUser($user);

        return $this->legacy()->financialOfflineBankUpdate($request, $id);
    }

    public function financialOfflineBankDelete($id)
    {
        return $this->legacy()->financialOfflineBankDelete($id);
    }
}
