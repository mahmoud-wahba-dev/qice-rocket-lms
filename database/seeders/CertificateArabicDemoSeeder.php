<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Role;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Clears local certificates and reseeds Arabic-first QIEC demo certificates.
 *
 *   php artisan db:seed --class=CertificateArabicDemoSeeder --force
 */
class CertificateArabicDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->wipeCertificates();

        $students = $this->ensureArabicStudents();
        $courses = $this->ensureArabicCourses();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command?->error('No students/courses available — abort certificate seed.');
            return;
        }

        $now = time();
        $created = 0;

        $pairs = [
            [
                'student' => $students[0],
                'course' => $courses[0],
                'meta' => [
                    'trainee_name_ar' => 'سارة الأحمد',
                    'trainee_name_en' => 'Sara Al-Ahmad',
                    'course_title_ar' => 'إدارة الجودة الشاملة',
                    'course_title_en' => 'Total Quality Management',
                    'hours' => '16',
                    'start_date' => '01/09/2026',
                    'end_date' => '05/09/2026',
                    'issue_date' => '10/09/2026',
                    'accreditation_number' => 'QIEC-ACC-001',
                    'accredited_by_ar' => 'المؤسسة العامة للتدريب التقني والمهني',
                    'accredited_by_en' => 'Technical and Vocational Training Corporation (TVTC)',
                    'officer_name' => 'مسؤول التدريب والاعتماد',
                    'director_name' => 'مدير مركز الجودة والتميز',
                ],
                'created_at' => $now - 86400 * 5,
            ],
            [
                'student' => $students[1] ?? $students[0],
                'course' => $courses[1] ?? $courses[0],
                'meta' => [
                    'trainee_name_ar' => 'فهد الشمري',
                    'trainee_name_en' => 'Fahad Al-Shammari',
                    'course_title_ar' => 'التحول الرقمي للعمليات التدريبية',
                    'course_title_en' => 'Digital Transformation in Training Operations',
                    'hours' => '24',
                    'start_date' => '08/09/2026',
                    'end_date' => '12/09/2026',
                    'issue_date' => '15/09/2026',
                    'accreditation_number' => 'QIEC-ACC-001',
                    'accredited_by_ar' => 'المؤسسة العامة للتدريب التقني والمهني',
                    'accredited_by_en' => 'Technical and Vocational Training Corporation (TVTC)',
                    'officer_name' => 'مسؤول التدريب والاعتماد',
                    'director_name' => 'مدير مركز الجودة والتميز',
                ],
                'created_at' => $now - 86400 * 4,
            ],
            [
                'student' => $students[2] ?? $students[0],
                'course' => $courses[2] ?? $courses[0],
                'meta' => [
                    'trainee_name_ar' => 'نورة القحطاني',
                    'trainee_name_en' => 'Noura Al-Qahtani',
                    'course_title_ar' => 'قياس مؤشرات الأداء والجودة',
                    'course_title_en' => 'Performance & Quality KPIs',
                    'hours' => '12',
                    'start_date' => '14/09/2026',
                    'end_date' => '16/09/2026',
                    'issue_date' => '18/09/2026',
                    'accreditation_number' => 'QIEC-ACC-002',
                    'accredited_by_ar' => 'مركز الجودة والتميز',
                    'accredited_by_en' => 'Quality & Excellence Center',
                    'officer_name' => 'مسؤولة الاعتماد',
                    'director_name' => 'مدير المركز',
                ],
                'created_at' => $now - 86400 * 3,
            ],
            [
                'student' => $students[3] ?? $students[0],
                'course' => $courses[3] ?? $courses[0],
                'meta' => [
                    'trainee_name_ar' => 'عبدالله الحربي',
                    'trainee_name_en' => 'Abdullah Al-Harbi',
                    'course_title_ar' => 'أساسيات نظم إدارة الجودة ISO 9001',
                    'course_title_en' => 'ISO 9001 Quality Management Basics',
                    'hours' => '20',
                    'start_date' => '20/09/2026',
                    'end_date' => '24/09/2026',
                    'issue_date' => '28/09/2026',
                    'accreditation_number' => 'QIEC-ACC-001',
                    'accredited_by_ar' => 'المؤسسة العامة للتدريب التقني والمهني',
                    'accredited_by_en' => 'Technical and Vocational Training Corporation (TVTC)',
                    'officer_name' => 'مسؤول التدريب والاعتماد',
                    'director_name' => 'مدير مركز الجودة والتميز',
                ],
                'created_at' => $now - 86400 * 2,
            ],
            [
                'student' => $students[4] ?? $students[0],
                'course' => $courses[0],
                'meta' => [
                    'trainee_name_ar' => 'لمى الدوسري',
                    'trainee_name_en' => 'Lama Al-Dosari',
                    'course_title_ar' => 'ورشة تطبيقية في تحسين العمليات',
                    'course_title_en' => 'Process Improvement Workshop',
                    'hours' => '08',
                    'start_date' => '01/10/2026',
                    'end_date' => '02/10/2026',
                    'issue_date' => '05/10/2026',
                    'accreditation_number' => 'QIEC-ACC-003',
                    'accredited_by_ar' => 'مركز الجودة والتميز',
                    'accredited_by_en' => 'Quality & Excellence Center',
                    'officer_name' => 'مسؤول التدريب والاعتماد',
                    'director_name' => 'مدير المركز',
                ],
                'created_at' => $now - 86400,
            ],
            [
                'student' => $students[5] ?? $students[0],
                'course' => $courses[1] ?? $courses[0],
                'meta' => [
                    'trainee_name_ar' => 'ماجد السبيعي',
                    'trainee_name_en' => 'Majed Al-Subaie',
                    'course_title_ar' => 'جلسة مباشرة: انطلاقة نظم الجودة',
                    'course_title_en' => 'Live Session: Quality Systems Kickoff',
                    'hours' => '04',
                    'start_date' => '10/10/2026',
                    'end_date' => '10/10/2026',
                    'issue_date' => '12/10/2026',
                    'accreditation_number' => 'QIEC-ACC-001',
                    'accredited_by_ar' => 'المؤسسة العامة للتدريب التقني والمهني',
                    'accredited_by_en' => 'Technical and Vocational Training Corporation (TVTC)',
                    'officer_name' => 'مسؤول التدريب والاعتماد',
                    'director_name' => 'مدير مركز الجودة والتميز',
                ],
                'created_at' => $now - 3600,
            ],
        ];

        foreach ($pairs as $row) {
            /** @var User $student */
            $student = $row['student'];
            /** @var Webinar $course */
            $course = $row['course'];

            if (!(bool) $course->certificate) {
                $course->update(['certificate' => true]);
            }

            // Keep student display name Arabic for panel lists
            if (!empty($row['meta']['trainee_name_ar']) && $student->full_name !== $row['meta']['trainee_name_ar']) {
                $student->update(['full_name' => $row['meta']['trainee_name_ar']]);
            }

            Certificate::create([
                'webinar_id' => $course->id,
                'student_id' => $student->id,
                'type' => 'course',
                'meta' => $row['meta'],
                'created_at' => $row['created_at'],
            ]);
            $created++;
        }

        $this->command?->info("Cleared old certificates and seeded {$created} Arabic demo certificates.");
    }

    private function wipeCertificates(): void
    {
        $count = Certificate::query()->count();

        Schema::disableForeignKeyConstraints();
        DB::table('certificates')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command?->info("Removed {$count} certificate(s).");
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function ensureArabicStudents()
    {
        $defs = [
            ['email' => 'cert.sara@demo.com', 'name' => 'سارة الأحمد', 'user' => 'cert-sara'],
            ['email' => 'cert.fahad@demo.com', 'name' => 'فهد الشمري', 'user' => 'cert-fahad'],
            ['email' => 'cert.noura@demo.com', 'name' => 'نورة القحطاني', 'user' => 'cert-noura'],
            ['email' => 'cert.abdullah@demo.com', 'name' => 'عبدالله الحربي', 'user' => 'cert-abdullah'],
            ['email' => 'cert.lama@demo.com', 'name' => 'لمى الدوسري', 'user' => 'cert-lama'],
            ['email' => 'cert.majed@demo.com', 'name' => 'ماجد السبيعي', 'user' => 'cert-majed'],
            // Keep primary panel student in the mix
            ['email' => 'student@demo.com', 'name' => 'متدرب تجريبي', 'user' => 'student-demo'],
        ];

        $now = time();
        $out = collect();

        foreach ($defs as $def) {
            $out->push(User::updateOrCreate(
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
            ));
        }

        return $out->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Webinar>
     */
    private function ensureArabicCourses()
    {
        $courses = Webinar::query()
            ->where('status', Webinar::$active)
            ->where('certificate', true)
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        if ($courses->count() < 2) {
            $courses = Webinar::query()
                ->where('status', Webinar::$active)
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        // Prefer courses that already have Arabic titles
        return $courses->values();
    }
}
