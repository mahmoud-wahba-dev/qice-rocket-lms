<?php

namespace Tests\Feature\PanelV1;

use App\Models\NotificationTemplate;
use App\Models\OfflineBank;
use App\Models\Setting;
use App\Models\Translation\OfflineBankTranslation;
use App\Models\Translation\SettingTranslation;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Validates panel_v1 settings UI still uses the same legacy Setting / OfflineBank backend.
 */
class AdminSettingsV1Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Legacy SettingsController uses authorize(); allow admin actions in tests
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    private function admin(): User
    {
        $u = User::where('role_name', 'admin')->first();
        if ($u) {
            return $u;
        }

        return User::create([
            'full_name' => 'Admin Settings Test',
            'email' => 'admin_settings_' . time() . rand(100, 999) . '@test.local',
            'role_name' => 'admin',
            'role_id' => 2,
            'password' => bcrypt('secret'),
            'status' => 'active',
            'created_at' => time(),
        ]);
    }

    private function req(User $user, string $uri = '/v1/admin', string $method = 'GET', array $data = []): Request
    {
        $r = Request::create($uri, $method, $data);
        $r->setUserResolver(fn () => $user);

        return $r;
    }

    public function test_settings_pages_return_views_with_db_collections(): void
    {
        $admin = $this->admin();
        $c = new \App\Http\Controllers\PanelV1\Admin\SettingsController();

        $general = $c->general($this->req($admin));
        $this->assertEquals('panel_v1.admin.pages.system.settings.general', $general->getName());
        $this->assertTrue($general->getData()['settings']->has('general') || $general->getData()['settings']->isNotEmpty());

        $financial = $c->financial($this->req($admin, '/v1/admin/system/settings/page/financial?tab=offline_banks', 'GET', ['tab' => 'offline_banks']));
        $this->assertEquals('panel_v1.admin.pages.system.settings.financial', $financial->getName());
        $this->assertEquals('offline_banks', $financial->getData()['activeTab']);
        $this->assertArrayHasKey('offlineBanks', $financial->getData());

        $personal = $c->personalization($this->req($admin), 'panel_sidebar');
        $this->assertEquals('panel_v1.admin.pages.system.settings.personalization', $personal->getName());
        $this->assertEquals('panel_sidebar', $personal->getData()['name']);

        $notifications = $c->notifications($this->req($admin));
        $this->assertEquals('panel_v1.admin.pages.system.settings.notifications', $notifications->getName());
        $this->assertGreaterThan(0, $notifications->getData()['notificationTemplates']->count());

        $seo = $c->seo($this->req($admin));
        $this->assertEquals('panel_v1.admin.pages.system.settings.seo', $seo->getName());
        $this->assertTrue(
            $seo->getData()['settings']->has('seo_metas') || $seo->getData()['settings']->isNotEmpty()
        );
    }

    public function test_store_uses_legacy_setting_translations_table(): void
    {
        $admin = $this->admin();
        $c = new \App\Http\Controllers\PanelV1\Admin\SettingsController();
        $marker = 'qiec-ui-' . time();

        $res = $c->store($this->req($admin, '/v1/admin/system/settings/store', 'POST', [
            'page' => 'general',
            'name' => 'general',
            'locale' => Setting::$defaultSettingsLocale,
            'value' => [
                'site_name' => $marker,
                'site_email' => 'ui@qiec.local',
                'register_method' => 'email',
            ],
        ]), 'general');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);

        $setting = Setting::where('name', 'general')->first();
        $this->assertNotNull($setting);
        $row = SettingTranslation::where('setting_id', $setting->id)
            ->where('locale', mb_strtolower(Setting::$defaultSettingsLocale))
            ->first();
        $this->assertNotNull($row);
        $decoded = json_decode($row->value, true);
        $this->assertEquals($marker, $decoded['site_name'] ?? null);
    }

    public function test_notifications_and_seo_delegate_to_legacy(): void
    {
        $admin = $this->admin();
        $c = new \App\Http\Controllers\PanelV1\Admin\SettingsController();
        $template = NotificationTemplate::query()->first();
        $this->assertNotNull($template);

        $c->storeNotifications($this->req($admin, '/', 'POST', [
            'locale' => Setting::$defaultSettingsLocale,
            'value' => ['new_comment_admin' => (string) $template->id],
        ]));

        $notif = Setting::where('name', 'notifications')->first();
        $this->assertNotNull($notif);
        $values = json_decode($notif->value, true);
        $this->assertEquals($template->id, (int) ($values['new_comment_admin'] ?? 0));

        $c->storeSeoMetas($this->req($admin, '/', 'POST', [
            'locale' => Setting::$defaultSettingsLocale,
            'value' => [
                'home' => [
                    'title' => 'عنوان SEO واجهة',
                    'description' => 'وصف',
                    'robot' => 'index',
                ],
            ],
        ]));

        $seo = Setting::where('name', Setting::$seoMetasName)->first();
        $seoValues = json_decode($seo->value, true);
        $this->assertEquals('عنوان SEO واجهة', $seoValues['home']['title'] ?? null);
    }

    public function test_offline_bank_crud_via_legacy_trait(): void
    {
        $admin = $this->admin();
        $c = new \App\Http\Controllers\PanelV1\Admin\SettingsController();
        $title = 'بنك واجهة ' . time();

        $json = $c->financialOfflineBankStore($this->req($admin, '/', 'POST', [
            'locale' => 'ar',
            'title' => $title,
            'logo' => '/store/ui-bank.png',
            'specifications' => [
                'new_1' => ['name' => 'رقم الحساب', 'value' => '999'],
            ],
        ]));
        $this->assertEquals(200, $json->getStatusCode());

        $bank = OfflineBank::query()
            ->whereHas('translations', fn ($q) => $q->where('title', $title))
            ->first();
        $this->assertNotNull($bank);

        $edit = $c->offlineBankEdit($this->req($admin), $bank->id);
        $this->assertEquals(200, $edit->getStatusCode());
        $this->assertStringContainsString('addOfflineBankForm', $edit->getData(true)['html'] ?? '');

        $c->financialOfflineBankUpdate($this->req($admin, '/', 'POST', [
            'locale' => 'ar',
            'title' => $title . ' 2',
            'logo' => '/store/ui-bank-2.png',
        ]), $bank->id);

        $tr = OfflineBankTranslation::where('offline_bank_id', $bank->id)->where('locale', 'ar')->first();
        $this->assertEquals($title . ' 2', $tr->title ?? null);

        $c->financialOfflineBankDelete($bank->id);
        $this->assertNull(OfflineBank::find($bank->id));
    }

    public function test_hub_urls_and_group_redirects(): void
    {
        $admin = $this->admin();
        $sys = new \App\Http\Controllers\PanelV1\Admin\SystemController();

        $hub = $sys->settingsHub($this->req($admin));
        $cards = collect($hub->getData()['settingsCards'])->keyBy('key');
        $this->assertStringContainsString('/settings/page/general', $cards['general']['url']);
        $this->assertStringContainsString('/settings/page/financial', $cards['financial']['url']);
        $this->assertStringContainsString('panel_sidebar', $cards['personalization']['url']);
        $this->assertStringContainsString('/settings/page/notifications', $cards['notifications']['url']);
        $this->assertStringContainsString('/settings/page/seo', $cards['seo']['url']);

        foreach (['general', 'financial', 'notifications', 'seo', 'personalization'] as $group) {
            $res = $sys->settingsGroup($this->req($admin), $group);
            $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
        }
    }

    public function test_student_blocked_from_settings_ui(): void
    {
        $student = User::where('role_name', 'user')->first()
            ?? User::create([
                'full_name' => 'Student',
                'email' => 'stu_set_' . time() . '@test.local',
                'role_name' => 'user',
                'role_id' => 1,
                'password' => bcrypt('secret'),
                'status' => 'active',
                'created_at' => time(),
            ]);

        $c = new \App\Http\Controllers\PanelV1\Admin\SettingsController();
        $res = $c->general($this->req($student));
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $res);
    }
}
