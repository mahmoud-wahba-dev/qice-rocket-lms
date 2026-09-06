<?php

namespace App\Http\Controllers\PanelV1\Support;

class InstructorMockData
{
    public static function common(): array
    {
        return [
            'instructorName' => 'علا محمد',
            'instructorEmail' => 'ola@qiec.example',
            'demoSlug' => 'demo',
            'demoAssignmentId' => 1,
        ];
    }

    public static function home(): array
    {
        return array_merge(self::common(), [
            'stats' => [
                ['label' => 'إجمالي الأرباح', 'value' => '0.00 ر.س'],
                ['label' => 'إجمالي الطلاب', 'value' => '0'],
                ['label' => 'الدورات النشطة', 'value' => '0'],
                ['label' => 'تكاليف وواجبات', 'value' => '0'],
                ['label' => 'الاختبارات النشطة', 'value' => '2'],
            ],
            'courses' => [
                [
                    'title' => 'قياس النجاح والجودة',
                    'subtitle' => 'الادارة والتنفيذ',
                    'progress' => 72,
                ],
                [
                    'title' => 'قياس النجاح والجودة',
                    'subtitle' => 'الادارة والتنفيذ',
                    'progress' => 72,
                ],
                [
                    'title' => 'قياس النجاح والجودة',
                    'subtitle' => 'الادارة والتنفيذ',
                    'progress' => 72,
                ],
            ],
            'quickActions' => [
                ['label' => 'انشاء دورة جديدة', 'route' => 'panel.v1.instructor.courses'],
                ['label' => 'انشاء اختبار جديد', 'route' => null],
                ['label' => 'عرض جميع الطلاب', 'route' => null],
                ['label' => 'عرض جميع التكليفات', 'route' => 'panel.v1.instructor.assignments'],
            ],
            'upcomingLectures' => [
                ['title' => 'مراجعة معايير السلامة CPHQ', 'when' => 'اليوم - 08:00 مساءً'],
                ['title' => 'مراجعة معايير السلامة CPHQ', 'when' => 'اليوم - 08:00 مساءً'],
            ],
            'pendingGrading' => [
                ['title' => 'مقال معايير الجودة الشاملة', 'student' => 'hiba', 'time' => 'منذ ساعتين'],
                ['title' => 'مقال معايير الجودة الشاملة', 'student' => 'hiba', 'time' => 'منذ ساعتين'],
            ],
        ]);
    }

    public static function courses(): array
    {
        $card = [
            'title' => 'قياس النجاح والجودة',
            'subtitle' => 'الإدارة والتنفيذ',
            'type' => 'دورة مسجلة',
            'activity' => '0:01',
            'duration' => '30:00',
            'lectures' => 12,
            'assignments' => 0,
            'progress' => 72,
        ];

        return array_merge(self::common(), [
            'courseCards' => [$card, $card, $card],
        ]);
    }

    public static function coursePerformance(string $slug): array
    {
        return array_merge(self::common(), [
            'slug' => $slug ?: 'demo',
            'courseTitle' => 'لوحة أداء الدورة',
            'courseSubtitle' => 'الفارس المعتمد في جودة الرعاية الصحية (CPHQ)',
            'alertText' => 'مهام تشغيلية تتطلب تدخلك اليوم: 10 واجبات تنتظر التصحيح والتقييم، 2 طلب تأخير عن موعد واحد',
            'perfStats' => [
                ['value' => '3 واجبات', 'label' => 'بانتظار التصحيح', 'tone' => 'red'],
                ['value' => '5 طلاب', 'label' => 'متأخرين عن جدول التقدم', 'tone' => 'yellow'],
                ['value' => '12 طالب', 'label' => 'أكملوا كافة متطلبات الدورة', 'tone' => 'green'],
            ],
            'students' => [
                [
                    'name' => 'علا محمد',
                    'email' => 'ola@example.com',
                    'progress' => 85,
                    'activity' => '04:30',
                    'lectures' => 12,
                    'assignments' => 5,
                    'certificates' => 2,
                ],
                [
                    'name' => 'أحمد خالد',
                    'email' => 'ahmed@example.com',
                    'progress' => 62,
                    'activity' => '02:10',
                    'lectures' => 8,
                    'assignments' => 3,
                    'certificates' => 1,
                ],
                [
                    'name' => 'سارة علي',
                    'email' => 'sara@example.com',
                    'progress' => 40,
                    'activity' => '01:20',
                    'lectures' => 5,
                    'assignments' => 1,
                    'certificates' => 0,
                ],
            ],
        ]);
    }

