<?php

namespace Tests\Feature\Models;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingFallbackTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        // Setting caches resolved values in static props (per-process);
        // reset them so tests stay independent.
        foreach (['seoMetas', 'general', 'financial', 'generalSecuritySettings'] as $prop) {
            $ref = new \ReflectionProperty(Setting::class, $prop);
            $ref->setAccessible(true);
            $ref->setValue(null, null);
        }

        parent::tearDown();
    }

    public function test_missing_key_returns_empty_string_on_empty_table(): void
    {
        $this->assertSame('', Setting::getGeneralSecuritySettings('admin_panel_url'));
        $this->assertSame('', Setting::getFinancialSettings('tax'));
    }

    public function test_no_key_returns_empty_array_on_empty_table(): void
    {
        $this->assertSame([], Setting::getFinancialSettings());
    }

    public function test_helper_falls_back_to_admin_prefix(): void
    {
        $this->assertSame('admin', getAdminPanelUrlPrefix());
    }

    public function test_settings_table_exists_in_testing_db(): void
    {
        $this->assertTrue(Schema::hasTable('settings'));
    }
}
