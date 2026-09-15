<?php

namespace Database\Seeders;

use App\Models\Accounting;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Comment;
use App\Models\CourseForum;
use App\Models\CourseForumAnswer;
use App\Models\CoursePersonalNote;
use App\Models\Favorite;
use App\Models\File;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\QuizzesResult;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\SessionTranslation;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\Models\WebinarAssignmentHistoryMessage;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\User;
use Illuminate\Database\Seeder;

class PanelV1StudentDemoSeeder extends Seeder
{
    public const COURSE_SLUG = 'panel-v1-student-demo-course';
    public const COURSE_SLUG_2 = 'panel-v1-student-demo-course-2';
    public const STUDENT_EMAIL = 'student@demo.com';
    public const STUDENT_PASSWORD = '123456';

    /** @var string[] */
    public static array $extraEmails = [];

    public static bool $enrollEmptyStudents = true;

    public function run(): void
    {
        $now = time();
        $locale = 'ar';

        $teacher = $this->ensureTeacher($now);
        $students = $this->ensureStudents($now);

        $webinar = $this->ensureWebinar($teacher, $now, $locale);
        $chapter = $this->ensureChapter($webinar, $teacher, $now, $locale);
        $file = $this->ensureFile($webinar, $chapter, $teacher, $now, $locale);
        $session = $this->ensureLiveSession($webinar, $chapter, $teacher, $now, $locale);
        [$pendingAssignment, $submittedAssignment] = $this->ensureAssignments($webinar, $chapter, $teacher, $now, $locale);
        $quiz = $this->ensureQuiz($webinar, $chapter, $teacher, $now, $locale);
        // Curriculum rebuilds all files (YouTube / upload / external / PDF) — run after ensureFile
        $this->ensureWatchCurriculum($webinar, $teacher, $now, $locale);
        $this->ensureThirdAssignment($webinar, $chapter, $teacher, $now, $locale);

        $webinar2 = $this->ensureSecondWebinar($teacher, $now, $locale);

        foreach ($students as $student) {
            $this->enrollStudent($student, $teacher, $webinar, $now);
            $this->enrollStudent($student, $teacher, $webinar2, $now - 20);
            $this->seedStudentOverlays(
                $student,
                $teacher,
                $webinar,
                $pendingAssignment,
                $submittedAssignment,
                $quiz,
                $now
            );
            $this->seedCourseProgress($student, $webinar, $now);
        }

        if ($this->command) {
            $this->command->info('Panel V1 student demo ready.');
            $emails = collect($students)->pluck('email')->implode(', ');
            $this->command->table(
                ['Field', 'Value'],
                [
                    ['Students enrolled', $emails],
                    ['Password (demo user)', self::STUDENT_PASSWORD],
                    ['Course slug', self::COURSE_SLUG],
                    ['Watch URL', '/v1/student/courses/' . self::COURSE_SLUG . '/watch'],
                    ['File id', $file->id],
                    ['Session id', $session->id],
                ]
            );
        }
    }

