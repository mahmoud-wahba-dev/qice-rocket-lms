<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Translation\FaqTranslation;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Seeds dummy FAQ rows on known paid courses so landing "الأسئلة الشائعة" can be tested.
 *
 * php artisan db:seed --class=LandingCourseFaqsSeeder --force
 */
class LandingCourseFaqsSeeder extends Seeder
{
    public function run(): void
    {
        $locale = mb_strtolower((string) config('app.locale', 'ar')) ?: 'ar';

        $creatorId = (int) (User::query()->where('role_name', 'admin')->value('id')
            ?: User::query()->where('role_name', 'teacher')->value('id')
            ?: 1);

        $targetSlugs = [
            'p1-inst-courses-live-quality-kickoff',
            'p1-inst-courses-recorded-digital-ops',
            'qiec-demo-quality-management',
            'qiec-demo-digital-marketing',
            'qiec-demo-project-management',
        ];

        $faqBlueprints = [
            [
                'title' => 'هل الشهادة معتمدة؟',
                'answer' => '<p>نعم، الشهادة صادرة من مركز الجودة والتميز للتدريب ويمكن التحقق منها عبر صفحة التحقق من الشهادات على المنصة.</p>',
            ],
            [
                'title' => 'هل يمكنني الحضور عن بُعد؟',
                'answer' => '<p>نعم، البرنامج يدعم الحضور المرن (عن بُعد / حضوري) حسب طبيعة الدورة والمقاعد المتاحة.</p>',
            ],
            [
                'title' => 'ماذا لو فوّتُّ إحدى الجلسات؟',
                'answer' => '<p>يمكنك الرجوع إلى التسجيلات والمواد داخل لوحة الطالب، وإعادة متابعة المحتوى حسب مسار الدورة.</p>',
            ],
            [
                'title' => 'كيف أسجّل وأدفع؟',
                'answer' => '<p>اضغط «اشتري الآن» في صفحة الدورة، ثم أكمل الدفع عبر البوابات المتاحة أو خيارات التقسيط إن وُجدت.</p>',
            ],
            [
                'title' => 'هل يمكن استرداد المبلغ؟',
                'answer' => '<p>تُطبَّق سياسة الاسترداد المعتمدة في المنصة. تواصل مع الدعم عبر صفحة اتصل بنا للمساعدة في حالتك.</p>',
            ],
        ];

        $coursesUpdated = 0;
        $faqsCreated = 0;

        foreach ($targetSlugs as $slug) {
            $course = Webinar::query()->where('slug', $slug)->first();
            if (empty($course)) {
                $this->command?->warn("Skip missing slug: {$slug}");
                continue;
            }

            // Replace existing demo FAQs for a clean test set.
            Faq::query()->where('webinar_id', $course->id)->delete();

            foreach ($faqBlueprints as $index => $item) {
                $faq = Faq::create([
                    'creator_id' => $creatorId,
                    'webinar_id' => $course->id,
                    'order' => $index + 1,
                    'created_at' => time(),
                ]);

                FaqTranslation::updateOrCreate(
                    [
                        'faq_id' => $faq->id,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $item['title'],
                        'answer' => $item['answer'],
                    ]
                );

                $faqsCreated++;
            }

            $coursesUpdated++;
            $this->command?->info("FAQs seeded for: {$slug} (#{$course->id})");
        }

        // Fallback: if none of the preferred slugs exist, seed the first active paid course.
        if ($coursesUpdated === 0) {
            $fallback = Webinar::query()
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->where('price', '>', 0)
                ->orderByDesc('id')
                ->first();

            if ($fallback) {
                Faq::query()->where('webinar_id', $fallback->id)->delete();
                foreach ($faqBlueprints as $index => $item) {
                    $faq = Faq::create([
                        'creator_id' => $creatorId,
                        'webinar_id' => $fallback->id,
                        'order' => $index + 1,
                        'created_at' => time(),
                    ]);
                    FaqTranslation::updateOrCreate(
                        ['faq_id' => $faq->id, 'locale' => $locale],
                        ['title' => $item['title'], 'answer' => $item['answer']]
                    );
                    $faqsCreated++;
                }
                $coursesUpdated = 1;
                $this->command?->info("FAQs seeded on fallback: {$fallback->slug} (#{$fallback->id})");
            }
        }

        $this->command?->info("Landing course FAQs: {$coursesUpdated} courses, {$faqsCreated} FAQ rows.");
    }
}
