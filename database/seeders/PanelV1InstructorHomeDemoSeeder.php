<?php

namespace Database\Seeders;

use App\Models\CourseLearning;
use App\Models\File;
use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\Models\WebinarChapter;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Real DB demo rows for panel_v1 instructor home:
 * active quizzes, assignments + pending grading, upcoming sessions,
 * and partial session progress — no controller Mock.
 *
 * Target: hodinio-instructor-v2pjq / mahmoud.wahba.lavaloon@gmail.com
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorCoursesDemoSeeder --force
 *   php artisan db:seed --class=PanelV1InstructorFinanceDemoSeeder --force
 *   php artisan db:seed --class=PanelV1InstructorHomeDemoSeeder --force
 */
class PanelV1InstructorHomeDemoSeeder extends Seeder
{
    public const TEACHER_USERNAME = 'hodinio-instructor-v2pjq';
    public const TEACHER_EMAIL = 'mahmoud.wahba.lavaloon@gmail.com';

    public function run(): void
    {
        $now = time();
        $locale = app()->getLocale() ?: 'ar';

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

        // Keep profile fields aligned with the account the user logs in with.
        $teacher->fill([
            'full_name' => $teacher->full_name ?: 'Hodinio Instructor',
            'email' => self::TEACHER_EMAIL,
            'verified' => true,
            'status' => User::$active,
        ]);
        $teacher->save();

        $activeWebinars = Webinar::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)
                    ->orWhere('creator_id', $teacher->id);
            })
            ->where('status', Webinar::$active)
            ->orderByDesc('id')
            ->get();

        if ($activeWebinars->isEmpty()) {
            $this->command?->warn('No active webinars. Run PanelV1InstructorCoursesDemoSeeder first.');
            return;
        }

        $buyers = $this->ensureBuyers($now);
        $quizCount = 0;
        $assignmentCount = 0;
        $pendingCount = 0;

        foreach ($activeWebinars->take(4) as $index => $webinar) {
            $chapter = $this->ensureChapter($webinar, $teacher->id, $locale, $now);

            $quizTitles = [
                'اختبار تقييم الوحدة الأولى',
                'اختبار تطبيقي: الجودة والامتثال',
                'اختبار قصير: القيادة الفعّالة',
                'اختبار ختامي للدورة',
            ];

            $quiz = Quiz::updateOrCreate(
                [
                    'webinar_id' => $webinar->id,
                    'creator_id' => $teacher->id,
                    'chapter_id' => $chapter->id,
                ],
                [
                    'time' => 20,
                    'attempt' => 3,
                    'pass_mark' => 60,
                    'certificate' => 1,
                    'status' => Quiz::ACTIVE,
                    'total_mark' => 100,
                    'created_at' => $now - (($index + 1) * 7200),
                ]
            );

            QuizTranslation::updateOrCreate(
                ['quiz_id' => $quiz->id, 'locale' => $locale],
                [
                    'title' => $quizTitles[$index % count($quizTitles)],
                    'description' => 'اختبار تجريبي مرتبط بالدورة لعرض لوحة المدرب.',
                ]
            );
            $quizCount++;

            $assignmentTitles = [
                'تسليم خطة تحسين العملية',
                'تقرير حالة عملي',
                'مشروع تطبيقي قصير',
                'واجب مراجعة معايير الجودة',
            ];

            $assignment = WebinarAssignment::query()
                ->where('webinar_id', $webinar->id)
                ->where('creator_id', $teacher->id)
                ->where('chapter_id', $chapter->id)
                ->first();

            if (empty($assignment)) {
                $assignment = WebinarAssignment::create([
                    'creator_id' => $teacher->id,
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapter->id,
                    'grade' => 100,
                    'pass_grade' => 50,
                    'deadline' => 14,
                    'attempts' => 2,
                    'check_previous_parts' => false,
                    'status' => 'active',
                    'created_at' => $now - (($index + 1) * 5400),
                ]);
            } else {
                $assignment->update([
                    'grade' => 100,
                    'pass_grade' => 50,
                    'deadline' => 14,
                    'attempts' => 2,
                    'status' => 'active',
                ]);
            }

            WebinarAssignmentTranslation::updateOrCreate(
                ['webinar_assignment_id' => $assignment->id, 'locale' => $locale],
                [
                    'title' => $assignmentTitles[$index % count($assignmentTitles)],
                    'description' => 'واجب تجريبي بانتظار تصحيح المدرب.',
                ]
            );
            $assignmentCount++;

            // 1–2 pending submissions per assignment from different buyers
            foreach (array_slice($buyers, 0, 2) as $bIndex => $buyer) {
                $history = WebinarAssignmentHistory::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $buyer->id,
                    ],
                    [
                        'instructor_id' => $teacher->id,
                        'grade' => null,
                        'status' => WebinarAssignmentHistory::$pending,
                        'created_at' => $now - ((($index * 2) + $bIndex + 1) * 3600),
                    ]
                );
                if ($history->wasRecentlyCreated || $history->status === WebinarAssignmentHistory::$pending) {
                    $pendingCount++;
                }
            }

            $file = $this->ensureDemoFile($webinar, $chapter, $teacher->id, $locale, $now);

            // Link buyers with sales + learning progress so course cards show real %
            foreach (array_slice($buyers, 0, 3) as $bIndex => $buyer) {
                Sale::updateOrCreate(
                    [
                        'buyer_id' => $buyer->id,
                        'webinar_id' => $webinar->id,
                        'type' => Sale::$webinar,
                        'manual_added' => true,
                    ],
                    [
                        'seller_id' => $teacher->id,
                        'payment_method' => Sale::$credit,
                        'amount' => max(100, (float) ($webinar->price ?? 300)),
                        'discount' => 0,
                        'total_amount' => max(100, (float) ($webinar->price ?? 300)),
                        'commission' => 20,
                        'tax' => 0,
                        'created_at' => $now - (($index + $bIndex + 1) * 86400),
                    ]
                );

                if (!empty($file)) {
                    CourseLearning::updateOrCreate(
                        [
                            'user_id' => $buyer->id,
                            'file_id' => $file->id,
                        ],
                        [
                            'session_id' => null,
                            'text_lesson_id' => null,
                            'created_at' => $now - (($bIndex + 1) * 1800),
                        ]
                    );
                }

                QuizzesResult::updateOrCreate(
                    [
                        'quiz_id' => $quiz->id,
                        'user_id' => $buyer->id,
                    ],
                    [
                        'results' => '[]',
                        'user_grade' => 70 + ($bIndex * 5),
                        'status' => 'passed',
                        'created_at' => $now - (($bIndex + 1) * 2400),
                    ]
                );
            }
        }

        $this->refreshUpcomingSessions($teacher->id, $now);

        $this->command?->info(sprintf(
            'Home demo ready for #%d (%s): quizzes=%d assignments=%d pending≈%d',
            $teacher->id,
            $teacher->email,
            $quizCount,
            $assignmentCount,
            $pendingCount
        ));
    }

    private function ensureChapter(Webinar $webinar, int $creatorId, string $locale, int $now): WebinarChapter
    {
        $chapter = WebinarChapter::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'order' => 1,
            ],
            [
                'user_id' => $creatorId,
                'status' => WebinarChapter::$chapterActive,
                'check_all_contents_pass' => false,
                'created_at' => $now,
            ]
        );

        WebinarChapterTranslation::updateOrCreate(
            ['webinar_chapter_id' => $chapter->id, 'locale' => $locale],
            ['title' => 'الوحدة الأولى']
        );

        return $chapter;
    }

    private function ensureDemoFile(Webinar $webinar, WebinarChapter $chapter, int $creatorId, string $locale, int $now): ?File
    {
        $file = File::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'file' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'creator_id' => $creatorId,
                'volume' => '0',
                'file_type' => 'video',
                'accessibility' => 'paid',
                'storage' => 'youtube',
                'downloadable' => false,
                'check_previous_parts' => false,
                'online_viewer' => false,
                'status' => File::$Active,
                'created_at' => $now,
            ]
        );

        FileTranslation::updateOrCreate(
            ['file_id' => $file->id, 'locale' => $locale],
            [
                'title' => 'محاضرة مسجلة — مقدمة',
                'description' => 'محتوى تجريبي لحساب التقدم في لوحة المدرب.',
            ]
        );

        return $file;
    }

    private function refreshUpcomingSessions(int $teacherId, int $now): void
    {
        $webinarIds = Webinar::query()
            ->where(function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId)->orWhere('creator_id', $teacherId);
            })
            ->where('type', Webinar::$webinar)
            ->where('status', Webinar::$active)
            ->pluck('id');

        if ($webinarIds->isEmpty()) {
            return;
        }

        $sessions = Session::query()
            ->whereIn('webinar_id', $webinarIds)
            ->orderBy('id')
            ->get();

        foreach ($sessions as $i => $session) {
            $session->update([
                'date' => $now + (($i + 1) * 86400) + (16 * 3600) + (50 * 60),
                'status' => 'active',
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * @return User[]
     */
    private function ensureBuyers(int $now): array
    {
        $defs = [
            ['email' => 'home.student1@demo.com', 'name' => 'سارة الأحمد', 'user' => 'home-student-1'],
            ['email' => 'home.student2@demo.com', 'name' => 'محمد العتيبي', 'user' => 'home-student-2'],
            ['email' => 'home.student3@demo.com', 'name' => 'نورة الشمري', 'user' => 'home-student-3'],
            ['email' => 'finance.buyer1@demo.com', 'name' => 'سارة أحمد', 'user' => 'finance-buyer-1'],
            ['email' => 'finance.buyer2@demo.com', 'name' => 'محمد العتيبي', 'user' => 'finance-buyer-2'],
        ];

        $buyers = [];
        foreach ($defs as $def) {
            $buyers[] = User::updateOrCreate(
                ['email' => $def['email']],
                [
                    'full_name' => $def['name'],
                    'username' => $def['user'],
                    'password' => Hash::make('123456'),
                    'role_name' => Role::$user,
                    'role_id' => Role::getUserRoleId(),
                    'status' => User::$active,
                    'verified' => true,
                    'created_at' => $now,
                ]
            );
        }

        return $buyers;
    }
}
