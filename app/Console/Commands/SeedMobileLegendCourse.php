<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\User;
use Illuminate\Console\Command;

class SeedMobileLegendCourse extends Command
{
    protected $signature = 'landing:seed-mobile-legend-course {--force : Recreate if slug already exists}';

    protected $description = 'Create a sample paid Mobile Legend course for landing_v1 listing tests';

    public function handle(): int
    {
        $slug = 'mobile-legend-course';
        $existing = Webinar::where('slug', $slug)->first();

        if ($existing && ! $this->option('force')) {
            $this->info("Course already exists: #{$existing->id} — {$existing->title}");
            $this->line('URL: ' . route('landing.v1.course-details', $existing->slug));
            $this->line('Paid listing: ' . route('landing.v1.courses-paid'));

            return self::SUCCESS;
        }

        if ($existing && $this->option('force')) {
            WebinarTranslation::where('webinar_id', $existing->id)->delete();
            $existing->delete();
            $this->warn("Deleted existing course #{$existing->id} for recreate.");
        }

        $teacher = User::where('full_name', 'like', '%محمد علي%')->first()
            ?? User::find(1069)
            ?? User::where('role_name', 'teacher')->first();

        if (! $teacher) {
            $this->error('No instructor found. Create a teacher user first.');

            return self::FAILURE;
        }

        $category = Category::query()
            ->where('enable', true)
            ->whereHas('translations', fn ($q) => $q->where('title', 'التسويق'))
            ->first()
            ?? Category::query()
                ->where('enable', true)
                ->whereHas('translations', fn ($q) => $q->where('title', 'like', '%تسويق%'))
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END')
                ->first();

        if (! $category) {
            $category = Category::where('enable', true)->whereNotNull('parent_id')->first()
                ?? Category::where('enable', true)->first();
        }

        if (! $category) {
            $this->error('No enabled category found.');

            return self::FAILURE;
        }

        $reference = Webinar::where('status', Webinar::$active)
            ->where('price', '>', 0)
            ->whereNotNull('thumbnail')
            ->orderByDesc('id')
            ->first();

        $thumbnail = $reference?->thumbnail ?? '/store/1/default_images/webinar.jpg';
        $imageCover = $reference?->image_cover ?? $thumbnail;
        $now = time();

        $webinar = Webinar::create([
            'type' => Webinar::$course,
            'slug' => $slug,
            'teacher_id' => $teacher->id,
            'creator_id' => $teacher->id,
            'thumbnail' => $thumbnail,
            'image_cover' => $imageCover,
            'video_demo' => null,
            'video_demo_source' => null,
            'sales_count_number' => 0,
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
                'summary' => 'دورة تدريبية مدفوعة لاختبار ظهور الدورات الجديدة في صفحة الدورات المعتمدة.',
                'description' => '<p>دورة Mobile Legend Course — محتوى تجريبي لاختبار القائمة والتفاصيل على landing_v1.</p>',
                'seo_description' => 'دورة Mobile Legend Course — برنامج تدريبي مدفوع من مركز QIEC لاختبار عرض الدورات الحديثة في الموقع.',
            ]
        );

        $this->info("Created paid course #{$webinar->id}: Mobile Legend Course");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $webinar->id],
                ['Slug', $webinar->slug],
                ['Price', $webinar->price],
                ['Status', $webinar->status],
                ['Private', $webinar->private ? 'yes' : 'no'],
                ['Teacher', "{$teacher->id} — {$teacher->full_name}"],
                ['Category', "{$category->id} — {$category->title}"],
                ['Created at', date('Y-m-d H:i:s', $webinar->created_at)],
            ]
        );

        $this->newLine();
        $this->line('Course page: ' . route('landing.v1.course-details', $webinar->slug));
        $this->line('Paid listing:  ' . route('landing.v1.courses-paid'));
        $this->line('All courses:   ' . route('landing.v1.courses'));

        $inPaidQuery = Webinar::where('status', Webinar::$active)
            ->where('private', false)
            ->where('price', '>', 0)
            ->where('id', $webinar->id)
            ->exists();

        $this->newLine();
        $this->info($inPaidQuery
            ? '✓ Course matches courses-paid filters (active, public, paid).'
            : '✗ Course does NOT match courses-paid filters — check status/price/private.');

        return self::SUCCESS;
    }
}
