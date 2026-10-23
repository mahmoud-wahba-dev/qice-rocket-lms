<?php

namespace Database\Seeders;

use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentAttachment;
use App\Models\WebinarChapter;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds a test assignment WITH instructor files for student testing.
 * Creates one assignment per active chapter so it shows regardless of which lesson is open.
 *
 * php artisan db:seed --class=StudentAssignmentDemoSeeder --force
 */
class StudentAssignmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $course = Webinar::query()
            ->where('slug', 'panel-v1-student-demo-course')
            ->first();

        if (empty($course)) {
            $course = Webinar::query()
                ->where('status', Webinar::$active)
                ->where('private', false)
                ->whereHas('chapters')
                ->whereHas('sales', function ($q) {
                    $q->whereNull('refund_at');
                })
                ->orderByDesc('id')
                ->first();
        }

        if (empty($course)) {
            $this->command?->error('No suitable course found for assignment demo.');

            return;
        }

        $chapters = WebinarChapter::query()
            ->where('webinar_id', $course->id)
            ->where('status', 'active')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($chapters->isEmpty()) {
            $this->command?->error('Course has no active chapters.');

            return;
        }

        $creatorId = (int) ($course->teacher_id ?: $course->creator_id ?: User::query()->where('role_name', 'teacher')->value('id') ?: 1);

        $title = 'تكليف تجريبي: تقرير جودة مختصر';
        $description = '<p>هذا تكليف تجريبي للاختبار. حمّل ملفات المدرب المرفقة، ثم اكتب ملخصاً قصيراً (200–400 كلمة) وارفع ملفك إن رغبت.</p><p><strong>المطلوب:</strong> قراءة دليل التكليف المرفق وتطبيق النقاط المذكورة في ورقة العمل.</p>';

        $dir = public_path('store/assignments/demo');
        File::ensureDirectoryExists($dir);

        $files = [
            [
                'title' => 'دليل التكليف (PDF)',
                'filename' => 'assignment-brief-demo.txt',
                'content' => "تكليف تجريبي — مركز الجودة والتميز\n\n1) اقرأ التعليمات.\n2) اكتب ملخصاً قصيراً.\n3) ارفع ملف الإجابة من لوحة الطالب.\n\nالموعد: خلال 14 يوماً من الشراء.\nدرجة النجاح: 60 / 100\n",
            ],
            [
                'title' => 'ورقة عمل الطالب',
                'filename' => 'assignment-worksheet-demo.txt',
                'content' => "ورقة عمل الطالب\n\n- اسم الطالب:\n- عنوان الملخص:\n- أهم 3 نقاط مستفادة:\n1.\n2.\n3.\n",
            ],
        ];

        foreach ($files as $file) {
            File::put(public_path('store/assignments/demo/' . $file['filename']), $file['content']);
        }

        // Replace any prior demos on this course.
        $oldIds = WebinarAssignment::query()->where('webinar_id', $course->id)->pluck('id');
        if ($oldIds->isNotEmpty()) {
            WebinarAssignmentAttachment::query()->whereIn('assignment_id', $oldIds)->delete();
            WebinarAssignmentTranslation::query()->whereIn('webinar_assignment_id', $oldIds)->delete();
            WebinarAssignment::query()->whereIn('id', $oldIds)->delete();
        }

        $created = 0;
        $firstId = null;

        foreach ($chapters as $chapter) {
            $assignment = WebinarAssignment::create([
                'creator_id' => $creatorId,
                'webinar_id' => $course->id,
                'chapter_id' => $chapter->id,
                'grade' => 100,
                'pass_grade' => 60,
                'deadline' => 14,
                'attempts' => 3,
                'check_previous_parts' => false,
                'access_after_day' => null,
                'status' => 'active',
                'created_at' => time(),
            ]);

            foreach (['ar', 'en'] as $loc) {
                WebinarAssignmentTranslation::updateOrCreate(
                    [
                        'webinar_assignment_id' => $assignment->id,
                        'locale' => $loc,
                    ],
                    [
                        'title' => $title,
                        'description' => $description,
                    ]
                );
            }

            foreach ($files as $file) {
                $relative = 'store/assignments/demo/' . $file['filename'];
                WebinarAssignmentAttachment::create([
                    'creator_id' => $creatorId,
                    'assignment_id' => $assignment->id,
                    'title' => $file['title'],
                    'attach' => '/' . ltrim($relative, '/'),
                ]);
            }

            $firstId = $firstId ?? $assignment->id;
            $created++;
        }

        $this->command?->info("Created {$created} assignments on course #{$course->id} ({$course->slug})");
        $this->command?->info("First assignment #{$firstId} — 2 downloadable files each");
        $this->command?->info('Student test URL: /v1/student/courses/' . $course->slug . '/assignment');
        $this->command?->info('Login as buyer user #995 (or any purchaser of this course).');
    }
}
