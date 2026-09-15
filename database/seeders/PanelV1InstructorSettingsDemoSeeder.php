<?php

namespace Database\Seeders;

use App\Bitwise\UserLevelOfTraining;
use App\Models\Region;
use App\Models\Role;
use App\Models\UserMeta;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Demo profile settings for panel_v1 instructor settings page.
 *
 * Target: hodinio-instructor-v2pjq
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorSettingsDemoSeeder --force
 */
class PanelV1InstructorSettingsDemoSeeder extends Seeder
{
    public const TEACHER_USERNAME = 'hodinio-instructor-v2pjq';

    public function run(): void
    {
        $teacher = User::query()
            ->where('username', self::TEACHER_USERNAME)
            ->where('role_name', Role::$teacher)
            ->first();

        if (empty($teacher)) {
            $this->command?->warn('Hodinio instructor not found.');
            return;
        }

        $countryId = Region::query()->where('type', Region::$country)->orderBy('id')->value('id');
        $provinceId = $countryId
            ? Region::query()->where('type', Region::$province)->where('country_id', $countryId)->orderBy('id')->value('id')
            : null;
        $cityId = $provinceId
            ? Region::query()->where('type', Region::$city)->where('province_id', $provinceId)->orderBy('id')->value('id')
            : null;

        $levels = (new UserLevelOfTraining())->getValue(['middle', 'expert']);

        $teacher->update([
            'offline' => true,
            'offline_message' => 'أنا حالياً في إجازة قصيرة. سأعود للرد على الاستفسارات خلال أيام العمل القادمة.',
            'language' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'newsletter' => true,
            'public_message' => true,
            'enable_profile_statistics' => true,
            'auto_renew_subscription' => false,
            'meeting_type' => 'all',
            'level_of_training' => $levels,
            'country_id' => $countryId,
            'province_id' => $provinceId,
            'city_id' => $cityId,
            'address' => 'الرياض — حي العليا',
            'headline' => 'مدرب موثق في الجودة والتميز المؤسسي',
            'bio' => 'مدرب متخصص في نظم الجودة والقيادة الصحية مع خبرة عملية في اعتماد المنشآت.',
            'about' => '<p>خبرة واسعة في تدريب فرق الجودة، إعداد مؤشرات الأداء، وبناء برامج تدريبية تطبيقية للمؤسسات الصحية والتعليمية.</p>',
            'verified' => true,
        ]);

        $this->upsertMeta($teacher->id, 'gender', 'man');
        $this->upsertMeta($teacher->id, 'birthday', (string) strtotime('1988-05-12'));
        $this->upsertMeta($teacher->id, 'socials', json_encode([
            'twitter' => 'https://x.com/hodinio',
            'linkedin' => 'https://linkedin.com/in/hodinio-instructor',
            'instagram' => 'https://instagram.com/hodinio',
        ], JSON_UNESCAPED_UNICODE));
        $this->upsertMeta($teacher->id, 'education', 'ماجستير إدارة الجودة — جامعة الملك سعود');
        $this->upsertMeta($teacher->id, 'experience', '10+ سنوات في التدريب والاستشارات المؤسسية');

        $this->command?->info('Instructor settings demo seeded for #' . $teacher->id);
    }

    private function upsertMeta(int $userId, string $name, string $value): void
    {
        UserMeta::query()->where('user_id', $userId)->where('name', $name)->delete();
        UserMeta::query()->create([
            'user_id' => $userId,
            'name' => $name,
            'value' => $value,
        ]);
    }
}
