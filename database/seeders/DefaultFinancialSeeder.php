<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Minimal financial defaults for fresh installs (local/dev).
 *
 * Without these rows, pricing helpers fall back to empty strings and
 * payment code paths misbehave on PHP 8. Production values must be
 * managed from Admin → Settings → Financial (never hard-code secrets here).
 */
class DefaultFinancialSeeder extends Seeder
{
    public function run()
    {
        $this->seedSetting('financial', 'financial', [
            'tax' => 0,
            'price_display' => 'only_price',
            'commission' => 0,
        ]);

        $this->seedSetting('currency_settings', 'financial', [
            'currency' => 'SAR',
            'currency_position' => 'right',
            'currency_separator' => 'dot',
            'currency_decimal' => 2,
        ]);

        Currency::updateOrCreate(
            ['currency' => 'SAR'],
            [
                'currency_position' => 'right',
                'currency_separator' => 'dot',
                'currency_decimal' => 2,
                'exchange_rate' => 1,
                'order' => 1,
                'created_at' => time(),
            ]
        );
    }

    private function seedSetting(string $name, string $page, array $value): void
    {
        $setting = Setting::updateOrCreate(
            ['name' => $name],
            ['page' => $page, 'updated_at' => time()]
        );

        foreach (['ar', 'en'] as $locale) {
            $translation = $setting->translateOrNew($locale);
            $translation->setting_id = $setting->id;
            $translation->locale = $locale;
            $translation->value = json_encode($value, JSON_UNESCAPED_UNICODE);
            $translation->save();
        }

        // Reset the in-memory static cache used by Setting::getSetting().
        cache()->forget('settings.' . $name);
    }
}