    private function ensureTeacher(int $now): User
    {
        $teacher = User::where('email', 'teacher@gmail.com')->first();

        if ($teacher) {
            return $teacher;
        }

        return User::updateOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'full_name' => 'مدرب تجريبي',
                'mobile' => '0500000003',
                'role_name' => Role::$teacher,
                'role_id' => Role::getTeacherRoleId(),
                'password' => User::generatePassword(self::STUDENT_PASSWORD),
                'status' => User::$active,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * @return User[]
     */
    private function ensureStudents(int $now): array
    {
        $byId = [];

        $demo = User::updateOrCreate(
            ['email' => self::STUDENT_EMAIL],
            [
                'full_name' => 'متدرب تجريبي',
                'mobile' => '0500000099',
                'role_name' => Role::$user,
                'role_id' => Role::getUserRoleId(),
                'password' => User::generatePassword(self::STUDENT_PASSWORD),
                'status' => User::$active,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        $byId[$demo->id] = $demo;

        $legacy = User::where('email', 'user@gmail.com')->first();
        if (!$legacy) {
            $legacy = User::updateOrCreate(
                ['email' => 'user@gmail.com'],
                [
                    'full_name' => 'user',
                    'mobile' => '09379332831',
                    'role_name' => Role::$user,
                    'role_id' => Role::getUserRoleId(),
                    'password' => User::generatePassword(self::STUDENT_PASSWORD),
                    'status' => User::$active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
        $byId[$legacy->id] = $legacy;

        foreach (self::$extraEmails as $email) {
            $email = trim((string) $email);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $user = User::where('email', $email)->where('role_name', Role::$user)->first();
            if ($user) {
                $byId[$user->id] = $user;
            }
        }

        if (self::$enrollEmptyStudents) {
            $candidates = User::query()
                ->where('role_name', Role::$user)
                ->where('status', User::$active)
                ->orderByDesc('id')
                ->limit(40)
                ->get();

            foreach ($candidates as $candidate) {
                $hasSale = Sale::where('buyer_id', $candidate->id)->whereNotNull('webinar_id')->exists();
                if (!$hasSale) {
                    $byId[$candidate->id] = $candidate;
                }
            }
        }

        // Always include common local testers if present
        foreach (['mahmodwahba360@gmail.com', 'abubakeraltahhan87@gmail.com'] as $email) {
            $user = User::where('email', $email)->where('role_name', Role::$user)->first();
            if ($user) {
                $byId[$user->id] = $user;
            }
        }

        return array_values($byId);
    }

    private function ensureSecondWebinar(User $teacher, int $now, string $locale): Webinar
    {
        $category = Category::where('enable', true)->orderBy('id')->first();

        $webinar = Webinar::updateOrCreate(
            ['slug' => self::COURSE_SLUG_2],
            [
                'type' => Webinar::$course,
                'teacher_id' => $teacher->id,
                'creator_id' => $teacher->id,
                'category_id' => $category?->id,
                'thumbnail' => '/assets/landing_v1/img/home/course.webp',
                'image_cover' => '/assets/landing_v1/img/home/course.webp',
                'price' => 299,
                'duration' => 90,
                'support' => true,
                'certificate' => true,
                'downloadable' => true,
                'forum' => true,
                'private' => false,
                'status' => Webinar::$active,
                'created_at' => $now - 50,
                'updated_at' => $now - 50,
            ]
        );

        WebinarTranslation::updateOrCreate(
            ['webinar_id' => $webinar->id, 'locale' => $locale],
            [
                'title' => 'ورشة تجريبية ثانية للمتدرب',
                'summary' => 'دورة إضافية لملء تبويب دوراتي',
                'description' => '<p>دورة ثانية للتجربة في لوحة المتدرب.</p>',
                'seo_description' => 'دورة تجريبية panel_v1 ثانية',
            ]
        );

        return $webinar->fresh();
    }

    private function ensureWebinar(User $teacher, int $now, string $locale): Webinar
    {
        $category = Category::where('enable', true)->orderBy('id')->first();

        $webinar = Webinar::updateOrCreate(
            ['slug' => self::COURSE_SLUG],
            [
                'type' => Webinar::$course,
                'teacher_id' => $teacher->id,
                'creator_id' => $teacher->id,
                'category_id' => $category?->id,
                'thumbnail' => '/assets/landing_v1/img/home/course.webp',
                'image_cover' => '/assets/landing_v1/img/home/course.webp',
                'price' => 499,
                'duration' => 180,
                'support' => true,
                'certificate' => true,
                'downloadable' => true,
                'forum' => true,
                'private' => false,
                'status' => Webinar::$active,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        WebinarTranslation::updateOrCreate(
            ['webinar_id' => $webinar->id, 'locale' => $locale],
            [
                'title' => 'قياس النجاح والجودة',
                'summary' => 'الادارة والتنفيذ — دورة تجريبية كاملة للمتدرب',
                'description' => '<p>محتوى تجريبي يغطي المشاهدة والتكليفات والاختبارات والمنتدى.</p>',
                'seo_description' => 'دورة تجريبية panel_v1',
            ]
        );

        WebinarTranslation::updateOrCreate(
            ['webinar_id' => $webinar->id, 'locale' => 'en'],
            [
                'title' => 'قياس النجاح والجودة',
                'summary' => 'Management & Implementation — student demo course',
                'description' => '<p>Demo course for panel_v1 student watch page.</p>',
                'seo_description' => 'panel_v1 demo course',
            ]
        );

        return $webinar->fresh();
    }

    private function ensureChapter(Webinar $webinar, User $teacher, int $now, string $locale): WebinarChapter
    {
        $chapter = WebinarChapter::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'order' => 1,
            ],
            [
                'user_id' => $teacher->id,
                'status' => WebinarChapter::$chapterActive,
                'check_all_contents_pass' => false,
                'created_at' => $now,
            ]
        );

        WebinarChapterTranslation::updateOrCreate(
            ['webinar_chapter_id' => $chapter->id, 'locale' => $locale],
            ['title' => 'المحاضرة الأولى']
        );

        return $chapter;
    }

    private function ensureFile(Webinar $webinar, WebinarChapter $chapter, User $teacher, int $now, string $locale): File
    {
        $file = File::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'file' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'creator_id' => $teacher->id,
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
                'title' => 'فيديو تعريفي',
                'description' => 'مقدمة الدورة التجريبية',
            ]
        );

        WebinarChapterItem::makeItem($teacher->id, $chapter->id, $file->id, WebinarChapterItem::$chapterFile);

        $pdf = File::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'file' => '/store/panel_v1/demo-handout.pdf',
            ],
            [
                'creator_id' => $teacher->id,
                'volume' => '1.4MB',
                'file_type' => 'pdf',
                'accessibility' => 'paid',
                'storage' => 'upload',
                'downloadable' => true,
                'check_previous_parts' => false,
                'online_viewer' => true,
                'status' => File::$Active,
                'created_at' => $now,
            ]
        );

        FileTranslation::updateOrCreate(
            ['file_id' => $pdf->id, 'locale' => $locale],
            [
                'title' => 'ملف مرفق PDF',
                'description' => 'مستند تجريبي للتحميل',
            ]
        );

        WebinarChapterItem::makeItem($teacher->id, $chapter->id, $pdf->id, WebinarChapterItem::$chapterFile);

        return $file;
    }

    private function ensureLiveSession(Webinar $webinar, WebinarChapter $chapter, User $teacher, int $now, string $locale): Session
    {
        $session = Session::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'creator_id' => $teacher->id,
            ],
            [
                'date' => $now - (15 * 60),
                'duration' => 90,
                'link' => null,
                'session_api' => 'local',
                'check_previous_parts' => false,
                'status' => Session::$Active,
                'created_at' => $now,
            ]
        );

        // Keep one demo lecture currently live (started ~15 minutes ago).
        $session->date = $now - (15 * 60);
        $session->duration = 90;
        $session->status = Session::$Active;
        $session->save();

        SessionTranslation::updateOrCreate(
            ['session_id' => $session->id, 'locale' => $locale],
            [
                'title' => 'مناقشة أساليب ربط واجهات',
                'description' => 'جلسة مباشرة لاختبار تبويب المحاضرات',
            ]
        );
        SessionTranslation::updateOrCreate(
            ['session_id' => $session->id, 'locale' => 'en'],
            [
                'title' => 'مناقشة أساليب ربط واجهات',
                'description' => 'Live session for student panel testing',
            ]
        );

        WebinarChapterItem::makeItem($teacher->id, $chapter->id, $session->id, WebinarChapterItem::$chapterSession);

        // Completed + upcoming lectures for the tab list
        foreach ([
            [
                'offset' => -(86400 * 10),
                'duration' => 80,
                'title' => 'مقدمة في بناء الأنظمة المدمجة واستخدام الأيقونات',
            ],
            [
                'offset' => 86400 * 5,
                'duration' => 45,
                'title' => 'ورشة تطبيقية مباشرة',
            ],
        ] as $extra) {
            $extraSession = Session::query()
                ->where('webinar_id', $webinar->id)
                ->where('chapter_id', $chapter->id)
                ->whereHas('translations', function ($query) use ($locale, $extra) {
                    $query->where('locale', $locale)->where('title', $extra['title']);
                })
                ->first();

            if (!$extraSession) {
                $extraSession = Session::create([
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapter->id,
                    'creator_id' => $teacher->id,
                    'date' => $now + $extra['offset'],
                    'duration' => $extra['duration'],
                    'link' => null,
                    'session_api' => 'local',
                    'check_previous_parts' => false,
                    'status' => Session::$Active,
                    'created_at' => $now,
                ]);
            } else {
                $extraSession->update([
                    'date' => $now + $extra['offset'],
                    'duration' => $extra['duration'],
                    'status' => Session::$Active,
                    'creator_id' => $teacher->id,
                ]);
            }

            SessionTranslation::updateOrCreate(
                ['session_id' => $extraSession->id, 'locale' => $locale],
                [
                    'title' => $extra['title'],
                    'description' => 'محاضرة مباشرة من قاعدة البيانات',
                ]
            );
            SessionTranslation::updateOrCreate(
                ['session_id' => $extraSession->id, 'locale' => 'en'],
                [
                    'title' => $extra['title'],
                    'description' => 'Live lecture from database',
                ]
            );

            WebinarChapterItem::makeItem($teacher->id, $chapter->id, $extraSession->id, WebinarChapterItem::$chapterSession);
        }

        return $session;
    }

