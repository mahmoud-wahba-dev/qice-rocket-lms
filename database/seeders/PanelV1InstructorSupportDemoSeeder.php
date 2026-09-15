<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Seeder;

/**
 * Demo support tickets for panel_v1 instructor support page.
 *
 * Target: hodinio-instructor-v2pjq
 *
 * Run:
 *   php artisan db:seed --class=PanelV1InstructorSupportDemoSeeder --force
 */
class PanelV1InstructorSupportDemoSeeder extends Seeder
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

        $departments = SupportDepartment::query()->orderBy('id')->get();
        if ($departments->isEmpty()) {
            $this->command?->warn('No support departments found.');
            return;
        }

        $platformTickets = [
            [
                'title' => 'تأخر ظهور الأرباح في المحفظة',
                'message' => 'بعد اكتمال عملية بيع أمس لم تظهر العمولة في رصيد المحفظة. الرجاء التحقق.',
                'days_ago' => 1,
                'status' => 'open',
            ],
            [
                'title' => 'مشكلة في رفع فيديو المحاضرة',
                'message' => 'أثناء رفع ملف فيديو للوحدة الثانية يظهر خطأ بعد 80٪ من الرفع.',
                'days_ago' => 3,
                'status' => 'replied',
            ],
            [
                'title' => 'استفسار عن توثيق الهوية',
                'message' => 'رفعت مسح الهوية والشهادات منذ أسبوع ولم تتغير حالة التوثيق.',
                'days_ago' => 7,
                'status' => 'close',
            ],
        ];

        $createdPlatform = 0;
        foreach ($platformTickets as $i => $ticket) {
            $department = $departments[$i % $departments->count()];
            $createdAt = $now - ($ticket['days_ago'] * 86400) - ($i * 1800);

            $support = Support::query()->firstOrCreate(
                [
                    'user_id' => $teacher->id,
                    'department_id' => $department->id,
                    'title' => $ticket['title'],
                ],
                [
                    'webinar_id' => null,
                    'status' => $ticket['status'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            if ($support->wasRecentlyCreated) {
                $createdPlatform++;
            } else {
                $support->update([
                    'status' => $ticket['status'],
                    'updated_at' => $createdAt,
                ]);
            }

            if (!SupportConversation::query()->where('support_id', $support->id)->exists()) {
                SupportConversation::create([
                    'support_id' => $support->id,
                    'sender_id' => $teacher->id,
                    'message' => $ticket['message'],
                    'attach' => null,
                    'created_at' => $createdAt,
                ]);
            }
        }

        $webinars = Webinar::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)->orWhere('creator_id', $teacher->id);
            })
            ->where('status', Webinar::$active)
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $students = User::query()
            ->where('role_name', Role::$user)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $createdCourse = 0;
        if ($webinars->isNotEmpty() && $students->isNotEmpty()) {
            $courseTickets = [
                ['title' => 'استفسار عن واجب الوحدة الأولى', 'message' => 'هل يمكن تمديد موعد تسليم التكليف؟', 'days_ago' => 2],
                ['title' => 'رابط الجلسة المباشرة لا يعمل', 'message' => 'عند الضغط على رابط Zoom يظهر خطأ صلاحية.', 'days_ago' => 4],
                ['title' => 'محتوى الدرس الثالث غير واضح', 'message' => 'أرجو توضيح الجزء الخاص بمؤشرات الأداء.', 'days_ago' => 6],
                ['title' => 'شهادة الإتمام لم تصدر', 'message' => 'أنهيت الدورة بنسبة 100٪ ولم تظهر الشهادة.', 'days_ago' => 8],
            ];

            foreach ($courseTickets as $i => $ticket) {
                $student = $students[$i % $students->count()];
                $webinar = $webinars[$i % $webinars->count()];
                $createdAt = $now - ($ticket['days_ago'] * 86400) - ($i * 900);

                $support = Support::query()->firstOrCreate(
                    [
                        'user_id' => $student->id,
                        'webinar_id' => $webinar->id,
                        'title' => $ticket['title'],
                    ],
                    [
                        'department_id' => null,
                        'status' => 'open',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );

                if ($support->wasRecentlyCreated) {
                    $createdCourse++;
                }

                if (!SupportConversation::query()->where('support_id', $support->id)->exists()) {
                    SupportConversation::create([
                        'support_id' => $support->id,
                        'sender_id' => $student->id,
                        'message' => $ticket['message'],
                        'attach' => null,
                        'created_at' => $createdAt,
                    ]);
                }
            }
        }

        $this->command?->info(sprintf(
            'Support demo for #%d: platform=%d course=%d',
            $teacher->id,
            $createdPlatform,
            $createdCourse
        ));
    }
}
