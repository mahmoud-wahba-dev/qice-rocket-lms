<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Demo consultations for panel_v1 instructor consultations page.
 *
 * Target: hodinio-instructor-v2pjq
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorConsultationsDemoSeeder --force
 */
class PanelV1InstructorConsultationsDemoSeeder extends Seeder
{
    public const TEACHER_USERNAME = 'hodinio-instructor-v2pjq';

    public function run(): void
    {
        $now = time();

        $teacher = User::query()
            ->where('username', self::TEACHER_USERNAME)
            ->where('role_name', Role::$teacher)
            ->first();

        if (empty($teacher)) {
            $this->command?->warn('Hodinio instructor not found.');
            return;
        }

        $meeting = Meeting::query()->firstOrCreate(
            ['creator_id' => $teacher->id],
            [
                'amount' => 150,
                'discount' => 0,
                'in_person' => true,
                'in_person_amount' => 200,
                'group_meeting' => true,
                'online_group_min_student' => 2,
                'online_group_max_student' => 8,
                'online_group_amount' => 120,
                'in_person_group_min_student' => 2,
                'in_person_group_max_student' => 6,
                'in_person_group_amount' => 180,
                'enable_meeting_packages' => false,
                'disabled' => false,
                'created_at' => $now,
            ]
        );

        $slots = [
            ['day_label' => 'sunday', 'time' => '10:00-11:00', 'meeting_type' => 'online', 'description' => 'جلسة استشارية: جودة العمليات'],
            ['day_label' => 'tuesday', 'time' => '18:00-19:30', 'meeting_type' => 'online', 'description' => 'جلسة مباشرة: مؤشرات الأداء'],
            ['day_label' => 'thursday', 'time' => '16:00-17:00', 'meeting_type' => 'in_person', 'description' => 'استشارة حضورية: التميز المؤسسي'],
        ];

        $timeIds = [];
        foreach ($slots as $slot) {
            $time = MeetingTime::query()->firstOrCreate(
                [
                    'meeting_id' => $meeting->id,
                    'day_label' => $slot['day_label'],
                    'time' => $slot['time'],
                ],
                [
                    'meeting_type' => $slot['meeting_type'],
                    'description' => $slot['description'],
                    'created_at' => $now,
                ]
            );
            $timeIds[] = $time->id;
        }

        $students = User::query()
            ->where('role_name', Role::$user)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        if ($students->isEmpty()) {
            $this->command?->warn('No students found to reserve consultations.');
            return;
        }

        $blueprints = [
            ['days' => 2, 'status' => ReserveMeeting::$open, 'type' => 'online', 'amount' => 150, 'students' => 1],
            ['days' => 5, 'status' => ReserveMeeting::$pending, 'type' => 'online', 'amount' => 150, 'students' => 1],
            ['days' => 9, 'status' => ReserveMeeting::$open, 'type' => 'in_person', 'amount' => 200, 'students' => 3],
            ['days' => -3, 'status' => ReserveMeeting::$finished, 'type' => 'online', 'amount' => 150, 'students' => 1],
        ];

        $created = 0;
        foreach ($blueprints as $i => $bp) {
            $student = $students[$i % $students->count()];
            $timeId = $timeIds[$i % count($timeIds)];
            $slot = MeetingTime::query()->find($timeId);
            $dayTs = strtotime(date('Y-m-d', $now + ((int) $bp['days'] * 86400)));
            $startParts = explode('-', (string) ($slot->time ?? '10:00-11:00'));
            $startClock = trim($startParts[0] ?? '10:00');
            $endClock = trim($startParts[1] ?? '11:00');
            $startAt = strtotime(date('Y-m-d', $dayTs) . ' ' . date('H:i', strtotime($startClock)));
            $endAt = strtotime(date('Y-m-d', $dayTs) . ' ' . date('H:i', strtotime($endClock)));
            if ($endAt <= $startAt) {
                $endAt = $startAt + 3600;
            }

            $reserve = ReserveMeeting::query()->firstOrCreate(
                [
                    'user_id' => $student->id,
                    'meeting_time_id' => $timeId,
                    'day' => date('Y-m-d', $dayTs),
                ],
                [
                    'meeting_id' => null,
                    'sale_id' => null,
                    'date' => $dayTs,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'paid_amount' => $bp['amount'],
                    'meeting_type' => $bp['type'],
                    'student_count' => $bp['students'],
                    'discount' => 0,
                    'link' => null,
                    'password' => null,
                    'description' => $slot->description ?? 'جلسة استشارية تجريبية',
                    'status' => $bp['status'],
                    'created_at' => $now - (($i + 1) * 3600),
                    'locked_at' => null,
                    'reserved_at' => $now - (($i + 1) * 7200),
                ]
            );

            if ($reserve->wasRecentlyCreated) {
                $created++;
            } else {
                $reserve->update([
                    'date' => $dayTs,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'status' => $bp['status'],
                    'paid_amount' => $bp['amount'],
                    'meeting_type' => $bp['type'],
                    'student_count' => $bp['students'],
                    'link' => null,
                ]);
            }
        }

        $this->command?->info(sprintf(
            'Consultations demo for #%d: slots=%d reserves_created=%d',
            $teacher->id,
            count($timeIds),
            $created
        ));
    }
}
