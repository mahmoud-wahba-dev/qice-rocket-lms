<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Minimal general settings for fresh installs (local/dev).
 *
 * Enables Arabic + English in the language switcher while keeping EN
 * as the site default. Admin users can switch to Arabic individually
 * (users.language = 'AR').
 */
class DefaultGeneralSeeder extends Seeder
{
    public function run()
    {
        $setting = Setting::updateOrCreate(
            ['name' => 'general'],
            ['page' => 'general', 'updated_at' => time()]
        );

        $value = json_encode([
            'site_name' => 'QIEC Training',
            'site_language' => 'EN',
            'user_languages' => ['AR', 'EN'],
            'rtl_languages' => ['AR', 'UR', 'FA'],
        ], JSON_UNESCAPED_UNICODE);

        foreach (['ar', 'en'] as $locale) {
            $translation = $setting->translateOrNew($locale);
            $translation->setting_id = $setting->id;
            $translation->locale = $locale;
            $translation->value = $value;
            $translation->save();
        }

        cache()->forget('settings.general');
        cache()->forget('settings.getDefaultLocales');
    }
}