    public static function assignments(): array
    {
        return array_merge(self::common(), [
            'assignmentStats' => [
                ['value' => '12 تكليف', 'label' => 'بانتظار التصحيح'],
                ['value' => '145 تكليف', 'label' => 'تم تصحيحها'],
                ['value' => '157 تكليف', 'label' => 'تسلمت'],
            ],
            'currentAssignments' => [
                [
                    'title' => 'تصميم تجربة واجهة المستخدم - User Experience Design',
                    'date' => 'الإثنين 17 أغسطس 2024',
                    'submissions' => '8 / 40',
                    'corrected' => '12 طالب',
                    'pending' => '13 طالب',
                    'progress' => 40,
                    'studentsCount' => 10,
                ],
                [
                    'title' => 'تصميم تجربة واجهة المستخدم - User Experience Design',
                    'date' => 'الثلاثاء 18 أغسطس 2024',
                    'submissions' => '10 / 40',
                    'corrected' => '15 طالب',
                    'pending' => '10 طالب',
                    'progress' => 55,
                    'studentsCount' => 12,
                ],
            ],
            'resultsRows' => [
                [
                    'instructor' => 'علا محمد',
                    'title' => 'UI مخطط الواجهة',
                    'course' => 'قياس النجاح والجودة',
                    'first' => '15 يوليو 2024',
                    'last' => '15 أغسطس 2024',
                    'attempts' => 2,
                    'grade' => '25 / 50',
                    'end' => '20 أغسطس 2024',
                    'status' => 'في انتظار التقييم',
                    'statusTone' => 'pending',
                ],
                [
                    'instructor' => 'علا محمد',
                    'title' => 'مقال معايير الجودة',
                    'course' => 'قياس النجاح والجودة',
                    'first' => '10 يوليو 2024',
                    'last' => '12 أغسطس 2024',
                    'attempts' => 1,
                    'grade' => '40 / 50',
                    'end' => '18 أغسطس 2024',
                    'status' => 'فعال',
                    'statusTone' => 'active',
                ],
            ],
        ]);
    }

    public static function courseAssignments(string $slug): array
    {
        return array_merge(self::common(), [
            'slug' => $slug ?: 'demo',
            'pageTitleMain' => 'متطلبات دوراتي / السلام',
            'pageSubtitle' => 'الممارس المعتمد في جودة الرعاية الصحية CPHQ',
            'summaryCards' => [
                ['label' => 'إجمالي التكليفات', 'value' => '2', 'edge' => '#0f4c45'],
                ['label' => 'التكليفات المكتملة', 'value' => '1', 'edge' => '#0FC787'],
                ['label' => 'قيد المراجعة', 'value' => '2', 'edge' => '#F59E0B'],
                ['label' => 'معدل انجاز المعلم', 'value' => '2', 'edge' => '#8B5CF6'],
            ],
            'submissions' => [
                [
                    'name' => 'علا محمد',
                    'submitted_at' => '15 يوليو 2024',
                    'updated_at' => '15 أغسطس 2024',
                    'grade' => '25 / 50',
                    'status' => 'تمت المراجعة',
                ],
                [
                    'name' => 'أحمد خالد',
                    'submitted_at' => '16 يوليو 2024',
                    'updated_at' => '16 أغسطس 2024',
                    'grade' => '30 / 50',
                    'status' => 'تمت المراجعة',
                ],
            ],
        ]);
    }

    public static function assignmentReview(int $id): array
    {
        return array_merge(self::common(), [
            'assignmentId' => $id ?: 1,
            'slug' => 'demo',
            'reviewTitle' => 'تكليف المحاضرة العاشرة: تطبيق معايير إشراك المريض في تحسين الجودة',
            'detailsTitle' => 'تفاصيل التكليف والمحتوى المطلوب',
            'detailsBody' => 'اكتب مقالاً تحليلياً يناقش أهمية إشراك المريض في تحسين الجودة داخل أقسام الطوارئ.',
            'pointsTitle' => 'نقاط يجب تغطيتها في المقال',
            'points' => [
                'مقدمة عن مفهوم إشراك المريض',
                'أمثلة تطبيقية من بيئة العمل',
                'خاتمة وتوصيات عملية',
            ],
            'studentAnswer' => 'يُعد إشراك المريض عنصراً أساسياً في تحسين جودة الخدمات الصحية، حيث يسهم في رفع مستوى الرضا وتقليل الأخطاء وتعزيز التواصل الفعّال بين الفريق الطبي والمريض.',
            'attachmentName' => 'خطة_تحسين_تجربة_المريض_الطوارئ.pdf',
            'maxGrade' => 50,
            'passGrade' => 25,
            'course' => [
                'title' => 'قياس النجاح والجودة',
                'subtitle' => 'الادارة والتنفيذ',
                'progress' => 85,
            ],
            'chapters' => [
                [
                    'title' => 'الباب الأول',
                    'completed' => true,
                    'expanded' => true,
                    'subtitle' => 'هنا عنوان المحاضرة',
                    'items' => [
                        ['title' => 'فيديو تعريفي', 'type' => 'video', 'active' => true],
                        ['title' => 'شرح نظري + أمثلة', 'type' => 'text', 'active' => false],
                    ],
                ],
                ['title' => 'الباب الثاني', 'completed' => false, 'expanded' => false, 'subtitle' => '', 'items' => []],
                ['title' => 'الباب الثالث', 'completed' => false, 'expanded' => false, 'subtitle' => '', 'items' => []],
            ],
        ]);
    }

    public static function consultations(): array
    {
        return array_merge(self::common(), [
            'session' => [
                'title' => 'جلسة مراجعة متطلبات CPHQ',
                'instructor' => 'سارة السليمان',
                'price' => '200 ر.س',
                'status' => 'مدفوع',
                'date' => '10 أغسطس 2024',
                'time' => '08:00 م - 09:45 م',
                'linkLabel' => 'لقاء أونلاين',
            ],
            'attendees' => [
                [
                    'initials' => 'HI',
                    'name' => 'Hala Ibrahim',
                    'email' => 'hala@example.com',
                    'joinType' => 'وجهًا لوجه',
                    'day' => 'السبت',
                    'date' => '25 أغسطس 2024',
                    'time' => '09:00 - 10:30',
                    'amount' => '0',
                    'files' => 1,
                    'status' => 'تم الحضور',
                ],
            ],
        ]);
    }
}
