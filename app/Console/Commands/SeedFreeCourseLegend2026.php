<?php

namespace App\Console\Commands;

use App\Models\Faq;
use App\Models\Translation\FaqTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Translation\WebinarExtraDescriptionTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarExtraDescription;
use App\Models\WebinarReview;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedFreeCourseLegend2026 extends Command
{
    protected $signature = 'landing:seed-free-course-legend-2026 {--force : Delete and recreate the course from scratch}';

    protected $description = 'Create free course legend 2026 with the same landing detail data as Mobile Legend Course';

    private const SOURCE_SLUG = 'mobile-legend-course';

    private const TARGET_SLUG = 'free-course-legend-2026';

    private const TARGET_TITLE = 'free course legend 2026';

    public function handle(): int
    {
        $source = Webinar::where('slug', self::SOURCE_SLUG)->first();
        if (! $source) {
            $this->error('Source course not found. Run: php artisan landing:seed-mobile-legend-course');

            return self::FAILURE;
        }

        $existing = Webinar::where('slug', self::TARGET_SLUG)->first();
        if ($existing && $this->option('force')) {
            $this->deleteCourseTree($existing);
            $existing = null;
        }

        if ($existing) {
            $webinar = $existing;
            $this->info("Enriching existing free course #{$webinar->id}: {$webinar->title}");
        } else {
            $webinar = $this->createCourseFromSource($source);
            $this->info("Created free course #{$webinar->id}: " . self::TARGET_TITLE);
        }

        $teacher = User::find($webinar->teacher_id) ?? User::find($source->teacher_id);
        if ($teacher) {
            $this->enrichTeacherProfile($teacher);
        }

        $this->seedCourseDetails($webinar, $source, $teacher);
        $this->seedFaq($webinar, $source, $teacher);
        $this->seedReviews($webinar, $source);

        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $webinar->id],
                ['Title', self::TARGET_TITLE],
                ['Slug', $webinar->slug],
                ['Price', $webinar->price],
                ['Chapters', $webinar->chapters()->where('status', WebinarChapter::$chapterActive)->count()],
                ['Learning topics', WebinarExtraDescription::where('webinar_id', $webinar->id)->count()],
                ['FAQs', Faq::where('webinar_id', $webinar->id)->count()],
                ['Reviews', $webinar->reviews()->where('status', 'active')->count()],
                ['Teacher', $teacher ? "{$teacher->id} — {$teacher->full_name}" : '—'],
                ['Course URL', route('landing.v1.course-details', $webinar->slug)],
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

        Faq::where('webinar_id', $webinar->id)->each(function ($faq) {
            FaqTranslation::where('faq_id', $faq->id)->delete();
            $faq->delete();
        });

        foreach ($webinar->chapters as $chapter) {
            WebinarChapterTranslation::where('webinar_chapter_id', $chapter->id)->delete();
            $chapter->delete();
        }

        WebinarTranslation::where('webinar_id', $webinar->id)->delete();
        $webinar->delete();
        $this->warn('Deleted existing free course legend 2026 tree.');
    }

    private function createCourseFromSource(Webinar $source): Webinar
    {
        $now = time();

        return Webinar::create([
            'type' => $source->type,
            'slug' => self::TARGET_SLUG,
            'teacher_id' => $source->teacher_id,
            'creator_id' => $source->creator_id,
            'thumbnail' => $source->thumbnail,
            'image_cover' => $source->image_cover,
            'video_demo' => $source->video_demo,
            'video_demo_source' => $source->video_demo_source,
            'sales_count_number' => $source->sales_count_number,
            'capacity' => $source->capacity,
            'duration' => $source->duration,
            'access_days' => $source->access_days,
            'support' => $source->support,
            'certificate' => $source->certificate,
            'downloadable' => $source->downloadable,
            'private' => false,
            'forum' => $source->forum,
            'price' => 0,
            'category_id' => $source->category_id,
            'status' => Webinar::$active,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
    }

    private function seedCourseDetails(Webinar $webinar, Webinar $source, ?User $teacher): void
    {
        $sourceTranslation = DB::table('webinar_translations')
            ->where('webinar_id', $source->id)
            ->where('locale', 'ar')
            ->first();

        WebinarTranslation::updateOrCreate(
            ['webinar_id' => $webinar->id, 'locale' => 'ar'],
            [
                'title' => self::TARGET_TITLE,
                'summary' => $sourceTranslation->summary ?? 'ملخص ملخص ملخص',
                'description' => $sourceTranslation->description ?? '<p>دورة Mobile Legend Course — برنامج تدريبي متكامل في التسويق الرقمي وصناعة المحتوى لمجتمع الرياضات الإلكترونية.</p>',
                'seo_description' => $sourceTranslation->seo_description ?? 'دورة Mobile Legend Course — تعلّم التسويق الرقمي وبناء العلامة الشخصية في عالم Mobile Legends مع مدرب معتمد.',
            ]
        );

        $this->seedChapters($webinar, $source, $teacher);
        $this->seedLearningMaterials($webinar, $source, $teacher);
    }

    private function seedChapters(Webinar $webinar, Webinar $source, ?User $teacher): void
    {
        foreach ($webinar->chapters as $chapter) {
            WebinarChapterTranslation::where('webinar_chapter_id', $chapter->id)->delete();
            $chapter->delete();
        }

        $sourceChapters = WebinarChapter::query()
            ->where('webinar_id', $source->id)
            ->where('status', WebinarChapter::$chapterActive)
            ->orderBy('order')
            ->get();

        $now = time();
        foreach ($sourceChapters as $sourceChapter) {
            $translation = DB::table('webinar_chapter_translations')
                ->where('webinar_chapter_id', $sourceChapter->id)
                ->where('locale', 'ar')
                ->first();

            $title = $translation->title ?? $sourceChapter->title;

            $chapter = WebinarChapter::create([
                'user_id' => $teacher?->id ?? $sourceChapter->user_id,
                'webinar_id' => $webinar->id,
                'title' => $title,
                'order' => $sourceChapter->order,
                'status' => WebinarChapter::$chapterActive,
                'check_all_contents_pass' => false,
                'created_at' => $now,
            ]);

            WebinarChapterTranslation::updateOrCreate(
                ['webinar_chapter_id' => $chapter->id, 'locale' => 'ar'],
                ['title' => $title]
            );
        }

        $this->line('Seeded ' . $sourceChapters->count() . ' curriculum chapters.');
    }

    private function seedLearningMaterials(Webinar $webinar, Webinar $source, ?User $teacher): void
    {
        WebinarExtraDescription::where('webinar_id', $webinar->id)->each(function ($item) {
            WebinarExtraDescriptionTranslation::where('webinar_extra_description_id', $item->id)->delete();
            $item->delete();
        });

        $sourceItems = WebinarExtraDescription::query()
            ->where('webinar_id', $source->id)
            ->where('type', WebinarExtraDescription::$LEARNING_MATERIALS)
            ->orderBy('order')
            ->get();

        $now = time();
        foreach ($sourceItems as $sourceItem) {
            $translation = DB::table('webinar_extra_description_translations')
                ->where('webinar_extra_description_id', $sourceItem->id)
                ->where('locale', 'ar')
                ->first();

            $value = trim((string) ($translation->value ?? ''));
            if ($value === '') {
                continue;
            }

            $item = WebinarExtraDescription::create([
                'creator_id' => $teacher?->id ?? $sourceItem->creator_id,
                'webinar_id' => $webinar->id,
                'type' => WebinarExtraDescription::$LEARNING_MATERIALS,
                'order' => $sourceItem->order,
                'created_at' => $now,
            ]);

            WebinarExtraDescriptionTranslation::updateOrCreate(
                ['webinar_extra_description_id' => $item->id, 'locale' => 'ar'],
                ['value' => $value]
            );
        }

        $this->line('Seeded ' . $sourceItems->count() . ' learning-outcome topics.');
    }

    private function seedFaq(Webinar $webinar, Webinar $source, ?User $teacher): void
    {
        Faq::where('webinar_id', $webinar->id)->each(function ($faq) {
            FaqTranslation::where('faq_id', $faq->id)->delete();
            $faq->delete();
        });

        $sourceFaqs = Faq::where('webinar_id', $source->id)->orderBy('order')->get();
        $now = time();

        foreach ($sourceFaqs as $sourceFaq) {
            $translation = DB::table('faq_translations')
                ->where('faq_id', $sourceFaq->id)
                ->where('locale', 'ar')
                ->first();

            $title = trim((string) ($translation->title ?? ''));
            $answer = trim((string) ($translation->answer ?? ''));
            if ($title === '' || $answer === '') {
                continue;
            }

            $faq = Faq::create([
                'creator_id' => $teacher?->id ?? $sourceFaq->creator_id,
                'webinar_id' => $webinar->id,
                'order' => $sourceFaq->order,
                'created_at' => $now,
            ]);

            FaqTranslation::updateOrCreate(
                ['faq_id' => $faq->id, 'locale' => 'ar'],
                ['title' => $title, 'answer' => $answer]
            );
        }

        $this->line('Seeded ' . $sourceFaqs->count() . ' FAQ items.');
    }

    private function seedReviews(Webinar $webinar, Webinar $source): void
    {
        WebinarReview::where('webinar_id', $webinar->id)->delete();

        $sourceReviews = WebinarReview::query()
            ->where('webinar_id', $source->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        foreach ($sourceReviews as $review) {
            WebinarReview::create([
                'webinar_id' => $webinar->id,
                'creator_id' => $review->creator_id,
                'content_quality' => $review->content_quality,
                'instructor_skills' => $review->instructor_skills,
                'purchase_worth' => $review->purchase_worth,
                'support_quality' => $review->support_quality,
                'rates' => $review->rates,
                'description' => $review->description,
                'created_at' => $review->created_at,
                'status' => 'active',
            ]);
        }

        $this->line('Seeded ' . $sourceReviews->count() . ' reviews.');
    }
}
