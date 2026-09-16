<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Certificate;
use App\Models\CourseLearning;
use App\Models\File;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\QuizzesResult;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\TextLesson;
use App\Models\TimeSpentOnCourse;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\TextLessonTranslation;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\Models\WebinarAssignmentHistoryMessage;
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

            $seededQuestions = $this->ensureDemoQuizQuestions($quiz, $teacher->id, $locale, $now);
            $quiz->update([
                'total_mark' => collect($seededQuestions)->sum(fn ($q) => (int) $q->grade),
            ]);

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

                WebinarAssignmentHistoryMessage::updateOrCreate(
                    [
                        'assignment_history_id' => $history->id,
                        'sender_id' => $buyer->id,
                    ],
                    [
                        'message' => 'هذه إجابة تجريبية للتكليف المسلّم. قمت بإعداد المطلوب وفق معايير الجودة ورفعت المرفقات الداعمة.',
                        'file_title' => $bIndex === 0 ? 'تسليم-التكليف.pdf' : 'ملاحظات-التسليم.pdf',
                        'file_path' => $bIndex === 0
                            ? 'store/panel_v1/demo-handout.pdf'
                            : 'store/panel_v1/demo-theory.pdf',
                        'created_at' => $now - ((($index * 2) + $bIndex + 1) * 3600) + 60,
                    ]
                );
            }

            $file = $this->ensureDemoFile($webinar, $chapter, $teacher->id, $locale, $now);
            $extraFiles = $this->ensureExtraDemoLessons($webinar, $chapter, $teacher->id, $locale, $now);

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

                $this->seedBuyerLearningProgress(
                    $buyer,
                    $webinar,
                    $file,
                    $extraFiles,
                    $bIndex,
                    $now
                );

                $this->seedBuyerQuizResult(
                    $quiz,
                    $buyer,
                    $seededQuestions,
                    $bIndex,
                    $now
                );
            }
        }

        $this->refreshUpcomingSessions($teacher->id, $now);
        $commentsCount = $this->seedCourseComments($teacher, $activeWebinars->take(4)->values(), $buyers, $now);
        $certsCount = $this->seedDemoCertificates($teacher, $activeWebinars, $buyers, $now);

        $this->command?->info(sprintf(
            'Home demo ready for #%d (%s): quizzes=%d assignments=%d pending≈%d comments=%d certs=%d',
            $teacher->id,
            $teacher->email,
            $quizCount,
            $assignmentCount,
            $pendingCount,
            $commentsCount,
            $certsCount
        ));
    }

    /**
     * Seed issued course/quiz certificates so instructor certificates pages have real rows.
     */
    private function seedDemoCertificates($teacher, $webinars, array $buyers, int $now): int
    {
        if ($webinars->isEmpty() || empty($buyers)) {
            return 0;
        }

        $created = 0;
        $certCourses = $webinars->take(3)->values();

        foreach ($certCourses as $wIndex => $webinar) {
            if (!(bool) $webinar->certificate) {
                $webinar->update(['certificate' => true]);
            }

            foreach (array_slice($buyers, 0, 2) as $bIndex => $buyer) {
                $exists = Certificate::query()
                    ->where('webinar_id', $webinar->id)
                    ->where('student_id', $buyer->id)
                    ->where('type', 'course')
                    ->exists();

                if ($exists) {
                    continue;
                }

                Certificate::create([
                    'webinar_id' => $webinar->id,
                    'student_id' => $buyer->id,
                    'type' => 'course',
                    'created_at' => $now - (($wIndex + 1) * 7200) - (($bIndex + 1) * 600),
                ]);
                $created++;
            }
        }

        $quiz = Quiz::query()
            ->where('creator_id', $teacher->id)
            ->where('certificate', true)
            ->orderByDesc('id')
            ->first();

        if ($quiz && !empty($buyers[0])) {
            $buyer = $buyers[0];
            $result = QuizzesResult::query()
                ->where('quiz_id', $quiz->id)
                ->where('user_id', $buyer->id)
                ->orderByDesc('id')
                ->first();

            if ($result && !Certificate::query()->where('quiz_id', $quiz->id)->where('student_id', $buyer->id)->exists()) {
                Certificate::create([
                    'quiz_id' => $quiz->id,
                    'quiz_result_id' => $result->id,
                    'student_id' => $buyer->id,
                    'user_grade' => $result->user_grade ?? 80,
                    'type' => 'quiz',
                    'created_at' => $now - 900,
                ]);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed student course comments (+ one reply + one report) for instructor comments page.
     */
    private function seedCourseComments($teacher, $webinars, array $buyers, int $now): int
    {
        if ($webinars->isEmpty() || empty($buyers)) {
            return 0;
        }

        $bodies = [
            'شرح الوحدة الأولى واضح جداً، هل يمكن إضافة مثال عملي إضافي؟',
            'أحتاج توضيحاً حول معيار الامتثال في المحاضرة الثالثة.',
            'المحتوى ممتاز لكن سرعة العرض سريعة قليلاً في بعض الأجزاء.',
            'هل يمكن رفع ملف الملخص الخاص بالفصل الثاني؟',
            'استفدت كثيراً من التكليف، أنتظر ملاحظاتكم على تسليمي.',
            'هل موعد الجلسة المباشرة القادمة ثابت أم قابل للتغيير؟',
        ];

        $created = 0;
        $firstCommentId = null;

        foreach ($webinars as $wIndex => $webinar) {
            foreach (array_slice($buyers, 0, 3) as $bIndex => $buyer) {
                $body = $bodies[($wIndex * 3 + $bIndex) % count($bodies)];
                $status = ($wIndex === 0 && $bIndex === 2)
                    ? Comment::$pending
                    : Comment::$active;

                $comment = Comment::query()
                    ->where('webinar_id', $webinar->id)
                    ->where('user_id', $buyer->id)
                    ->whereNull('reply_id')
                    ->where('comment', $body)
                    ->first();

                if (empty($comment)) {
                    $comment = Comment::create([
                        'user_id' => $buyer->id,
                        'webinar_id' => $webinar->id,
                        'comment' => $body,
                        'reply_id' => null,
                        'status' => $status,
                        'created_at' => $now - (($wIndex + 1) * 3600) - (($bIndex + 1) * 900),
                        'viewed_at' => null,
                    ]);
                    $created++;
                } else {
                    $comment->update([
                        'status' => $status,
                        'created_at' => $now - (($wIndex + 1) * 3600) - (($bIndex + 1) * 900),
                    ]);
                }

                if ($firstCommentId === null) {
                    $firstCommentId = $comment->id;
                }

                // One instructor reply on the first webinar's first buyer comment
                if ($wIndex === 0 && $bIndex === 0) {
                    $replyBody = 'شكراً لملاحظتك، سأضيف مثالاً تطبيقياً في المحاضرة القادمة.';
                    $existsReply = Comment::query()
                        ->where('reply_id', $comment->id)
                        ->where('user_id', $teacher->id)
                        ->exists();

                    if (!$existsReply) {
                        Comment::create([
                            'user_id' => $teacher->id,
                            'webinar_id' => $webinar->id,
                            'comment' => $replyBody,
                            'reply_id' => $comment->id,
                            'status' => Comment::$active,
                            'created_at' => $now - 1800,
                            'viewed_at' => $now - 1800,
                        ]);
                        $created++;
                    }
                }
            }
        }

        if ($firstCommentId) {
            $existsReport = CommentReport::query()
                ->where('comment_id', $firstCommentId)
                ->where('user_id', $teacher->id)
                ->exists();

            if (!$existsReport) {
                $parent = Comment::find($firstCommentId);
                CommentReport::create([
                    'webinar_id' => $parent?->webinar_id,
                    'user_id' => $teacher->id,
                    'comment_id' => $firstCommentId,
                    'message' => 'تعليق تجريبي للتحقق من شاشة البلاغات.',
                    'created_at' => $now - 600,
                ]);
            }
        }

        return $created;
    }

    /**
     * @return QuizzesQuestion[]
     */
    private function ensureDemoQuizQuestions(Quiz $quiz, int $creatorId, string $locale, int $now): array
    {
        $defs = [
            [
                'type' => QuizzesQuestion::$multiple,
                'grade' => 25,
                'order' => 1,
                'title' => 'أيّ مما يلي يُعد من مؤشرات جودة الخدمة الصحية؟',
                'correct' => null,
                'options' => [
                    ['title' => 'زمن انتظار المريض', 'correct' => true],
                    ['title' => 'عدد مواقف السيارات فقط', 'correct' => false],
                    ['title' => 'لون الزي الرسمي', 'correct' => false],
                    ['title' => 'اسم المبنى', 'correct' => false],
                ],
            ],
            [
                'type' => QuizzesQuestion::$multiple,
                'grade' => 25,
                'order' => 2,
                'title' => 'الهدف الأساسي من دورة PDCA هو:',
                'correct' => null,
                'options' => [
                    ['title' => 'التحسين المستمر للعمليات', 'correct' => true],
                    ['title' => 'زيادة عدد الاجتماعات فقط', 'correct' => false],
                    ['title' => 'إلغاء التوثيق', 'correct' => false],
                    ['title' => 'تقليل التدريب', 'correct' => false],
                ],
            ],
            [
                'type' => QuizzesQuestion::$descriptive,
                'grade' => 50,
                'order' => 3,
                'title' => 'هل تتوافق معايير الجودة الصحية الحديثة مع تقليص تكاليف التشغيل؟ وضح ذلك.',
                'correct' => 'نعم؛ تطبيق المعايير يقلل الأخطاء وإعادة العمل فيعزز الكفاءة ويخفض التكاليف على المدى المتوسط.',
                'options' => [],
            ],
        ];

        $questions = [];
        foreach ($defs as $def) {
            $question = QuizzesQuestion::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'creator_id' => $creatorId,
                    'order' => $def['order'],
                    'type' => $def['type'],
                ],
                [
                    'grade' => $def['grade'],
                    'created_at' => $now,
                ]
            );

            QuizzesQuestionTranslation::updateOrCreate(
                [
                    'quizzes_question_id' => $question->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $def['title'],
                    'correct' => $def['correct'],
                ]
            );

            if ($def['type'] === QuizzesQuestion::$multiple) {
                $existing = QuizzesQuestionsAnswer::where('question_id', $question->id)->orderBy('id')->get();
                foreach ($def['options'] as $optIndex => $optionDef) {
                    $answer = $existing->get($optIndex);
                    if (empty($answer)) {
                        $answer = QuizzesQuestionsAnswer::create([
                            'creator_id' => $creatorId,
                            'question_id' => $question->id,
                            'correct' => $optionDef['correct'] ? 1 : 0,
                            'created_at' => $now,
                        ]);
                    } else {
                        $answer->update([
                            'correct' => $optionDef['correct'] ? 1 : 0,
                        ]);
                    }

                    QuizzesQuestionsAnswerTranslation::updateOrCreate(
                        [
                            'quizzes_questions_answer_id' => $answer->id,
                            'locale' => $locale,
                        ],
                        ['title' => $optionDef['title']]
                    );
                }
            }

            $questions[] = $question->fresh(['quizzesQuestionsAnswers']);
        }

        return $questions;
    }

    /**
     * @param QuizzesQuestion[] $questions
     */
    private function seedBuyerQuizResult(Quiz $quiz, User $buyer, array $questions, int $buyerIndex, int $now): void
    {
        $resultsPayload = [];
        $earned = 0;
        $hasDescriptive = false;

        foreach ($questions as $qIndex => $question) {
            $entry = ['grade' => (int) $question->grade, 'status' => false];

            if ($question->type === QuizzesQuestion::$descriptive) {
                $hasDescriptive = true;
                $entry['text'] = $buyerIndex === 0
                    ? 'نعم. تطبيق المعايير يقلل الأخطاء وإعادة العمل، مما يخفض التكاليف التشغيلية مع الزمن.'
                    : ($buyerIndex === 1
                        ? 'أعتقد أن المعايير تساعد جزئياً لكنها تحتاج استثماراً أولياً.'
                        : 'غير متأكد من العلاقة المباشرة بين الجودة والتكلفة.');
            } else {
                $answers = $question->quizzesQuestionsAnswers;
                $correct = $answers->firstWhere('correct', 1) ?: $answers->first();
                // Buyer 0 always correct MCQ; buyer 1 mixes; buyer 2 often wrong
                $pickCorrect = $buyerIndex === 0 || ($buyerIndex === 1 && $qIndex === 0);
                $chosen = $pickCorrect
                    ? $correct
                    : ($answers->firstWhere('correct', 0) ?: $answers->last());

                if ($chosen) {
                    $entry['answer'] = $chosen->id;
                    if ((int) $chosen->correct === 1) {
                        $entry['status'] = true;
                        $earned += (int) $question->grade;
                    }
                }
            }

            $resultsPayload[$question->id] = $entry;
        }

        // First buyer waits for descriptive grading; others remain waiting too for review cards
        $status = $hasDescriptive
            ? QuizzesResult::$waiting
            : (($earned >= (int) $quiz->pass_mark) ? QuizzesResult::$passed : 'failed');

        if ($buyerIndex >= 2 && $hasDescriptive) {
            // Keep one graded sample for pass-rate stats
            $status = QuizzesResult::$passed;
            $earned = min(100, $earned + 40);
        }

        QuizzesResult::updateOrCreate(
            [
                'quiz_id' => $quiz->id,
                'user_id' => $buyer->id,
            ],
            [
                'results' => json_encode($resultsPayload, JSON_UNESCAPED_UNICODE),
                'user_grade' => $earned,
                'status' => $status,
                'created_at' => $now - (($buyerIndex + 1) * 2400),
            ]
        );
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
                'order' => 1,
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

    /**
     * Extra lectures so instructor can track multi-item student progress (same player design).
     *
     * @return array{files: File[], text: ?TextLesson}
     */
    private function ensureExtraDemoLessons(Webinar $webinar, WebinarChapter $chapter, int $creatorId, string $locale, int $now): array
    {
        $defs = [
            [
                'file' => 'store/panel_v1/demo-lecture.mp4',
                'file_type' => 'video',
                'storage' => 'upload',
                'order' => 2,
                'title' => 'محاضرة — أساسيات التطبيق',
            ],
            [
                'file' => 'store/panel_v1/demo-handout.pdf',
                'file_type' => 'document',
                'storage' => 'upload',
                'order' => 3,
                'title' => 'ملف — ملخص الوحدة',
            ],
        ];

        $files = [];
        foreach ($defs as $def) {
            $file = File::updateOrCreate(
                [
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapter->id,
                    'file' => $def['file'],
                ],
                [
                    'creator_id' => $creatorId,
                    'volume' => '0',
                    'file_type' => $def['file_type'],
                    'accessibility' => 'paid',
                    'storage' => $def['storage'],
                    'downloadable' => true,
                    'check_previous_parts' => false,
                    'online_viewer' => false,
                    'status' => File::$Active,
                    'order' => $def['order'],
                    'created_at' => $now,
                ]
            );

            FileTranslation::updateOrCreate(
                ['file_id' => $file->id, 'locale' => $locale],
                [
                    'title' => $def['title'],
                    'description' => 'درس تجريبي لتتبع تقدم الطالب في لوحة المدرب.',
                ]
            );

            $files[] = $file;
        }

        $text = TextLesson::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'order' => 4,
            ],
            [
                'creator_id' => $creatorId,
                'image' => null,
                'study_time' => 10,
                'accessibility' => 'paid',
                'status' => TextLesson::$Active,
                'created_at' => $now,
            ]
        );

        TextLessonTranslation::updateOrCreate(
            ['text_lesson_id' => $text->id, 'locale' => $locale],
            [
                'title' => 'درس نصي — مراجعة سريعة',
                'summary' => 'ملخص نصي تجريبي',
                'content' => 'هذا درس نصي تجريبي لمتابعة تقدم الطالب في نفس تصميم مشغل الدورة.',
            ]
        );

        return [
            'files' => $files,
            'text' => $text,
        ];
    }

    private function seedBuyerLearningProgress(
        User $buyer,
        Webinar $webinar,
        ?File $introFile,
        array $extraLessons,
        int $buyerIndex,
        int $now
    ): void {
        $allFiles = collect($extraLessons['files'] ?? []);
        if (!empty($introFile)) {
            $allFiles = $allFiles->prepend($introFile);
        }

        // Buyer 0 ~ high progress, buyer 1 ~ mid, buyer 2 ~ low
        $fileTake = match ($buyerIndex) {
            0 => $allFiles->count(),
            1 => max(1, (int) ceil($allFiles->count() * 0.66)),
            default => max(1, (int) ceil($allFiles->count() * 0.33)),
        };

        foreach ($allFiles->take($fileTake) as $i => $file) {
            CourseLearning::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'file_id' => $file->id,
                ],
                [
                    'session_id' => null,
                    'text_lesson_id' => null,
                    'created_at' => $now - (($buyerIndex + 1) * 1800) - ($i * 120),
                ]
            );
        }

        $text = $extraLessons['text'] ?? null;
        if ($text && $buyerIndex <= 1) {
            CourseLearning::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'text_lesson_id' => $text->id,
                ],
                [
                    'file_id' => null,
                    'session_id' => null,
                    'created_at' => $now - (($buyerIndex + 1) * 1500),
                ]
            );
        }

        $sessions = Session::where('webinar_id', $webinar->id)->orderBy('id')->get();
        $sessionTake = $buyerIndex === 0 ? min(2, $sessions->count()) : ($buyerIndex === 1 ? min(1, $sessions->count()) : 0);
        foreach ($sessions->take($sessionTake) as $i => $session) {
            CourseLearning::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'session_id' => $session->id,
                ],
                [
                    'file_id' => null,
                    'text_lesson_id' => null,
                    'created_at' => $now - (($buyerIndex + 1) * 1600) - ($i * 90),
                ]
            );
        }

        TimeSpentOnCourse::updateOrCreate(
            [
                'user_id' => $buyer->id,
                'course_id' => $webinar->id,
                'page' => 'learning_page',
            ],
            [
                'entry_time' => $now - (7200 - ($buyerIndex * 600)),
                'exit_time' => $now - 100,
                'seconds_spent' => 5400 - ($buyerIndex * 1200),
            ]
        );
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
