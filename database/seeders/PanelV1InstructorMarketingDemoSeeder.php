<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\DiscountCourse;
use App\Models\Role;
use App\Models\SpecialOffer;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Demo marketing rows for panel_v1 instructor marketing page.
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorMarketingDemoSeeder --force
 */
class PanelV1InstructorMarketingDemoSeeder extends Seeder
{
    public const TEACHER_USERNAME = 'hodinio-instructor-v2pjq';
    public const TEACHER_EMAIL = 'mahmoud.wahba.lavaloon@gmail.com';

    public function run(): void
    {
        $now = time();

        $teacher = User::query()
            ->where(function ($q) {
                $q->where('username', self::TEACHER_USERNAME)
                    ->orWhere('email', self::TEACHER_EMAIL);
            })
            ->where('role_name', Role::$teacher)
            ->first();

        if (empty($teacher)) {
            $this->command?->warn('Hodinio instructor not found.');
            return;
        }

        $webinars = Webinar::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)
                    ->orWhere('creator_id', $teacher->id);
            })
            ->where('status', Webinar::$active)
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        if ($webinars->isEmpty()) {
            $this->command?->warn('No active webinars for marketing demo.');
            return;
        }

        $coupons = 0;
        $couponDefs = [
            ['title' => 'قسيمة ترحيب الطلاب', 'percent' => 15, 'code' => 'WELCOME15'],
            ['title' => 'خصم نهاية الأسبوع', 'percent' => 20, 'code' => 'WEEKEND20'],
            ['title' => 'عرض خاص للمسجلين الجدد', 'percent' => 25, 'code' => 'NEW25'],
        ];

        foreach ($couponDefs as $i => $def) {
            $discount = Discount::updateOrCreate(
                [
                    'creator_id' => $teacher->id,
                    'code' => $def['code'],
                ],
                [
                    'title' => $def['title'],
                    'discount_type' => Discount::$discountTypePercentage,
                    'source' => $i === 0 ? Discount::$discountSourceAll : Discount::$discountSourceCourse,
                    'percent' => $def['percent'],
                    'count' => 50 + ($i * 10),
                    'user_type' => 'all_users',
                    'status' => 'active',
                    'expired_at' => $now + ((30 + $i * 10) * 86400),
                    'created_at' => $now - (($i + 1) * 86400),
                ]
            );

            if ($i > 0) {
                $course = $webinars[$i % $webinars->count()];
                DiscountCourse::query()
                    ->where('discount_id', $discount->id)
                    ->delete();
                DiscountCourse::create([
                    'discount_id' => $discount->id,
                    'course_id' => $course->id,
                    'created_at' => $now - (($i + 1) * 86400),
                ]);
            }

            $coupons++;
        }

        $offers = 0;
        foreach ($webinars->take(3) as $i => $webinar) {
            SpecialOffer::query()
                ->where('webinar_id', $webinar->id)
                ->where('creator_id', $teacher->id)
                ->where('name', 'like', 'عرض تجريبي%')
                ->delete();

            SpecialOffer::create([
                'creator_id' => $teacher->id,
                'name' => 'عرض تجريبي ' . ($i + 1),
                'webinar_id' => $webinar->id,
                'percent' => 10 + ($i * 5),
                'status' => $i === 2 ? SpecialOffer::$inactive : SpecialOffer::$active,
                'created_at' => $now - (($i + 1) * 3600),
                'from_date' => $now - 86400,
                'to_date' => $now + ((7 + $i * 3) * 86400),
            ]);
            $offers++;
        }

        $this->command?->info(sprintf(
            'Marketing demo ready for #%d: coupons=%d offers=%d',
            $teacher->id,
            $coupons,
            $offers
        ));
    }
}
