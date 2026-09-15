<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Role;
use App\Models\Session;
use App\Models\Translation\CategoryTranslation;
use App\Models\Translation\SessionTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo courses for panel_v1 instructor courses tabs:
 * live (webinar) / recorded (course|text_lesson) / drafts (is_draft).
 *
 * Target: hodinio-instructor-v2pjq (fallback: any active teacher).
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorCoursesDemoSeeder --force
 */
class PanelV1InstructorCoursesDemoSeeder extends Seeder
{
    public const TEACHER_USERNAME = 'hodinio-instructor-v2pjq';
    public const SLUG_PREFIX = 'p1-inst-courses-';

    public function run(): void
    {
        $now = time();
        $locale = app()->getLocale() ?: 'ar';

        $teacher = User::query()
            ->where('username', self::TEACHER_USERNAME)
            ->where('role_name', Role::$teacher)
            ->first();

        if (empty($teacher)) {
            $teacher = User::query()
                ->where('role_name', Role::$teacher)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->first();
        }

        if (empty($teacher)) {
            $this->command?->warn('No instructor found to seed courses demo data.');
            return;
        }

        $defaultImage = '/assets/landing_v1/img/home/course.webp';
        $blueprints = [
            [
                'slug' => self::SLUG_PREFIX . 'live-quality-kickoff',
                'title' => 'جلسة مباشرة: انطلاقة نظم الجودة',
                'summary' => 'محاضرة تفاعلية مباشرة لتأسيس مفاهيم الجودة وتطبيقها في بيئة العمل.',
                'category' => 'الحوكمة والجودة',
                'type' => Webinar::$webinar,
                'status' => Webinar::$active,
                'price' => 450,
                'duration' => 90,
                'sessions' => 2,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'live-leadership-lab',
                'title' => 'مختبر القيادة المباشرة',
                'summary' => 'ورش عمل مباشرة حول مهارات القيادة واتخاذ القرار.',
                'category' => 'القيادة والإدارة',
                'type' => Webinar::$webinar,
                'status' => Webinar::$active,
                'price' => 650,
                'duration' => 120,
                'sessions' => 3,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'live-compliance-qna',
                'title' => 'أسئلة وأجوبة: الامتثال والحوكمة',
                'summary' => 'جلسة مباشرة مفتوحة للإجابة على استفسارات الامتثال في المؤسسات.',
                'category' => 'القانون والامتثال',
                'type' => Webinar::$webinar,
                'status' => Webinar::$active,
                'price' => 0,
                'duration' => 60,
                'sessions' => 1,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'recorded-digital-ops',
                'title' => 'التحول الرقمي للعمليات التدريبية',
                'summary' => 'دورة مسجلة تغطي أدوات وأتمتة العمليات داخل مراكز التدريب.',
                'category' => 'التميز المؤسسي',
                'type' => Webinar::$course,
                'status' => Webinar::$active,
                'price' => 899,
                'duration' => 480,
                'sessions' => 0,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'recorded-healthcare-quality',
                'title' => 'جودة الخدمات في المنشآت الصحية',
                'summary' => 'مسار مسجل متخصص في مؤشرات الجودة وسلامة المرضى.',
                'category' => 'الإدارة الصحية',
                'type' => Webinar::$course,
                'status' => Webinar::$active,
                'price' => 1099,
                'duration' => 360,
                'sessions' => 0,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'draft-soft-skills',
                'title' => 'مسودة: مهارات التواصل المهني',
                'summary' => 'مسودة دورة قيد الإعداد حول التواصل والعرض الفعّال.',
                'category' => 'المهارات الناعمة',
                'type' => Webinar::$course,
                'status' => Webinar::$isDraft,
                'price' => null,
                'duration' => null,
                'sessions' => 0,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'draft-live-workshop',
                'title' => 'مسودة: ورشة مباشرة لإدارة التغيير',
                'summary' => 'مسودة محاضرة مباشرة لم تُنشر بعد.',
                'category' => 'القيادة والإدارة',
                'type' => Webinar::$webinar,
                'status' => Webinar::$isDraft,
                'price' => 300,
                'duration' => 90,
                'sessions' => 0,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'draft-text-handbook',
                'title' => 'مسودة: دليل الامتثال النصي',
                'summary' => 'مسودة محتوى نصي للمراجعة قبل الإرسال.',
                'category' => 'القانون والامتثال',
                'type' => Webinar::$textLesson,
                'status' => Webinar::$isDraft,
                'price' => 199,
                'duration' => 45,
                'sessions' => 0,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($blueprints as $index => $data) {
            $categoryId = $this->ensureCategory($data['category'], $locale);
            $slug = $data['slug'];

            $webinar = Webinar::query()->where('slug', $slug)->first();
            $payload = [
                'teacher_id' => $teacher->id,
                'creator_id' => $teacher->id,
                'category_id' => $categoryId,
                'type' => $data['type'],
                'status' => $data['status'],
                'private' => false,
                'thumbnail' => $defaultImage,
                'image_cover' => $defaultImage,
                'price' => $data['price'],
                'duration' => $data['duration'],
                'start_date' => $data['type'] === Webinar::$webinar
                    ? ($now + (($index + 1) * 86400))
                    : null,
                'timezone' => 'Asia/Riyadh',
                'certificate' => $data['status'] === Webinar::$active,
                'updated_at' => $now,
            ];

            if (empty($webinar)) {
                $payload['slug'] = $slug;
                $payload['created_at'] = $now - (($index + 1) * 3600);
                $webinar = Webinar::create($payload);
                $created++;
            } else {
                $webinar->fill($payload);
                $webinar->save();
                $updated++;
            }

            WebinarTranslation::updateOrCreate(
                [
                    'webinar_id' => $webinar->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'description' => '<p>' . $data['summary'] . '</p>',
                    'seo_description' => $data['summary'],
                ]
            );

            $sessionCount = (int) ($data['sessions'] ?? 0);
            if ($sessionCount > 0 && $data['type'] === Webinar::$webinar) {
                $this->ensureSessions($webinar, $teacher->id, $sessionCount, $locale, $now);
            }
        }

        $this->command?->info(sprintf(
            'Instructor courses demo ready for #%d (%s): created=%d updated=%d',
            $teacher->id,
            $teacher->username ?? $teacher->full_name,
            $created,
            $updated
        ));
    }

    private function ensureSessions(Webinar $webinar, int $creatorId, int $count, string $locale, int $now): void
    {
        $existing = Session::query()->where('webinar_id', $webinar->id)->count();
        if ($existing >= $count) {
            return;
        }

        for ($i = $existing + 1; $i <= $count; $i++) {
            $session = Session::create([
                'creator_id' => $creatorId,
                'webinar_id' => $webinar->id,
                'date' => $now + ($i * 86400),
                'duration' => 60,
                'link' => 'https://meet.example.com/' . $webinar->slug . '-s' . $i,
                'session_api' => 'local',
                'order' => $i,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            SessionTranslation::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'locale' => $locale,
                ],
                [
                    'title' => 'جلسة مباشرة ' . $i,
                    'description' => 'جلسة تجريبية للتحقق من تبويب المحاضرات المباشرة.',
                ]
            );
        }
    }

    private function ensureCategory(string $title, string $locale): int
    {
        $translation = CategoryTranslation::query()
            ->where('locale', $locale)
            ->where('title', $title)
            ->first();

        if ($translation) {
            return (int) $translation->category_id;
        }

        $category = Category::create([
            'slug' => method_exists(Category::class, 'makeSlug')
                ? Category::makeSlug($title)
                : Str::slug($title) . '-' . Str::random(4),
            'enable' => true,
        ]);

        CategoryTranslation::updateOrCreate(
            [
                'category_id' => $category->id,
                'locale' => $locale,
            ],
            [
                'title' => $title,
            ]
        );

        if (property_exists(Category::class, 'cacheKey') || isset(Category::$cacheKey)) {
            cache()->forget(Category::$cacheKey);
        }

        return (int) $category->id;
    }
}
