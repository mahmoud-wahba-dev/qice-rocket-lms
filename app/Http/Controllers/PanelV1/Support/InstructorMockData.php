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

    public static function quizzes(): array
    {
        $rowOpen = [
            'title' => 'مخطط الواجهة UX',
            'course' => 'دبلومة UI/UX الشاملة',
            'questions' => 6,
            'duration' => 'مفتوح',
            'fullGrade' => 50,
            'passGrade' => 2,
            'students' => 2,
            'status' => 'نشط',
            'created_at' => '28 يوليو 2026',
        ];

        $rowTimed = array_merge($rowOpen, ['duration' => 6]);

        $studentRow = [
            'name' => 'علا محمد',
            'title' => 'مخطط الواجهة UX',
            'course' => 'دبلومة UI/UX الشاملة',
            'grade' => '42 / 50',
            'attempts' => '1 / 3',
            'attempted_at' => '28 يوليو 2026',
            'status' => 'ناجح',
        ];

        return array_merge(self::common(), [
            'quizStats' => [
                ['value' => '12 اختبار', 'label' => 'إجمالي الاختبارات'],
                ['value' => '145 اختبار', 'label' => 'متوسط نسبة النجاح'],
                ['value' => '157 اختبار', 'label' => 'إجابات بانتظار التصحيح'],
            ],
            'pendingQuizzes' => [
                [
                    'name' => 'علا محمد',
                    'status' => 'بانتظار التصحيح',
                    'title' => 'اختبار الدرس الثاني ( الترابط في الأنظمة )',
                    'course' => 'دورة قياس النجاح',
                    'date' => '28 يوليو 2026',
                ],
            ],
            'quizReviewSubtitle' => 'اختبار كورس التست • دورة CPHQ',
            'quizReviewQuestions' => [
                [
                    'question' => 'هل تتوافق معايير الجودة الصحية الحديثة مع تقليص تكاليف التشغيل؟ وضح ذلك.',
                    'model' => 'الإجابة هي نعم. حيث يسهم تطبيق المعايير في تقليل الأخطاء وإعادة العمل، مما يعزز الكفاءة.',
                    'submitted' => 'الإجابة هي نعم. حيث يسهم تطبيق المعايير في تقليل الأخطاء وإعادة العمل، مما يعزز الكفاءة.',
                    'score' => 10,
                    'max' => 10,
                ],
                [
                    'question' => 'اذكر مؤشرَين من مؤشرات الأداء المرتبطة برضا المريض في أقسام الطوارئ.',
                    'model' => 'زمن الانتظار قبل الكشف، ونسبة الشكاوى المستجابة خلال 48 ساعة.',
                    'submitted' => 'زمن الانتظار، ومعدل رضا المرضى بعد الزيارة.',
                    'score' => 7,
                    'max' => 10,
                ],
            ],
            'quizRows' => [$rowOpen, $rowTimed, $rowOpen, $rowTimed],
            'quizStudentRows' => [$studentRow, $studentRow, $studentRow, $studentRow],
        ]);
    }

    public static function quizView(int $id): array
    {
        $review = self::assignmentReview(1);

        return array_merge(self::common(), [
            'quizId' => $id ?: 1,
            'slug' => 'demo',
            'course' => $review['course'],
            'chapters' => $review['chapters'],
            'quizView' => [
                'title' => 'اختبار كورس التست',
                'subtitle' => 'المعايير المعتمدة في جودة الرعاية الصحية (CPHQ)',
                'questions_count' => 12,
                'current' => 10,
                'total' => 12,
                'question' => 'هذا سؤال للعرض',
                'options' => [
                    ['id' => 1, 'text' => 'تجربة 1', 'selected' => false, 'correct' => false],
                    ['id' => 2, 'text' => 'تجربة 2', 'selected' => false, 'correct' => false],
                    ['id' => 3, 'text' => 'تجربة 3', 'selected' => false, 'correct' => false],
                    ['id' => 4, 'text' => 'تجربة 4', 'selected' => true, 'correct' => true],
                ],
            ],
        ]);
    }

    public static function support(): array
    {
        $ticket = [
            'id' => '#TK-8821',
            'subject' => 'استفسار عن مواعيد تحويل الأرباح الشهرية',
            'date' => '28 يوليو 2026',
            'status' => 'تم الرد',
        ];

        return array_merge(self::common(), [
            'supportTickets' => [$ticket, $ticket, $ticket, $ticket],
            'courseSupportRows' => [],
        ]);
    }

    public static function marketing(): array
    {
        $row = [
            'name' => 'علا محمد',
            'email' => 'ollamoh@gmail.com',
            'course' => 'دورة قياس النجاح',
            'course_id' => '2354',
            'original_price' => '150 ر.س',
            'discount' => '—',
            'total' => '150 ر.س',
            'net' => '120 ر.س',
            'type' => 'دورة',
            'date' => '29 يوليو 2026',
            'time' => '09:43 ص',
        ];

        $subtitle = 'فتح طلب جديد وتوجيهه للفريق المختص';

        return array_merge(self::common(), [
            'marketingActions' => [
                ['title' => 'إنشاء قسيمة خصم جديدة', 'subtitle' => $subtitle, 'href' => '#'],
                ['title' => 'إنشاء تخفيض لدورتك', 'subtitle' => $subtitle, 'href' => '#'],
                ['title' => 'إنشاء خطط ترويجية', 'subtitle' => $subtitle, 'href' => '#'],
            ],
            'couponRows' => [$row, $row, $row, $row],
            'discountRows' => [$row, $row],
            'promoRows' => [],
        ]);
    }

    public static function payouts(): array
    {
        $row = [
            'id' => '#PAY-11029',
            'datetime' => '15 يوليو 2026 - 10:15',
            'type_line1' => 'شراء دورة: CPHQ',
            'type_line2' => 'الممارس المعتمد',
            'amount' => '+ 1,199.00 ر.س',
            'status' => 'مكتملة',
        ];

        return array_merge(self::common(), [
            'payoutSummary' => [
                'available' => '0.00',
                'next_payout' => '15 أغسطس 2026',
                'min_withdraw' => '500 ر.س',
                'total_income' => '1,199.00',
                'held' => '0.00',
            ],
            'payoutRows' => [$row, $row, $row, $row],
        ]);
    }

    public static function finance(): array
    {
        $courseRow = [
            'name' => 'علا محمد',
            'email' => 'olamaah@gmail.com',
            'service' => 'دورة قياس النجاح',
            'service_id' => '2354',
            'original_price' => '150 ر.س',
            'discount' => '—',
            'total' => '150 ر.س',
            'net' => '120 ر.س',
            'type' => 'course',
            'type_label' => 'دورة',
            'date' => '29 يوليو 2026',
            'time' => '09:43 ص',
        ];

        $sessionRow = array_merge($courseRow, [
            'type' => 'session',
            'type_label' => 'جلسة',
        ]);

        return array_merge(self::common(), [
            'salesRows' => [$courseRow, $sessionRow, $courseRow, $sessionRow],
        ]);
    }

    public static function certificates(): array
    {
        return array_merge(self::common(), [
            'certificateStats' => [
                ['value' => '2', 'label' => 'إجمالي الشهادات الصادرة'],
                ['value' => '2', 'label' => 'شهادات الإتمام'],
                ['value' => '2', 'label' => 'شهادات الاختبارات'],
                ['value' => '2', 'label' => 'القوالب المنشأة'],
            ],
            'recentCertificates' => [
                ['title' => '', 'student' => '', 'preview' => null],
                ['title' => '', 'student' => '', 'preview' => null],
                ['title' => '', 'student' => '', 'preview' => null],
                ['title' => '', 'student' => '', 'preview' => null],
            ],
            'completionRows' => [],
            'examRows' => [],
        ]);
    }

    public static function consultations(): array
    {
        return array_merge(self::common(), [
            'session' => [
                'title' => 'جلسة مراجعة متطلبات CPHQ',
                'instructor' => 'سارة السلمان',
                'instructorInitials' => 'سس',
                'price' => '200 ر.س',
                'status' => 'مجدولة',
                'date' => 'غدًا، 16 أغسطس 2026',
                'time' => '08:00 م - 08:45 م (45 دقيقة)',
                'linkLabel' => 'لقاء أونلاين',
            ],
            'attendees' => [
                [
                    'initials' => 'HI',
                    'name' => 'hiba',
                    'email' => 'hibagammer222@gmail.com',
                    'joinType' => 'وجهاً لوجه',
                    'day' => 'سبت',
                    'date' => '15 أغسطس 2026',
                    'time' => '03:31 - 02:46',
                    'amount' => '0',
                    'students' => '1',
                    'status' => 'تم الانتهاء من',
                ],
            ],
        ]);
    }

    public static function settings(): array
    {
        return array_merge(self::common(), [
            'profileName' => 'Dr. Saudy Mohamed Hasanin',
            'profileEmail' => 'drsaudyh@gmail.com',
            'profilePhone' => '501234567',
            'profileCountryCode' => '+966',
            'profileBio' => 'Healthcare Quality leader with extensive experience in CPHQ training and hospital accreditation programs.',
            'profileJobTitle' => 'مدرب جودة صحية',
            'identityVerified' => false,
            'paymentAccount' => 'ماي فاتورة',
            'accountOptions' => [
                'اشترك في النشرة الإخبارية عبر البريد الإلكتروني',
                'السماح للمستخدمين بمراسلتك من صفحة ملفك الشخصي',
                'تفعيل إحصائيات الملف الشخصي',
                'التجديد التلقائي للاشتراك',
            ],
            'socialNetworks' => [
                ['key' => 'instagram', 'label' => 'Instagram', 'icon' => 'instagram'],
                ['key' => 'whatsapp', 'label' => 'Whatsapp', 'icon' => 'whatsapp'],
                ['key' => 'messenger', 'label' => 'Messenger', 'icon' => 'messenger'],
                ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'facebook'],
            ],
            'skillOptions' => [
                'إدارة الجودة',
                'سلامة المرضى',
                'اعتماد المستشفيات',
                'CPHQ',
                'التحسين المستمر',
            ],
            'loginHistory' => [
                [
                    'os' => 'Windows 10.0',
                    'browser' => 'Chrome',
                    'device' => 'desktop',
                    'ip' => '2a02:9b0:401e:a:0:0:0:1',
                    'country' => 'Saudi Arabia',
                    'city' => 'Riyadh',
                    'session_start' => '10 فبراير 2026 11:42',
                    'session_end' => '10 فبراير 2026 11:43',
                    'duration' => '27 ثانية',
                    'active' => false,
                ],
                [
                    'os' => 'Windows 10.0',
                    'browser' => 'Chrome',
                    'device' => 'desktop',
                    'ip' => '2a02:9b0:401e:a:0:0:0:1',
                    'country' => 'Saudi Arabia',
                    'city' => 'Riyadh',
                    'session_start' => '9 فبراير 2026 18:10',
                    'session_end' => null,
                    'duration' => 'نشط',
                    'active' => true,
                ],
            ],
        ]);
    }
}
