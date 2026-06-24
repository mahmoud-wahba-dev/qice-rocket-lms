<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Role;
use App\Models\Translation\BlogCategoryTranslation;
use App\Models\Translation\BlogTranslation;
use App\Models\Translation\CategoryTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;

class LandingV1DemoDataSeeder extends Seeder
{
    private const SLUG_PREFIX = 'qiec-demo-';

    private const USER_PREFIX = 'qiec-demo-';

    public function run(): void
    {
        $locale = mb_strtolower(config('app.locale', 'ar'));

        [$instructorsCreated, $instructorsSkipped, $teacherIds] = $this->seedDemoInstructors();

        $teacher = !empty($teacherIds)
            ? User::query()->find($teacherIds[0])
            : null;

        if (!$teacher) {
            $teacher = User::query()
                ->where('role_name', Role::$teacher)
                ->where('status', 'active')
                ->first();
        }

        if (!$teacher) {
            $teacher = User::query()
                ->where('role_name', Role::$admin)
                ->where('status', 'active')
                ->first();
        }

        if (!$teacher) {
            $this->command?->error('No active teacher or admin user found. Create a user first.');

            return;
        }

        [$workshopsCreated, $workshopsSkipped] = $this->seedDemoWorkshops($teacher, $teacherIds, $locale);
        [$paidCreated, $paidSkipped] = $this->seedDemoPaidCourses($teacherIds, $locale);
        [$blogsCreated, $blogsSkipped] = $this->seedDemoBlogs($teacher, $locale);

        $this->command?->info("Landing demo instructors: {$instructorsCreated} created, {$instructorsSkipped} already existed.");
        $this->command?->info("Landing demo workshops: {$workshopsCreated} created, {$workshopsSkipped} already existed.");
        $this->command?->info("Landing demo paid courses: {$paidCreated} created, {$paidSkipped} already existed.");
        $this->command?->info("Landing demo blogs: {$blogsCreated} created, {$blogsSkipped} already existed.");
    }

    /**
     * @return array{0: int, 1: int, 2: array<int>}
     */
    private function seedDemoInstructors(): array
    {
        $roleId = Role::getTeacherRoleId();
        $created = 0;
        $skipped = 0;
        $teacherIds = [];

        $instructors = [
            [
                'username' => self::USER_PREFIX . 'm-abu-haisha',
                'email' => self::USER_PREFIX . 'm-abu-haisha@qiec.local',
                'full_name' => 'د. محمد أبو هيشة',
                'headline' => 'استشاري الإدارة الصحية',
                'bio' => 'استشاري الإدارة الصحية',
                'about' => 'دكتوراه في الإدارة والتخطيط الصحي. استشاري الإدارة الصحية والمستشفيات. عمل مديرًا عامًا لعدة إدارات عامة بوزارة الصحة لمدة 30 سنة.',
            ],
            [
                'username' => self::USER_PREFIX . 'a-alqahtani',
                'email' => self::USER_PREFIX . 'a-alqahtani@qiec.local',
                'full_name' => 'د. عبدالله القحطاني',
                'headline' => 'خبير الحوكمة والتطوير المؤسسي',
                'bio' => 'خبير الحوكمة المؤسسية',
                'about' => 'دكتوراه في إدارة الأعمال. خبير في الحوكمة والتطوير المؤسسي. مدرب معتمد في برامج التميز المؤسسي.',
            ],
            [
                'username' => self::USER_PREFIX . 's-alotaibi',
                'email' => self::USER_PREFIX . 's-alotaibi@qiec.local',
                'full_name' => 'د. سارة العتيبي',
                'headline' => 'استشارية الجودة والاعتماد',
                'bio' => 'استشارية الجودة الصحية',
                'about' => 'دكتوراه في الجودة والاعتماد المؤسسي. استشارية في نظم الجودة الصحية. خبرة تتجاوز 20 عامًا في التدريب والتطوير.',
            ],
            [
                'username' => self::USER_PREFIX . 'k-alshammari',
                'email' => self::USER_PREFIX . 'k-alshammari@qiec.local',
                'full_name' => 'د. خالد الشمري',
                'headline' => 'مستشار الامتثال والحوكمة',
                'bio' => 'مستشار الامتثال والحوكمة',
                'about' => 'ماجستير في القانون الإداري. مستشار في الامتثال والحوكمة. مدرب في برامج القيادة والإدارة الاستراتيجية.',
            ],
            [
                'username' => self::USER_PREFIX . 'n-aldosari',
                'email' => self::USER_PREFIX . 'n-aldosari@qiec.local',
                'full_name' => 'د. نورة الدوسري',
                'headline' => 'خبيرة التخطيط الاستراتيجي',
                'bio' => 'خبيرة التخطيط الاستراتيجي',
                'about' => 'دكتوراه في التخطيط الاستراتيجي. خبيرة في تطوير الكفاءات القيادية. مدربة معتمدة في برامج التطوير المهني.',
            ],
            [
                'username' => self::USER_PREFIX . 'f-alharbi',
                'email' => self::USER_PREFIX . 'f-alharbi@qiec.local',
                'full_name' => 'د. فهد الحربي',
                'headline' => 'مدرب التميز المؤسسي',
                'bio' => 'مدرب التميز المؤسسي',
                'about' => 'ماجستير في إدارة الجودة الشاملة. مدرب معتمد في نماذج التميز الحكومي والخاص. خبرة 15 عامًا في الاستشارات المؤسسية.',
            ],
        ];

        foreach ($instructors as $data) {
            $existing = User::query()
                ->where('email', $data['email'])
                ->orWhere('username', $data['username'])
                ->first();

            if ($existing) {
                $teacherIds[] = $existing->id;
                $skipped++;
                continue;
            }

            $user = User::create([
                'username' => $data['username'],
                'full_name' => $data['full_name'],
                'role_name' => Role::$teacher,
                'role_id' => $roleId,
                'email' => $data['email'],
                'password' => User::generatePassword('qiec-demo-2026'),
                'status' => 'active',
                'verified' => true,
                'headline' => $data['headline'],
                'bio' => $data['bio'],
                'about' => $data['about'],
                'affiliate' => false,
                'created_at' => time(),
            ]);

            $teacherIds[] = $user->id;
            $created++;
        }

        return [$created, $skipped, $teacherIds];
    }