    /**
     * @return array{0: WebinarAssignment, 1: WebinarAssignment}
     */
    private function ensureAssignments(Webinar $webinar, WebinarChapter $chapter, User $teacher, int $now, string $locale): array
    {
        $pending = $this->findOrCreateAssignmentByTitle(
            $webinar,
            $chapter,
            $teacher,
            $locale,
            'تكليف التطبيق العملي (السلام)',
            'لخّص معايير الجودة المذكورة في المحاضرة وأرفق ملف PDF.',
            25,
            15,
            14,
            0,
            $now
        );

        $submitted = $this->findOrCreateAssignmentByTitle(
            $webinar,
            $chapter,
            $teacher,
            $locale,
            'تكليف مُسلّم مسبقاً',
            'تكليف تجريبي بحالة مسلّم وبانتظار التقييم.',
            50,
            25,
            7,
            2,
            $now - 100
        );

        return [$pending, $submitted];
    }

    private function findOrCreateAssignmentByTitle(
        Webinar $webinar,
        WebinarChapter $chapter,
        User $teacher,
        string $locale,
        string $title,
        string $description,
        int $grade,
        int $passGrade,
        int $deadline,
        int $attempts,
        int $createdAt
    ): WebinarAssignment {
        $existingId = WebinarAssignmentTranslation::query()
            ->where('webinar_assignment_translations.locale', $locale)
            ->where('webinar_assignment_translations.title', $title)
            ->join('webinar_assignments', 'webinar_assignments.id', '=', 'webinar_assignment_translations.webinar_assignment_id')
            ->where('webinar_assignments.webinar_id', $webinar->id)
            ->value('webinar_assignment_translations.webinar_assignment_id');

        if ($existingId) {
            $assignment = WebinarAssignment::find($existingId);
            $assignment->update([
                'grade' => $grade,
                'pass_grade' => $passGrade,
                'deadline' => $deadline,
                'attempts' => $attempts,
                'status' => 'active',
            ]);
        } else {
            $assignment = WebinarAssignment::create([
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'creator_id' => $teacher->id,
                'grade' => $grade,
                'pass_grade' => $passGrade,
                'deadline' => $deadline,
                'attempts' => $attempts,
                'check_previous_parts' => false,
                'status' => 'active',
                'created_at' => $createdAt,
            ]);
        }

        WebinarAssignmentTranslation::updateOrCreate(
            ['webinar_assignment_id' => $assignment->id, 'locale' => $locale],
            [
                'title' => $title,
                'description' => $description,
            ]
        );
        WebinarAssignmentTranslation::updateOrCreate(
            ['webinar_assignment_id' => $assignment->id, 'locale' => 'en'],
            [
                'title' => $title,
                'description' => $description,
            ]
        );

        WebinarChapterItem::makeItem($teacher->id, $chapter->id, $assignment->id, WebinarChapterItem::$chapterAssignment);

        return $assignment;
    }

