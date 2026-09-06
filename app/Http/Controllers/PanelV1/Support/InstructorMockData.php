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
        $student = [
            'name' => 'علا محمد',
            'email' => 'ola@example.com',
            'progress' => 38.4,
            'activity' => '04:30 دقائق',
            'exams' => 0,
            'assignments' => '0/1',
            'certificates' => 0,
        ];

        return array_merge(self::common(), [
            'slug' => $slug ?: 'demo',
            'courseTitle' => 'لوحة اداء الدورة',
            'courseSubtitle' => 'الممارس المعتمد في جودة الرعاية الصحية CPHQ',
            'alertText' => 'مهام تشغيلية تتطلب تدخلك اليوم: • 3 واجبات بانتظار التصحيح والتقييم • 2 طلاب متأخرين عن جدول الدراسة',
            'perfStats' => [
                ['value' => '12 طالب', 'label' => 'أكملوا كافة متطلبات الدورة', 'tone' => 'green'],
                ['value' => '5 طلاب', 'label' => 'متأخرين عن جدول التقدم', 'tone' => 'yellow'],
                ['value' => '3 واجبات', 'label' => 'بانتظار التصحيح', 'tone' => 'red'],
            ],
            'students' => [$student, $student, $student, $student],
        ]);
    }

    public static function assignments(): array
    {
        $current = [
            'title' => 'تصميم خريطة رحلة المستخدم (User Journey)',
            'course' => 'دبلومة UI/UX الشاملة',
            'deadline' => '10 أغسطس 2026',
            'submissions' => '45 / 50',
            'pending' => '12 طالب',
            'graded' => '33 طالب',
            'progress' => 90,
            'points' => 20,
            'badge' => 12,
        ];

        $row = [
            'title' => 'مخطط الواجهة UX',
            'course' => 'دبلومة UI/UX الشاملة',
            'grade' => 6,
            'passGrade' => 6,
            'submissions' => 50,
            'pending' => 2,
            'passed' => 2,
            'failed' => 2,
            'deadline' => '28 يوليو 2026',
            'status' => 'نشط',
        ];

        $studentRow = [
            'name' => 'علا محمد',
            'title' => 'مخطط الواجهة UX',
            'course' => 'دورة قياس النجاح دورة قياس النجاح',
            'first_at' => '—',
            'last_at' => '—',
            'attempts' => '—',
            'grade' => '—',
            'created_day' => '28',
            'created_month' => 'يوليو 2026',
            'status' => 'لم يتم التسليم',
        ];

        return array_merge(self::common(), [
            'assignmentStats' => [
                ['value' => '12 تكليف', 'label' => 'بانتظار التصحيح'],
                ['value' => '145 تكليف', 'label' => 'تم تصحيحها'],
                ['value' => '157 تكليف', 'label' => 'تسليم'],
            ],
            'currentAssignments' => [
                array_merge($current, ['cta' => 'عرض التسليمات']),
                array_merge($current, ['cta' => 'تصحيح الإجابات']),
            ],
            'resultsRows' => [$row, $row, $row, $row],
            'studentResultsRows' => [$studentRow, $studentRow, $studentRow, $studentRow],
        ]);
    }

    public static function courseAssignments(string $slug): array
    {
        $row = [
            'name' => 'علا محمد',
            'joined_at' => '28 يوليو 2026',
            'latest_at' => '12 أغسطس 2026',
            'last_at' => '—',
            'attempts' => '1 / 3',
            'grade' => '25 / 50',
            'status' => 'لن يتم التسليم',
        ];

        return array_merge(self::common(), [
            'slug' => $slug ?: 'demo',
            'pageTitleMain' => 'متطلبات دوراتي / السلام',
            'pageSubtitle' => 'الممارس المعتمد في جودة الرعاية الصحية CPHQ',
            'summaryCards' => [
                ['label' => 'إجمالي التسليمات', 'value' => '2', 'edge' => '#0f4c45', 'valueClass' => 'text-primary'],
                ['label' => 'التسليمات المجتازة', 'value' => '1', 'edge' => '#0FC787', 'valueClass' => 'text-[#0FC787]'],
                ['label' => 'قيد المراجعة', 'value' => '2', 'edge' => '#F59E0B', 'valueClass' => 'text-[#F59E0B]'],
                ['label' => 'معدل النجاح العام', 'value' => '2', 'edge' => '#6366F1', 'valueClass' => 'text-[#6366F1]'],
            ],
            'submissions' => [$row, $row, $row, $row],
        ]);
    }

    public static function assignmentReview(int $id): array
    {
        $chapters = [
            [
                'title' => 'الباب الأول',
                'completed' => true,
                'expanded' => true,
                'subtitle' => 'هنا عنوان المحاضرة',
                'items' => [
                    ['title' => 'فيديو تعريفي', 'type' => 'video', 'active' => true],
                    ['title' => 'نموذج تجريبي', 'type' => 'text', 'active' => false],
                    ['title' => 'شرح نظري + أمثلة', 'type' => 'text', 'active' => false],
                ],
            ],
        ];

        for ($i = 2; $i <= 9; $i++) {
            $chapters[] = [
                'title' => 'الباب ' . ['', '', 'الثاني', 'الثالث', 'الرابع', 'الخامس', 'السادس', 'السابع', 'الثامن', 'التاسع'][$i],
                'completed' => false,
                'expanded' => false,
                'subtitle' => 'هنا عنوان المحاضرة',
                'items' => [],
            ];
        }

        return array_merge(self::common(), [
            'assignmentId' => $id ?: 1,
            'slug' => 'demo',
            'reviewTitle' => 'تكليف المحاضرة العاشرة: تطبيق معايير إشراك المريض في تحسين الجودة',
            'detailsTitle' => 'تفاصيل التكليف والمحتوى المطلوب',
            'detailsBody' => 'اكتب مقالاً تحليلياً يناقش دور أخصائي الجودة في إشراك المريض داخل أقسام الطوارئ، مع التركيز على قياس أثر المشاركة على مؤشرات الأداء ورضا المستفيدين.',
            'pointsTitle' => 'نقاط يجب تغطيتها في المقال',
            'points' => [
                'مقدمة عن مفهوم إشراك المريض وأهميته في تحسين الجودة',
                'مؤشرات أداء (KPIs) يمكن ربطها بمشاركة المريض في الطوارئ',
                'خاتمة وتوصيات عملية قابلة للتطبيق داخل القسم',
            ],
            'studentAnswerParagraphs' => [
                'السيد المحاضر / المقيم، تحية طيبة وبعد،',
                'أتقدم لكم بإجابتي على تكليف إشراك المريض في تحسين الجودة داخل أقسام الطوارئ، وقد ركزت على ثلاثة محاور أساسية مرتبطة بمؤشرات الأداء:',
            ],
            'studentAnswerPoints' => [
                'زمن الانتظار قبل الكشف الأول وكيفية تقليله عبر مشاركة المريض في ترتيب الأولويات.',
                'رضا المريض عن التواصل مع الفريق الطبي أثناء الرحلة العلاجية في الطوارئ.',
                'سرعة الاستجابة للشكاوى والملاحظات الواردة من المرضى وذويهم.',
            ],
            'attachmentName' => 'خطة_تحسين_تجربة_المريض_الطوارئ.pdf',
            'attachmentSize' => '2.4 MB',
            'attachmentScan' => 'تم فحص الملف',
            'maxGrade' => 50,
            'passGrade' => 25,
            'course' => [
                'title' => 'قياس النجاح والجودة',
                'subtitle' => 'الادارة والتنفيذ',
                'progress' => 46,
                'progress_label' => 'نسبة الإنجاز',
            ],
            'chapters' => $chapters,
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