    /**
     * @param array<int> $teacherIds
     * @return array{0: int, 1: int}
     */
    private function seedDemoWorkshops(User $fallbackTeacher, array $teacherIds, string $locale): array
    {
        $workshops = [
            [
                'slug' => self::SLUG_PREFIX . 'saudi-work-intro',
                'title' => 'مقدمة في مفاهيم العمل السعودي',
                'category' => 'التطوير المهني',
                'summary' => 'ورشة تعريفية تستعرض أهم مفاهيم سوق العمل السعودي، فرص التوظيف، ومتطلبات الانضمام للقطاع الخاص.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'corporate-governance-basics',
                'title' => 'أساسيات الحوكمة المؤسسية',
                'category' => 'الحوكمة والجودة',
                'summary' => 'محاضرة مجانية تشرح مبادئ الحوكمة، أدوار اللجان، وكيف تُبنى ثقافة الامتثال داخل المؤسسات.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'health-administration-intro',
                'title' => 'مبادئ الإدارة الصحية',
                'category' => 'الإدارة الصحية',
                'summary' => 'تعرف على أساسيات إدارة المنشآت الصحية، جودة الخدمات، ودور القيادة في تحسين تجربة المستفيد.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'institutional-excellence',
                'title' => 'التميز المؤسسي للمبتدئين',
                'category' => 'التميز المؤسسي',
                'summary' => 'جلسة تمهيدية حول نماذج التميز، مؤشرات الأداء، وكيفية بناء خطة تحسين مؤسسية عملية.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'professional-communication',
                'title' => 'مهارات التواصل المهني',
                'category' => 'المهارات الناعمة',
                'summary' => 'ورشة عملية لتطوير مهارات التواصل في بيئة العمل، العروض التقديمية، والتعامل مع الفرق.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'training-development-intro',
                'title' => 'مقدمة في التدريب والتطوير',
                'category' => 'التطوير المهني',
                'summary' => 'استكشف دور التدريب في رفع كفاءة الموظفين، وكيف تختار البرامج المناسبة لاحتياجات فريقك.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'labor-law-for-training-centers',
                'title' => 'قانون العمل للمؤسسات التدريبية',
                'category' => 'القانون والامتثال',
                'summary' => 'محاضرة توضح أبرز أحكام نظام العمل ذات الصلة بمراكز التدريب والجهات التعليمية.',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'high-performance-teams',
                'title' => 'بناء فرق العمل عالية الأداء',
                'category' => 'القيادة والإدارة',
                'summary' => 'تعلم كيف تُشكّل فرقاً فعالة، توزّع المهام، وتحفّز الأداء ضمن بيئة عمل تعاونية.',
            ],
        ];

        $created = 0;
        $skipped = 0;
        $teacherPool = !empty($teacherIds) ? $teacherIds : [$fallbackTeacher->id];

        foreach ($workshops as $index => $data) {
            $categoryId = $this->ensureCategory($data['category'], $locale);
            $teacherId = $teacherPool[$index % count($teacherPool)];

            if (Webinar::query()->where('slug', $data['slug'])->exists()) {
                $skipped++;
                continue;
            }

            $webinar = Webinar::create([
                'type' => Webinar::$course,
                'slug' => $data['slug'],
                'teacher_id' => $teacherId,
                'creator_id' => $teacherId,
                'thumbnail' => null,
                'image_cover' => null,
                'video_demo' => null,
                'video_demo_source' => null,
                'category_id' => $categoryId,
                'price' => null,
                'status' => Webinar::$active,
                'private' => false,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => $locale,
            ], [
                'title' => $data['title'],
                'summary' => $data['summary'],
                'description' => '<p>' . $data['summary'] . '</p>',
                'seo_description' => $data['summary'],
            ]);

            $created++;
        }

        return [$created, $skipped];
    }