    private function ensureQuiz(Webinar $webinar, WebinarChapter $chapter, User $teacher, int $now, string $locale): Quiz
    {
        $quiz = Quiz::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'chapter_id' => $chapter->id,
                'creator_id' => $teacher->id,
            ],
            [
                'attempt' => 3,
                'pass_mark' => 50,
                'time' => 15,
                'certificate' => true,
                'status' => Quiz::ACTIVE,
                'created_at' => $now,
            ]
        );

        QuizTranslation::updateOrCreate(
            ['quiz_id' => $quiz->id, 'locale' => $locale],
            [
                'title' => 'اختبار الدورة التجريبية',
                'description' => 'اختبار قصير لتجربة لوحة المتدرب',
            ]
        );

        WebinarChapterItem::makeItem($teacher->id, $chapter->id, $quiz->id, WebinarChapterItem::$chapterQuiz);

        $question = QuizzesQuestion::firstOrCreate(
            [
                'quiz_id' => $quiz->id,
                'creator_id' => $teacher->id,
                'order' => 1,
            ],
            [
                'grade' => 50,
                'type' => 'multiple',
                'created_at' => $now,
            ]
        );

        QuizzesQuestionTranslation::updateOrCreate(
            ['quizzes_question_id' => $question->id, 'locale' => $locale],
            ['title' => 'ما الهدف من معايير الجودة؟']
        );

        $correct = QuizzesQuestionsAnswer::firstOrCreate(
            [
                'question_id' => $question->id,
                'creator_id' => $teacher->id,
                'correct' => 1,
            ],
            ['created_at' => $now]
        );

        QuizzesQuestionsAnswerTranslation::updateOrCreate(
            ['quizzes_questions_answer_id' => $correct->id, 'locale' => $locale],
            ['title' => 'تحسين مستوى الخدمة وتقليل الأخطاء']
        );

        $wrong = QuizzesQuestionsAnswer::firstOrCreate(
            [
                'question_id' => $question->id,
                'creator_id' => $teacher->id,
                'correct' => 0,
            ],
            ['created_at' => $now]
        );

        QuizzesQuestionsAnswerTranslation::updateOrCreate(
            ['quizzes_questions_answer_id' => $wrong->id, 'locale' => $locale],
            ['title' => 'زيادة التكلفة فقط']
        );

        return $quiz;
    }

    private function enrollStudent(User $student, User $teacher, Webinar $webinar, int $now): void
    {
        Sale::updateOrCreate(
            [
                'buyer_id' => $student->id,
                'webinar_id' => $webinar->id,
                'type' => Sale::$webinar,
            ],
            [
                'seller_id' => $teacher->id,
                'payment_method' => Sale::$credit,
                'amount' => 499,
                'total_amount' => 499,
                'manual_added' => true,
                'created_at' => $now,
            ]
        );
    }

    private function seedStudentOverlays(
        User $student,
        User $teacher,
        Webinar $webinar,
        WebinarAssignment $pendingAssignment,
        WebinarAssignment $submittedAssignment,
        Quiz $quiz,
        int $now
    ): void {
        // Keep pending assignment without history for this student
        WebinarAssignmentHistory::where('assignment_id', $pendingAssignment->id)
            ->where('student_id', $student->id)
            ->delete();

        $history = WebinarAssignmentHistory::updateOrCreate(
            [
                'assignment_id' => $submittedAssignment->id,
                'student_id' => $student->id,
            ],
            [
                'instructor_id' => $teacher->id,
                'status' => 'pending',
                'grade' => null,
                'created_at' => $now - 50,
            ]
        );

        WebinarAssignmentHistoryMessage::firstOrCreate(
            [
                'assignment_history_id' => $history->id,
                'sender_id' => $student->id,
            ],
            [
                'message' => 'هذه إجابة تجريبية للتكليف المسلّم.',
                'created_at' => $now - 50,
            ]
        );

        QuizzesResult::updateOrCreate(
            [
                'quiz_id' => $quiz->id,
                'user_id' => $student->id,
            ],
            [
                'results' => json_encode([]),
                'user_grade' => 80,
                'status' => 'passed',
                'created_at' => $now - 40,
            ]
        );

        Certificate::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'student_id' => $student->id,
                'type' => 'course',
            ],
            ['created_at' => $now - 30]
        );

        Comment::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'user_id' => $student->id,
            ],
            [
                'comment' => 'تعليق تجريبي على الدورة من لوحة المتدرب.',
                'status' => 'active',
                'created_at' => $now - 20,
            ]
        );

        Favorite::updateOrCreate(
            [
                'user_id' => $student->id,
                'webinar_id' => $webinar->id,
            ],
            ['created_at' => $now]
        );

        CoursePersonalNote::updateOrCreate(
            [
                'user_id' => $student->id,
                'course_id' => $webinar->id,
                'targetable_id' => $webinar->id,
                'targetable_type' => 'webinar',
            ],
            [
                'note' => 'ملاحظة تجريبية: راجع معايير الجودة قبل الاختبار.',
                'created_at' => $now,
            ]
        );

        $notification = Notification::updateOrCreate(
            [
                'user_id' => $student->id,
                'type' => 'single',
                'title' => 'مرحباً بك في لوحة المتدرب',
            ],
            [
                'message' => 'تم تجهيز دورة تجريبية لاختبار الصفحات والنماذج.',
                'sender' => 'system',
                'created_at' => $now,
            ]
        );

        // Leave unread (no NotificationStatus row) — delete any existing seen marks for this demo notice
        \App\Models\NotificationStatus::where('user_id', $student->id)
            ->where('notification_id', $notification->id)
            ->delete();

        $department = SupportDepartment::query()->orderBy('id')->first();

        $platformTicket = Support::updateOrCreate(
            [
                'user_id' => $student->id,
                'title' => 'تذكرة دعم تجريبية — المنصة',
                'webinar_id' => null,
            ],
            [
                'department_id' => $department?->id,
                'status' => 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        SupportConversation::firstOrCreate(
            [
                'support_id' => $platformTicket->id,
                'sender_id' => $student->id,
            ],
            [
                'message' => 'هذه رسالة دعم تجريبية للمنصة.',
                'attach' => null,
                'created_at' => $now,
            ]
        );

        $courseTicket = Support::updateOrCreate(
            [
                'user_id' => $student->id,
                'title' => 'تذكرة دعم تجريبية — الدورة',
                'webinar_id' => $webinar->id,
            ],
            [
                'department_id' => null,
                'status' => 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        SupportConversation::firstOrCreate(
            [
                'support_id' => $courseTicket->id,
                'sender_id' => $student->id,
            ],
            [
                'message' => 'استفسار تجريبي عن محتوى الدورة.',
                'attach' => null,
                'created_at' => $now,
            ]
        );

        Accounting::updateOrCreate(
            [
                'user_id' => $student->id,
                'webinar_id' => $webinar->id,
                'type' => Accounting::$deduction,
                'type_account' => Accounting::$asset,
                'description' => 'شراء دورة تجريبية panel_v1',
            ],
            [
                'amount' => 499,
                'store_type' => 'manual',
                'created_at' => $now,
            ]
        );

        Accounting::updateOrCreate(
            [
                'user_id' => $student->id,
                'webinar_id' => null,
                'type' => Accounting::$addiction,
                'type_account' => Accounting::$asset,
                'description' => 'رصيد تجريبي panel_v1',
            ],
            [
                'amount' => 1000,
                'store_type' => 'manual',
                'created_at' => $now - 10,
            ]
        );

        $forum = CourseForum::updateOrCreate(
            [
                'webinar_id' => $webinar->id,
                'user_id' => $student->id,
                'title' => 'سؤال تجريبي في المنتدى',
            ],
            [
                'description' => 'كيف أبدأ بالمحاضرة الأولى؟',
                'attach' => null,
                'pin' => false,
                'created_at' => $now,
            ]
        );

        CourseForumAnswer::firstOrCreate(
            [
                'forum_id' => $forum->id,
                'user_id' => $student->id,
            ],
            [
                'description' => 'كيف أبدأ بالمحاضرة الأولى؟',
                'pin' => false,
                'resolved' => false,
                'created_at' => $now,
            ]
        );
    }

    /**
     * Rich curriculum: YouTube / local upload / external mp4 / PDF attachments across 8 lectures.
     */
    private function ensureWatchCurriculum(Webinar $webinar, User $teacher, int $now, string $locale): void
    {
        // Clean previous demo files so storage types / paths stay accurate for testing
        $oldFileIds = File::where('webinar_id', $webinar->id)->pluck('id');
        if ($oldFileIds->isNotEmpty()) {
            \App\Models\CourseLearning::whereIn('file_id', $oldFileIds)->delete();
            \App\Models\WebinarChapterItem::where('type', \App\Models\WebinarChapterItem::$chapterFile)
                ->whereIn('item_id', $oldFileIds)
                ->delete();
            FileTranslation::whereIn('file_id', $oldFileIds)->delete();
            File::whereIn('id', $oldFileIds)->delete();
        }

        $pdfTheory = '/store/panel_v1/demo-theory.pdf';
        $pdfHandout = '/store/panel_v1/demo-handout.pdf';
        $mp4Local = '/store/panel_v1/demo-lecture.mp4';
        $mp4External = 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4';

        $lectures = [
            1 => [
                'title' => 'المحاضرة الأولى',
                'subtitle' => 'هنا عنوان المحاضرة',
                'items' => [
                    ['kind' => 'youtube', 'title' => 'فيديو تعريفي', 'src' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ'],
                    ['kind' => 'pdf', 'title' => 'شرح نظري + أمثلة تطبيقية', 'src' => $pdfTheory],
                ],
            ],
            2 => [
                'title' => 'المحاضرة الثانية',
                'subtitle' => 'مؤشرات الأداء والجودة',
                'items' => [
                    ['kind' => 'upload', 'title' => 'فيديو مرفوع على السيرفر', 'src' => $mp4Local],
                    ['kind' => 'pdf', 'title' => 'ملف مرفق — مؤشرات الجودة', 'src' => $pdfHandout],
                ],
            ],
            3 => [
                'title' => 'المحاضرة الثالثة',
                'subtitle' => 'أدوات القياس',
                'items' => [
                    ['kind' => 'youtube', 'title' => 'فيديو أدوات القياس (يوتيوب)', 'src' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4'],
                    ['kind' => 'pdf', 'title' => 'دليل أدوات القياس PDF', 'src' => $pdfHandout],
                ],
            ],
            4 => [
                'title' => 'المحاضرة الرابعة',
                'subtitle' => 'دراسات حالة',
                'items' => [
                    ['kind' => 'external', 'title' => 'فيديو خارجي (رابط مباشر)', 'src' => $mp4External],
                    ['kind' => 'pdf', 'title' => 'ورقة عمل دراسة حالة', 'src' => $pdfTheory],
                ],
            ],
            5 => [
                'title' => 'المحاضرة الخامسة',
                'subtitle' => 'خطط التحسين',
                'items' => [
                    ['kind' => 'upload', 'title' => 'فيديو خطط التحسين (رفع محلي)', 'src' => $mp4Local],
                ],
            ],
            6 => [
                'title' => 'المحاضرة السادسة',
                'subtitle' => 'الخلاصة والتقييم',
                'items' => [
                    ['kind' => 'youtube', 'title' => 'فيديو ختامي', 'src' => 'https://www.youtube.com/watch?v=LXb3EKWsInQ'],
                    ['kind' => 'pdf', 'title' => 'ملخص الدورة PDF', 'src' => $pdfTheory],
                ],
            ],
            7 => [
                'title' => 'المحاضرة السابعة',
                'subtitle' => 'ورشة تطبيقية',
                'items' => [
                    ['kind' => 'upload', 'title' => 'تسجيل الورشة التطبيقية', 'src' => $mp4Local],
                    ['kind' => 'pdf', 'title' => 'تمارين الورشة', 'src' => $pdfHandout],
                ],
            ],
            8 => [
                'title' => 'المحاضرة الثامنة',
                'subtitle' => 'مراجعة نهائية',
                'items' => [
                    ['kind' => 'external', 'title' => 'مراجعة بالفيديو (خارجي)', 'src' => $mp4External],
                    ['kind' => 'pdf', 'title' => 'أسئلة المراجعة PDF', 'src' => $pdfHandout],
                ],
            ],
        ];

        foreach ($lectures as $order => $lecture) {
            $chapter = WebinarChapter::updateOrCreate(
                [
                    'webinar_id' => $webinar->id,
                    'order' => $order,
                ],
                [
                    'user_id' => $teacher->id,
                    'status' => WebinarChapter::$chapterActive,
                    'check_all_contents_pass' => false,
                    'created_at' => $now,
                ]
            );

            foreach ([$locale, 'en'] as $loc) {
                WebinarChapterTranslation::updateOrCreate(
                    ['webinar_chapter_id' => $chapter->id, 'locale' => $loc],
                    ['title' => $lecture['title']]
                );
            }

            foreach ($lecture['items'] as $itemIndex => $item) {
                $itemOrder = $itemIndex + 1;
                $meta = $this->fileMetaFromKind($item['kind'], $item['src']);

                $file = File::updateOrCreate(
                    [
                        'webinar_id' => $webinar->id,
                        'chapter_id' => $chapter->id,
                        'order' => $itemOrder,
                        'file_type' => $meta['file_type'],
                    ],
                    [
                        'creator_id' => $teacher->id,
                        'file' => $meta['file'],
                        'volume' => $meta['volume'],
                        'accessibility' => 'paid',
                        'storage' => $meta['storage'],
                        'downloadable' => $meta['downloadable'],
                        'check_previous_parts' => false,
                        'online_viewer' => $meta['online_viewer'],
                        'status' => File::$Active,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                foreach ([$locale, 'en'] as $loc) {
                    FileTranslation::updateOrCreate(
                        ['file_id' => $file->id, 'locale' => $loc],
                        [
                            'title' => $item['title'],
                            'description' => $lecture['subtitle'],
                        ]
                    );
                }

                WebinarChapterItem::makeItem($teacher->id, $chapter->id, $file->id, WebinarChapterItem::$chapterFile);
            }
        }

        $webinar->type = Webinar::$course;
        $webinar->duration = 180;
        $webinar->updated_at = $now;
        $webinar->save();

        // Per-lecture quizzes: only some chapters have one (others show empty quiz tab)
        $this->ensureChapterQuizzes($webinar, $teacher, $now, $locale);
    }

    private function ensureChapterQuizzes(Webinar $webinar, User $teacher, int $now, string $locale): void
    {
        $chapters = WebinarChapter::where('webinar_id', $webinar->id)->orderBy('order')->orderBy('id')->get();
        $specs = [
            1 => ['title' => 'اختبار المحاضرة الأولى', 'question' => 'ما الهدف من معايير الجودة؟', 'correct' => 'تحسين مستوى الخدمة وتقليل الأخطاء', 'wrong' => 'زيادة التكلفة فقط'],
            3 => ['title' => 'اختبار مؤشرات الأداء', 'question' => 'ماذا يعني KPI؟', 'correct' => 'مؤشر أداء رئيسي', 'wrong' => 'خطة تسويق'],
            5 => ['title' => 'اختبار التحسين المستمر', 'question' => 'أي منهج يركز على التحسين المستمر؟', 'correct' => 'PDCA / كايزن', 'wrong' => 'التجميد التام للعمليات'],
            8 => ['title' => 'اختبار المراجعة النهائية', 'question' => 'متى تُعتبر الدورة مكتملة للشهادة؟', 'correct' => 'عند إتمام المحتوى والنجاح في المتطلبات', 'wrong' => 'عند فتح الصفحة فقط'],
        ];

        foreach ($chapters as $chapter) {
            $order = (int) $chapter->order;
            if (!isset($specs[$order])) {
                continue;
            }
            $spec = $specs[$order];

            $quiz = Quiz::updateOrCreate(
                [
                    'webinar_id' => $webinar->id,
                    'chapter_id' => $chapter->id,
                    'creator_id' => $teacher->id,
                ],
                [
                    'attempt' => 3,
                    'pass_mark' => 50,
                    'time' => 10 + $order,
                    'certificate' => $order === 8,
                    'status' => Quiz::ACTIVE,
                    'created_at' => $now,
                ]
            );

            foreach ([$locale, 'en'] as $loc) {
                QuizTranslation::updateOrCreate(
                    ['quiz_id' => $quiz->id, 'locale' => $loc],
                    [
                        'title' => $spec['title'],
                        'description' => 'اختبار خاص بهذه المحاضرة',
                    ]
                );
            }

            WebinarChapterItem::makeItem($teacher->id, $chapter->id, $quiz->id, WebinarChapterItem::$chapterQuiz);

            $question = QuizzesQuestion::firstOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'creator_id' => $teacher->id,
                    'order' => 1,
                ],
                [
                    'grade' => 50,
                    'type' => 'multiple',
                    'created_at' => $now,
                ]
            );

            QuizzesQuestionTranslation::updateOrCreate(
                ['quizzes_question_id' => $question->id, 'locale' => $locale],
                ['title' => $spec['question']]
            );

            $correct = QuizzesQuestionsAnswer::firstOrCreate(
                [
                    'question_id' => $question->id,
                    'creator_id' => $teacher->id,
                    'correct' => 1,
                ],
                ['created_at' => $now]
            );
            QuizzesQuestionsAnswerTranslation::updateOrCreate(
                ['quizzes_questions_answer_id' => $correct->id, 'locale' => $locale],
                ['title' => $spec['correct']]
            );

            $wrong = QuizzesQuestionsAnswer::firstOrCreate(
                [
                    'question_id' => $question->id,
                    'creator_id' => $teacher->id,
                    'correct' => 0,
                ],
                ['created_at' => $now]
            );
            QuizzesQuestionsAnswerTranslation::updateOrCreate(
                ['quizzes_questions_answer_id' => $wrong->id, 'locale' => $locale],
                ['title' => $spec['wrong']]
            );
        }
    }

    /**
     * @return array{storage:string,file_type:string,file:string,volume:string,downloadable:bool,online_viewer:bool}
     */
    private function fileMetaFromKind(string $kind, string $src): array
    {
        return match ($kind) {
            'youtube' => [
                'storage' => 'youtube',
                'file_type' => 'video',
                'file' => $src,
                'volume' => '0',
                'downloadable' => false,
                'online_viewer' => false,
            ],
            'upload' => [
                'storage' => 'upload',
                'file_type' => 'video',
                'file' => $src,
                'volume' => '9.1MB',
                'downloadable' => false,
                'online_viewer' => false,
            ],
            'external' => [
                'storage' => 'external_link',
                'file_type' => 'video',
                'file' => $src,
                'volume' => '0',
                'downloadable' => false,
                'online_viewer' => false,
            ],
            default => [
                'storage' => 'upload',
                'file_type' => 'pdf',
                'file' => $src,
                'volume' => '1.4MB',
                'downloadable' => true,
                'online_viewer' => true,
            ],
        };
    }

    private function seedCourseProgress(User $student, Webinar $webinar, int $now): void
    {
        $files = File::where('webinar_id', $webinar->id)
            ->where('status', File::$Active)
            ->orderBy('chapter_id')
            ->orderBy('order')
            ->get();

        \App\Models\CourseLearning::where('user_id', $student->id)
            ->where(function ($q) use ($files, $webinar) {
                $q->whereIn('file_id', $files->pluck('id'))
                    ->orWhereIn('session_id', Session::where('webinar_id', $webinar->id)->pluck('id'));
            })
            ->delete();

        // Target ~46% overall progress (files + sessions + assignments + quizzes)
        $markCount = max(1, (int) ceil($files->count() * 0.55));
        foreach ($files->take($markCount) as $file) {
            \App\Models\CourseLearning::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'file_id' => $file->id,
                ],
                [
                    'text_lesson_id' => null,
                    'session_id' => null,
                    'created_at' => $now - 100,
                ]
            );
        }

        $sessions = Session::where('webinar_id', $webinar->id)->orderBy('id')->get();
        foreach ($sessions->take(2) as $session) {
            \App\Models\CourseLearning::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'session_id' => $session->id,
                ],
                [
                    'file_id' => null,
                    'text_lesson_id' => null,
                    'created_at' => $now - 90,
                ]
            );
        }

        \App\Models\TimeSpentOnCourse::updateOrCreate(
            [
                'user_id' => $student->id,
                'course_id' => $webinar->id,
                'page' => 'learning_page',
            ],
            [
                'entry_time' => $now - 7200,
                'exit_time' => $now - 100,
                'seconds_spent' => 5400,
            ]
        );
    }

    private function ensureThirdAssignment(
        Webinar $webinar,
        WebinarChapter $chapter,
        User $teacher,
        int $now,
        string $locale
    ): void {
        $this->findOrCreateAssignmentByTitle(
            $webinar,
            $chapter,
            $teacher,
            $locale,
            'تكليف تحليل مؤشرات الجودة',
            'حلّل مؤشرات الجودة الثلاثة المذكورة في المحاضرة الثانية.',
            40,
            20,
            21,
            0,
            $now - 50
        );
    }
}
