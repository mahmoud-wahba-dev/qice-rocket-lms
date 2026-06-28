<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Translation\WebinarExtraDescriptionTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarExtraDescription;
use App\Models\WebinarReview;
use App\User;
use Illuminate\Console\Command;

class SeedMobileLegendCourse extends Command
{
    protected $signature = 'landing:seed-mobile-legend-course {--force : Delete and recreate the course from scratch}';

    protected $description = 'Create or enrich Mobile Legend Course with full landing_v1 detail-page data';

    public function handle(): int
    {
        $slug = 'mobile-legend-course';
        $existing = Webinar::where('slug', $slug)->first();

        if ($existing && $this->option('force')) {
            $this->deleteCourseTree($existing);
            $existing = null;
        }

        $teacher = User::where('full_name', 'like', '%محمد علي%')->first()
            ?? User::find(1016)
            ?? User::where('role_name', 'teacher')->first();

        if (! $teacher) {
            $this->error('No instructor found. Create a teacher user first.');

            return self::FAILURE;
        }

        if (! $existing) {
            $existing = $this->createCourse($teacher);
            $this->info("Created course #{$existing->id}: Mobile Legend Course");
        } else {
            $this->info("Enriching existing course #{$existing->id}: {$existing->title}");
        }

        $this->enrichTeacherProfile($teacher);
        $this->seedCourseDetails($existing, $teacher);

        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $existing->id],
                ['Slug', $existing->slug],
                ['Chapters', $existing->chapters()->where('status', WebinarChapter::$chapterActive)->count()],
                ['Reviews', $existing->reviews()->where('status', 'active')->count()],
                ['Teacher', "{$teacher->id} — {$teacher->full_name}"],
                ['Course URL', route('landing.v1.course-details', $existing->slug)],
            ]
        );

        return self::SUCCESS;
    }

    private function deleteCourseTree(Webinar $webinar): void
    {
        WebinarReview::where('webinar_id', $webinar->id)->delete();
        WebinarExtraDescription::where('webinar_id', $webinar->id)->each(function ($item) {
            WebinarExtraDescriptionTranslation::where('webinar_extra_description_id', $item->id)->delete();
            $item->delete();
        });

        foreach ($webinar->chapters as $chapter) {
            WebinarChapterTranslation::where('webinar_chapter_id', $chapter->id)->delete();
            $chapter->delete();
        }

        WebinarTranslation::where('webinar_id', $webinar->id)->delete();
        $webinar->delete();
        $this->warn('Deleted existing Mobile Legend course tree.');
    }

    private function createCourse(User $teacher): Webinar
    {
        $category = Category::query()
            ->where('enable', true)
            ->whereHas('translations', fn ($q) => $q->where('title', 'التسويق'))
            ->first()
            ?? Category::where('enable', true)->first();

        $reference = Webinar::where('status', Webinar::$active)
            ->where('price', '>', 0)
            ->whereNotNull('thumbnail')
            ->orderByDesc('id')
            ->first();

        $now = time();

        $webinar = Webinar::create([
            'type' => Webinar::$course,
            'slug' => 'mobile-legend-course',
            'teacher_id' => $teacher->id,
            'creator_id' => $teacher->id,
            'thumbnail' => $reference?->thumbnail ?? '/store/1/default_images/webinar.jpg',
            'image_cover' => $reference?->image_cover ?? '/store/1/default_images/webinar.jpg',
            'video_demo' => null,
            'video_demo_source' => null,
            'sales_count_number' => 4,
            'capacity' => 50,
            'duration' => 35,
            'access_days' => 7,
            'support' => true,
            'certificate' => true,
            'downloadable' => false,
            'private' => false,
            'forum' => false,
            'price' => 299,
            'category_id' => $category->id,
            'status' => Webinar::$active,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        WebinarTranslation::updateOrCreate(
            ['webinar_id' => $webinar->id, 'locale' => 'ar'],
            [
                'title' => 'Mobile Legend Course',
                'summary' => 'ملخص ملخص ملخص',
                'description' => '<p>دورة Mobile Legend Course — برنامج تدريبي متكامل في التسويق الرقمي وصناعة المحتوى لمجتمع الرياضات الإلكترونية.</p>',
                'seo_description' => 'دورة Mobile Legend Course — تعلّم التسويق الرقمي وبناء العلامة الشخصية في عالم Mobile Legends مع مدرب معتمد.',
            ]
        );

        return $webinar;
    }

    private function enrichTeacherProfile(User $teacher): void
    {
        $bio = 'Ricardo dave has a BS and MS in Mechanical Engineering from Santa Clara University and years of experience as a professi...';

        $teacher->update([
            'created_at' => strtotime('2021-01-01 00:00:00'),
            'headline' => 'مدرب معتمد في التسويق الرقمي وصناعة المحتوى',
            'about' => $bio,
            'bio' => $bio,
        ]);

        $this->line('Updated instructor profile: محمد علي (member since 2021).');
    }

    private function seedCourseDetails(Webinar $webinar, User $teacher): void
    {
        WebinarTranslation::updateOrCreate(
            ['webinar_id' => $webinar->id, 'locale' => 'ar'],
            [
                'title' => 'Mobile Legend Course',
                'summary' => 'ملخص ملخص ملخص',
                'description' => '<p>دورة Mobile Legend Course — برنامج تدريبي متكامل في التسويق الرقمي وصناعة المحتوى لمجتمع الرياضات الإلكترونية.</p>',
                'seo_description' => 'دورة Mobile Legend Course — تعلّم التسويق الرقمي وبناء العلامة الشخصية في عالم Mobile Legends مع مدرب معتمد.',
            ]
        );

        $this->seedChapters($webinar, $teacher);
        $this->seedLearningMaterials($webinar, $teacher);
        $this->seedReviews($webinar);
    }

    private function seedChapters(Webinar $webinar, User $teacher): void
    {
        foreach ($webinar->chapters as $chapter) {
            WebinarChapterTranslation::where('webinar_chapter_id', $chapter->id)->delete();
            $chapter->delete();
        }

        $modules = [
            'مقدمة في Mobile Legends والتسويق الرقمي',
            'بناء العلامة الشخصية للاعب والمدرب',
            'استراتيجيات البث المباشر وجذب الجمهور',
            'تحليل البيانات وإدارة الحملات الإعلانية',
            'صناعة المحتوى القصير لمنصات التواصل',
            'مشروع نهائي: إطلاق حملة تسويقية متكاملة',
        ];

        $now = time();
        foreach ($modules as $index => $title) {
            $chapter = WebinarChapter::create([
                'user_id' => $teacher->id,
                'webinar_id' => $webinar->id,
                'title' => $title,
                'order' => $index + 1,
                'status' => WebinarChapter::$chapterActive,
                'check_all_contents_pass' => false,
                'created_at' => $now,
            ]);

            WebinarChapterTranslation::updateOrCreate(
                ['webinar_chapter_id' => $chapter->id, 'locale' => 'ar'],
                ['title' => $title]
            );
        }

        $this->line('Seeded ' . count($modules) . ' curriculum chapters.');
    }

    private function seedLearningMaterials(Webinar $webinar, User $teacher): void
    {
        WebinarExtraDescription::where('webinar_id', $webinar->id)->each(function ($item) {
            WebinarExtraDescriptionTranslation::where('webinar_extra_description_id', $item->id)->delete();
            $item->delete();
        });

        $topics = [
            'أساسيات Mobile Legends وفهم جمهور اللاعبين',
            'بناء استراتيجية محتوى تجذب المتابعين',
            'تحويل شغف اللعب إلى فرص مهنية',
            'إدارة الحملات الرقمية بميزانية محدودة',
        ];

        $now = time();
        foreach ($topics as $order => $value) {
            $item = WebinarExtraDescription::create([
                'creator_id' => $teacher->id,
                'webinar_id' => $webinar->id,
                'type' => WebinarExtraDescription::$LEARNING_MATERIALS,
                'order' => $order + 1,
                'created_at' => $now,
            ]);

            WebinarExtraDescriptionTranslation::updateOrCreate(
                ['webinar_extra_description_id' => $item->id, 'locale' => 'ar'],
                ['value' => $value]
            );
        }

        $this->line('Seeded ' . count($topics) . ' learning-outcome topics.');
    }

    private function seedReviews(Webinar $webinar): void
    {
        WebinarReview::where('webinar_id', $webinar->id)->delete();

        $reviewers = User::query()
            ->where('id', '!=', $webinar->teacher_id)
            ->whereIn('role_name', ['user', 'student', 'organization'])
            ->limit(5)
            ->pluck('id')
            ->all();

        if (count($reviewers) < 3) {
            $reviewers = array_merge($reviewers, User::query()
                ->where('id', '!=', $webinar->teacher_id)
                ->limit(5)
                ->pluck('id')
                ->all());
        }

        $reviewers = array_values(array_unique($reviewers));

        $samples = [
            ['rates' => 5, 'description' => 'دورة ممتازة! استفدت كثيراً من محتوى التسويق الرقمي وتطبيقات Mobile Legends العملية.'],
            ['rates' => 5, 'description' => 'شرح واضح ومنظم، والمدرب محمد علي يمتلك خبرة حقيقية في المجال.'],
            ['rates' => 4, 'description' => 'محتوى غني ومناسب للمبتدئين ومحترفي صناعة المحتوى على حد سواء.'],
            ['rates' => 5, 'description' => 'أفضل دورة جربتها في التسويق للرياضات الإلكترونية — أنصح بها بشدة.'],
            ['rates' => 4, 'description' => 'تجربة تعليمية ممتازة مع أمثلة تطبيقية وتمارين عملية مفيدة.'],
        ];

        $now = time();
        foreach ($samples as $index => $sample) {
            $creatorId = $reviewers[$index % count($reviewers)] ?? $webinar->teacher_id;
            $rate = $sample['rates'];

            WebinarReview::create([
                'webinar_id' => $webinar->id,
                'creator_id' => $creatorId,
                'content_quality' => $rate,
                'instructor_skills' => $rate,
                'purchase_worth' => $rate,
                'support_quality' => max(4, $rate),
                'rates' => (string) $rate,
                'description' => $sample['description'],
                'created_at' => $now - (($index + 1) * 86400),
                'status' => 'active',
            ]);
        }

        $this->line('Seeded ' . count($samples) . ' reviews / comments.');
    }
}
