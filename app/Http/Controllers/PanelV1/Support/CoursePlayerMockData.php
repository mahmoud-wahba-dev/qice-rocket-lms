<?php

namespace App\Http\Controllers\PanelV1\Support;

class CoursePlayerMockData
{
    public static function forSlug(string $slug): array
    {
        return [
            'slug' => $slug ?: 'demo',
            'course' => [
                'title' => 'قياس النجاح والجودة',
                'subtitle' => 'الادارة والتنفيذ',
                'progress' => 46,
                'progress_label' => 'نسبة الإنجاز',
            ],
            'chapters' => [
                [
                    'id' => 1,
                    'title' => 'المحاضرة الأولى',
                    'subtitle' => 'هنا عنوان المحاضرة',
                    'completed' => true,
                    'expanded' => true,
                    'items' => [
                        ['id' => 1, 'type' => 'video', 'title' => 'فيديو تعريفي', 'active' => true],
                        ['id' => 2, 'type' => 'text', 'title' => 'شرح نظري + أمثلة تطبيقية', 'active' => false],
                    ],
                ],
                [
                    'id' => 2,
                    'title' => 'المحاضرة الثانية',
                    'subtitle' => 'هنا عنوان المحاضرة',
                    'completed' => false,
                    'expanded' => false,
                    'items' => [
                        ['id' => 3, 'type' => 'video', 'title' => 'فيديو تعريفي', 'active' => false],
                        ['id' => 4, 'type' => 'text', 'title' => 'شرح نظري + أمثلة تطبيقية', 'active' => false],
                    ],
                ],
                ['id' => 3, 'title' => 'المحاضرة الثالثة', 'subtitle' => 'هنا عنوان المحاضرة', 'completed' => false, 'expanded' => false, 'items' => []],
                ['id' => 4, 'title' => 'المحاضرة الرابعة', 'subtitle' => 'هنا عنوان المحاضرة', 'completed' => false, 'expanded' => false, 'items' => []],
                ['id' => 5, 'title' => 'المحاضرة الخامسة', 'subtitle' => 'هنا عنوان المحاضرة', 'completed' => false, 'expanded' => false, 'items' => []],
                ['id' => 6, 'title' => 'المحاضرة السادسة', 'subtitle' => 'هنا عنوان المحاضرة', 'completed' => false, 'expanded' => false, 'items' => []],
            ],
            'lesson' => [
                'title' => 'محتوى اليوم - اسم المحاضرة هنا',
            ],
            'hasLectureQuiz' => true,
            'hasLectureAssignment' => true,
            'hasComments' => false,
            'hasFiles' => true,
            'lectureQuiz' => [
                'title' => 'اختبار كورس التست',
                'subtitle' => 'المعايير المعتمدة في جودة الرعاية الصحية CPHQ',
                'duration' => '5 دقائق',
                'questions_count' => '2 سؤال',
                'pass_score' => '20 / 50',
                'attempts' => '3',
            ],
            'lectureAssignment' => [
                'title' => 'تكليف التطبيق العملي (السلام)',
                'subtitle' => 'الممارس المعتمد في جودة الرعاية الصحية CPHQ',
                'deadline' => 'غير محدود',
                'attempts' => 'غير محدد',
                'grade' => '50 درجة',
                'pass_grade' => '25 درجة',
                'description' => 'قم بتلخيص معايير الجودة المذكورة في المحاضرة وإرفاق الملف بصيغة PDF.',
                'file_name' => 'CPHQ_Template.pdf',
                'file_size' => '1.2MB',
            ],
            'files' => [
                ['name' => 'قم بتحميل ملف الاسم', 'ext' => 'PDF'],
            ],
            'forum' => [
                'banner_title' => 'منتدى قياس النجاح والجودة',
                'posts' => [
                    [
                        'author' => 'أحمد خالد',
                        'initial' => 'أ',
                        'time' => 'منذ 3 ساعات',
                        'body' => 'ما هي أفضل الممارسات لتطبيق مؤشرات الجودة في المستشفيات؟ وهل يمكن مشاركة نماذج جاهزة تساعد الفرق على البدء بسرعة؟',
                        'likes' => 2,
                        'comments' => 2,
                    ],
                    [
                        'author' => 'سارة علي',
                        'initial' => 'س',
                        'time' => 'منذ 8 ساعات',
                        'body' => 'هل توجد مراجع إضافية حول قياس النجاح في الإدارات التنفيذية؟ أحتاج مصادر موثوقة للمراجعة قبل الاختبار.',
                        'likes' => 5,
                        'comments' => 1,
                    ],
                ],
            ],
            'assignmentPage' => [
                'title' => 'تقديم إجابة التكليف',
                'subtitle' => 'اكتب نص الإجابة أو أرفق الملفات المطلوبة لتسليم التكليف للمحاضر.',
                'details_title' => 'تفاصيل التكليف والمحتوى المطلوب:',
                'details_body' => 'اكتب مقالاً تحليلياً (500 كلمة) يناقش أهمية تطبيق معايير الجودة في أقسام العناية الحرجة.',
                'points_title' => 'نقاط يجب تغطيتها في المقال:',
                'points' => [
                    'مقدمة عن مفهوم سلامة المرضى',
                    'شرح أدوات قياس الأداء (KPIs)',
                    'خاتمة تتضمن توصيات للتطبيق العملي',
                ],
                'form_title' => 'إجابة التكليف والتسليم',
                'word_limit' => 500,
            ],
            'quiz' => [
                'title' => 'اختبار كورس التست',
                'description' => 'اختبر معرفتك لتقييم مدى استعدادك لاجتياز هذه الدورة التدريبية.',
                'duration' => '5 دقائق',
                'questions_count' => '2 سؤال',
                'pass_score' => '20 / 50',
                'attempts' => 'غير محدود',
                'deadline' => 'ينتهي هذا الاختبار في: 19 أغسطس 2026 الساعة 23:59',
            ],
            'quizTake' => [
                'quiz_title' => 'اختبار كورس التست',
                'current' => 1,
                'total' => 3,
                'question_title' => 'السؤال الأول',
                'instruction' => 'اختر الإجابة الصحيحة',
                'options' => [
                    ['id' => 1, 'text' => 'التركيز على تحسين العمليات وليس لوم الأفراد', 'selected' => true],
                    ['id' => 2, 'text' => 'زيادة العقوبات المالية على الأخطاء الطبية', 'selected' => false],
                    ['id' => 3, 'text' => 'تقليل عدد مراجعات الأداء السنوية للأطباء', 'selected' => false],
                    ['id' => 4, 'text' => 'التركيز المباشر على خفض التكاليف التشغيلية فقط', 'selected' => false],
                ],
            ],
            'certificateLocked' => true,
        ];
    }
}