    /**
     * @param array<int> $teacherIds
     * @return array{0: int, 1: int}
     */
    private function seedDemoPaidCourses(array $teacherIds, string $locale): array
    {
        $created = 0;
        $skipped = 0;
        $teacherPool = $teacherIds;
        $defaultImage = '/assets/landing_v1/img/home/course.webp';

        $courses = [
            [
                'slug' => self::SLUG_PREFIX . 'digital-marketing',
                'title' => 'التسويق الرقمي الاحترافي',
                'category' => 'التسويق',
                'summary' => 'برنامج معتمد يغطي استراتيجيات التسويق الرقمي، إدارة الحملات، وتحليل الأداء.',
                'price' => 699,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'project-management',
                'title' => 'إدارة المشاريع الاحترافية',
                'category' => 'القيادة والإدارة',
                'summary' => 'تعلم أدوات وتقنيات إدارة المشاريع وفق أفضل الممارسات العالمية.',
                'price' => 899,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'quality-management',
                'title' => 'نظم الجودة الشاملة',
                'category' => 'الحوكمة والجودة',
                'summary' => 'دورة متقدمة في بناء وتطبيق نظم الجودة داخل المؤسسات.',
                'price' => 799,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'healthcare-leadership',
                'title' => 'القيادة في المنشآت الصحية',
                'category' => 'الإدارة الصحية',
                'summary' => 'برنامج تدريبي متخصص في مهارات القيادة والإدارة للقطاع الصحي.',
                'price' => 1199,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'institutional-excellence-pro',
                'title' => 'التميز المؤسسي المتقدم',
                'category' => 'التميز المؤسسي',
                'summary' => 'مسار تدريبي معتمد لتطبيق نماذج التميز وقياس الأداء المؤسسي.',
                'price' => 999,
            ],
            [
                'slug' => self::SLUG_PREFIX . 'compliance-program',
                'title' => 'برنامج الامتثال والحوكمة',
                'category' => 'القانون والامتثال',
                'summary' => 'تطبيق عملي لمتطلبات الامتثال والحوكمة في بيئات العمل السعودية.',
                'price' => 749,
            ],
        ];

        foreach ($courses as $index => $data) {
            if (Webinar::query()->where('slug', $data['slug'])->exists()) {
                $skipped++;
                continue;
            }

            $categoryId = $this->ensureCategory($data['category'], $locale);
            $teacherId = !empty($teacherPool) ? $teacherPool[$index % count($teacherPool)] : null;

            if (empty($teacherId)) {
                continue;
            }

            $webinar = Webinar::create([
                'type' => Webinar::$course,
                'slug' => $data['slug'],
                'teacher_id' => $teacherId,
                'creator_id' => $teacherId,
                'thumbnail' => $defaultImage,
                'image_cover' => $defaultImage,
                'video_demo' => null,
                'video_demo_source' => null,
                'category_id' => $categoryId,
                'price' => $data['price'],
                'status' => Webinar::$active,
                'private' => false,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => $locale,
            ], [
                'title' => $data['title'],
                'summary' => $data['summary'],
                'description' => '<p>' . $data['summary'] . '</p>',
                'seo_description' => $data['summary'],
            ]);

            $created++;
        }

        return [$created, $skipped];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function seedDemoBlogs(User $author, string $locale): array
    {
        $created = 0;
        $skipped = 0;
        $categoryId = $this->ensureBlogCategory('أخبار المركز', $locale);
        $defaultImage = '/assets/landing_v1/img/home/news1.webp';

        $posts = [
            [
                'slug' => self::SLUG_PREFIX . 'choose-training-course',
                'title' => 'استثمر في نفسك: كيف تختار الدورة التدريبية المناسبة؟',
                'subtitle' => 'دليل عملي لاختيار المسار التدريبي الأنسب لمسيرتك المهنية',
                'description' => 'نصائح عملية لاختيار الدورة التدريبية المناسبة وفق أهدافك المهنية.',
                'content' => '<p>اختيار الدورة التدريبية المناسبة خطوة محورية في تطوير مسيرتك المهنية. ابدأ بتحديد هدفك، ثم قارن بين البرامج المعتمدة، والمدربين، وطريقة التدريب قبل التسجيل.</p>',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'institutional-excellence-news',
                'title' => 'التميز المؤسسي: أين تبدأ رحلتك؟',
                'subtitle' => 'مقدمة إلى مفاهيم التميز وقياس الأداء',
                'description' => 'تعرف على أساسيات التميز المؤسسي وكيف تبدأ خطة التحسين في مؤسستك.',
                'content' => '<p>التميز المؤسسي ليس مشروعاً قصير المدى، بل رحلة مستمرة لتحسين الأداء ورفع جودة الخدمات. في هذا المقال نستعرض نقطة البداية العملية لبناء ثقافة التميز.</p>',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'free-workshops-benefits',
                'title' => 'لماذا تحضر الورش المجانية قبل الالتحاق بالدورات المعتمدة؟',
                'subtitle' => 'فوائد الورش التعريفية للمتدربين الجدد',
                'description' => 'كيف تساعدك الورش المجانية على اتخاذ قرار التسجيل الصحيح.',
                'content' => '<p>الورش والمحاضرات المجانية تمنحك فرصة للتعرف على أسلوب المدرب ومحتوى البرنامج قبل الالتزام بدورة معتمدة مدفوعة.</p>',
            ],
            [
                'slug' => self::SLUG_PREFIX . 'healthcare-training-trends',
                'title' => 'أبرز اتجاهات التدريب في القطاع الصحي',
                'subtitle' => 'مهارات مطلوبة لقادة المنشآت الصحية',
                'description' => 'نظرة على أهم مجالات التدريب في الإدارة الصحية اليوم.',
                'content' => '<p>يشهد القطاع الصحي تحولات سريعة تتطلب مهارات جديدة في القيادة، الجودة، وإدارة التغيير. إليك أبرز الاتجاهات التدريبية التي يبحث عنها القطاع.</p>',
            ],
        ];

        foreach ($posts as $data) {
            if (Blog::query()->where('slug', $data['slug'])->exists()) {
                $skipped++;
                continue;
            }

            $blog = Blog::create([
                'slug' => $data['slug'],
                'category_id' => $categoryId,
                'author_id' => $author->id,
                'image' => $defaultImage,
                'enable_comment' => false,
                'status' => 'publish',
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            BlogTranslation::updateOrCreate([
                'blog_id' => $blog->id,
                'locale' => $locale,
            ], [
                'title' => $data['title'],
                'subtitle' => $data['subtitle'],
                'description' => $data['description'],
                'meta_description' => $data['description'],
                'content' => $data['content'],
            ]);

            $created++;
        }

        return [$created, $skipped];
    }

    private function ensureBlogCategory(string $title, string $locale): int
    {
        $translation = BlogCategoryTranslation::query()
            ->where('locale', $locale)
            ->where('title', $title)
            ->first();

        if ($translation) {
            return $translation->blog_category_id;
        }

        $category = BlogCategory::create([
            'slug' => BlogCategory::makeSlug($title),
        ]);

        BlogCategoryTranslation::updateOrCreate([
            'blog_category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $title,
        ]);

        return $category->id;
    }

    private function ensureCategory(string $title, string $locale): int
    {
        $translation = CategoryTranslation::query()
            ->where('locale', $locale)
            ->where('title', $title)
            ->first();

        if ($translation) {
            return $translation->category_id;
        }

        $category = Category::create([
            'slug' => Category::makeSlug($title),
            'enable' => true,
        ]);

        CategoryTranslation::updateOrCreate([
            'category_id' => $category->id,
            'locale' => $locale,
        ], [
            'title' => $title,
        ]);

        cache()->forget(Category::$cacheKey);

        return $category->id;
    }
}
