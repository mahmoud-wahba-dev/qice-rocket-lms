<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\CourseLearning;
use App\Models\File;
use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\Role;
use App\Models\Sale;
use App\Models\TimeSpentOnCourse;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo students + progress for a specific instructor course performance page.
 *
 * Target slug: p1-inst-courses-live-leadership-lab
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorCoursePerformanceDemoSeeder --force
 */
class PanelV1InstructorCoursePerformanceDemoSeeder extends Seeder
{
    public const COURSE_SLUG = 'p1-inst-courses-live-leadership-lab';

    public function run(): void
    {
        $now = time();

        $webinar = Webinar::query()->where('slug', self::COURSE_SLUG)->first();
        if (empty($webinar)) {
            $this->command?->warn('Course not found: ' . self::COURSE_SLUG);
            return;
        }

        $teacherId = (int) ($webinar->teacher_id ?: $webinar->creator_id);
        $students = $this->ensureStudents($now);
        $assignment = WebinarAssignment::query()->where('webinar_id', $webinar->id)->orderByDesc('id')->first();
        $quiz = Quiz::query()->where('webinar_id', $webinar->id)->orderByDesc('id')->first();
        $files = File::query()->where('webinar_id', $webinar->id)->orderBy('id')->limit(3)->get();

        $profiles = [
            ['progress_files' => 1, 'seconds' => 2400, 'assignment' => 'pending', 'quiz' => 'waiting', 'cert' => false],
            ['progress_files' => 2, 'seconds' => 5400, 'assignment' => 'passed', 'quiz' => 'passed', 'cert' => false],
            ['progress_files' => 3, 'seconds' => 9000, 'assignment' => 'passed', 'quiz' => 'passed', 'cert' => true],
            ['progress_files' => 1, 'seconds' => 1200, 'assignment' => 'not_passed', 'quiz' => 'failed', 'cert' => false],
            ['progress_files' => 2, 'seconds' => 3600, 'assignment' => 'pending', 'quiz' => 'passed', 'cert' => false],
            ['progress_files' => 0, 'seconds' => 300, 'assignment' => null, 'quiz' => null, 'cert' => false],
        ];

        $createdSales = 0;
        foreach ($students as $i => $student) {
            $profile = $profiles[$i % count($profiles)];

            $sale = Sale::updateOrCreate(
                [
                    'buyer_id' => $student->id,
                    'webinar_id' => $webinar->id,
                    'type' => Sale::$webinar,
                ],
                [
                    'seller_id' => $teacherId,
                    'payment_method' => Sale::$credit,
                    'amount' => 500,
                    'discount' => ($i % 2) * 50,
                    'total_amount' => 500 - (($i % 2) * 50),
                    'commission' => 40,
                    'tax' => 0,
                    'created_at' => $now - (($i + 1) * 86400),
                ]
            );
            $createdSales++;

            TimeSpentOnCourse::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'course_id' => $webinar->id,
                    'page' => 'learning_page',
                ],
                [
                    'entry_time' => $now - 7200,
                    'exit_time' => $now - 3600,
                    'seconds_spent' => (int) $profile['seconds'],
                ]
            );

            if ($files->isNotEmpty()) {
                foreach ($files->take(max(0, (int) $profile['progress_files'])) as $file) {
                    CourseLearning::updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'file_id' => $file->id,
                        ],
                        [
                            'created_at' => $now - (($i + 1) * 1800),
                        ]
                    );
                }
            }

            if ($assignment && !empty($profile['assignment'])) {
                WebinarAssignmentHistory::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'instructor_id' => $teacherId,
                        'status' => $profile['assignment'],
                        'grade' => $profile['assignment'] === 'passed' ? 85 : ($profile['assignment'] === 'not_passed' ? 40 : null),
                        'created_at' => $now - (($i + 1) * 3600),
                    ]
                );
            }

            if ($quiz && !empty($profile['quiz'])) {
                QuizzesResult::updateOrCreate(
                    [
                        'quiz_id' => $quiz->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'results' => json_encode([]),
                        'user_grade' => $profile['quiz'] === 'passed' ? 80 : 45,
                        'status' => $profile['quiz'],
                        'created_at' => $now - (($i + 1) * 2400),
                    ]
                );
            }

            if (!empty($profile['cert'])) {
                Certificate::updateOrCreate(
                    [
                        'webinar_id' => $webinar->id,
                        'student_id' => $student->id,
                        'type' => 'course',
                    ],
                    [
                        'created_at' => $now - 1800,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            'Performance demo ready for course #%d (%s): students/sales≈%d',
            $webinar->id,
            $webinar->slug,
            $createdSales
        ));
    }

    private function ensureStudents(int $now): array
    {
        $defs = [
            ['email' => 'perf.student1@demo.com', 'name' => 'ليان العتيبي', 'user' => 'perf-student-1'],
            ['email' => 'perf.student2@demo.com', 'name' => 'فهد الشمري', 'user' => 'perf-student-2'],
            ['email' => 'perf.student3@demo.com', 'name' => 'هدى القحطاني', 'user' => 'perf-student-3'],
            ['email' => 'perf.student4@demo.com', 'name' => 'سامي الحربي', 'user' => 'perf-student-4'],
            ['email' => 'perf.student5@demo.com', 'name' => 'نوف الدوسري', 'user' => 'perf-student-5'],
            ['email' => 'perf.student6@demo.com', 'name' => 'ماجد السبيعي', 'user' => 'perf-student-6'],
        ];

        $students = [];
        foreach ($defs as $def) {
            $students[] = User::updateOrCreate(
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

        return $students;
    }
}
