<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PanelV1\Support\InstructorMockData;
use App\Models\Quiz;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    use \App\Http\Controllers\PanelV1\Support\ProfileSettingsTrait;
    public function home(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return $this->render($request, 'panel_v1.instructor.pages.home', 'لوحة المدرب', $this->homeData($user));
    }

    public function courses(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return $this->render($request, 'panel_v1.instructor.pages.courses', 'إدارة الدورات', [
            'courseCards' => $this->courseCards($user),
        ]);
    }

    public function createCourse(Request $request, ?int $step = 1)
    {
        $user = $request->user();
        $step = max(1, min(5, $step ?? 1));

        $draft = null;
        if ($request->filled('draft')) {
            $draft = \App\Models\Webinar::where('id', $request->input('draft'))
                ->where('teacher_id', optional($user)->id)
                ->where('status', 'is_draft')
                ->first();
        }

        $categories = \App\Models\Category::whereNull('parent_id')
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                return ['id' => $category->id, 'title' => $category->title];
            })->all();

        $teacherQuizzes = \App\Models\Quiz::where('creator_id', optional($user)->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($quiz) {
                return ['id' => $quiz->id, 'title' => $quiz->title, 'webinar_id' => $quiz->webinar_id];
            })->all();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.create-course',
            'إنشاء دورة جديدة',
            array_merge(InstructorMockData::createCourse(), [
                'wizardStep' => $step,
                'draftId' => $draft->id ?? null,
                'draftTitle' => $draft->title ?? 'دورة تدريبية بدون عنوان',
                'draft' => $draft ? [
                    'title' => $draft->title,
                    'category_id' => $draft->category_id,
                    'locale' => 'ar',
                    'seo_description' => $draft->seo_description,
                    'description' => $draft->description,
                    'video_demo_link' => $draft->video_demo_source === 'external_link' ? $draft->video_demo : null,
                    'tags' => $draft->tags->pluck('title')->implode(','),
                ] : [],
                'categories' => !empty($categories) ? $categories : (InstructorMockData::createCourse()['categories'] ?? []),
                'languages' => [
                    ['key' => 'ar', 'label' => 'العربية'],
                    ['key' => 'en', 'label' => 'English'],
                ],
                'curriculumUnits' => $this->curriculumUnits($draft),
                'teacherQuizzes' => $teacherQuizzes,
                'draftPrice' => $draft->price ?? null,
                'draftCapacity' => $draft->capacity ?? null,
                'draftCertificate' => (bool) ($draft->certificate ?? false),
            ])
        );
    }

    public function storeCourse(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $step = max(1, min(5, (int) $request->input('wizard_step', 1)));

        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = \App\Models\Webinar::where('id', $request->input('draft_id'))
                ->where('teacher_id', $user->id)
                ->where('status', 'is_draft')
                ->firstOrFail();
        }

        if ($step === 1) {
            $request->validate([                'title' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'course_type' => 'nullable|in:recorded,live,text',
                'seo_description' => 'nullable|string|max:160',
                'description' => 'nullable|string',
                'video_demo_link' => 'nullable|url|max:2000',
                'image_thumbnail' => 'nullable|image|max:5120',
                'image_cover' => 'nullable|image|max:5120',
                'tags' => 'nullable|string|max:1000',
            ]);

            $typeMap = ['recorded' => 'course', 'live' => 'webinar', 'text' => 'text_lesson'];

            if (empty($draft)) {
                $draft = new \App\Models\Webinar();
                $draft->teacher_id = $user->id;
                $draft->creator_id = $user->id;
                $draft->status = 'is_draft';
                $draft->slug = \Illuminate\Support\Str::slug($request->input('title')) . '-' . time();
                $draft->created_at = time();
            }

            $draft->type = $typeMap[$request->input('course_type', 'recorded')] ?? 'course';
            $draft->category_id = $request->input('category_id');
            $draft->updated_at = time();

            if ($request->hasFile('image_thumbnail')) {
                $draft->thumbnail = '/storage/' . $request->file('image_thumbnail')->store('webinars', 'public');
            }

            if ($request->hasFile('image_cover')) {
                $draft->image_cover = '/storage/' . $request->file('image_cover')->store('webinars', 'public');
            }

            if ($request->filled('video_demo_link')) {
                $draft->video_demo = $request->input('video_demo_link');
                $draft->video_demo_source = 'external_link';
            }

            $draft->save();

            $locale = $request->input('locale', 'ar');
            $translation = $draft->translateOrNew($locale);
            $translation->webinar_id = $draft->id;
            $translation->locale = $locale;
            $translation->title = $request->input('title');
            $translation->seo_description = $request->input('seo_description');
            $translation->description = $request->input('description');
            $translation->save();

            $tags = array_filter(array_map('trim', explode(',', (string) $request->input('tags', ''))));
            if (!empty($tags)) {
                \App\Models\Tag::where('webinar_id', $draft->id)->delete();
                foreach (array_slice(array_unique($tags), 0, 10) as $tagTitle) {
                    \App\Models\Tag::create(['title' => mb_substr($tagTitle, 0, 64), 'webinar_id' => $draft->id]);
                }
            }
        }

        if (!empty($draft) && $step === 3) {
            $request->validate([
                'quiz_id' => 'nullable|exists:quizzes,id',
                'certificate' => 'nullable|boolean',
            ]);

            if ($request->filled('quiz_id')) {
                $quiz = \App\Models\Quiz::where('id', $request->input('quiz_id'))
                    ->where('creator_id', $user->id)
                    ->firstOrFail();
                $quiz->webinar_id = $draft->id;
                $quiz->save();
            }

            $draft->certificate = $request->boolean('certificate');
            $draft->save();
        }

        if (!empty($draft) && $step === 4) {
            $request->validate([
                'price' => 'nullable|integer|min:0',
                'capacity' => 'nullable|integer|min:1',
            ]);

            if ($request->filled('price') && (int) $request->input('price') > 0) {
                $draft->price = (int) $request->input('price');
            } else {
                $draft->price = null;
            }

            $draft->capacity = $request->input('capacity') ?: null;
            $draft->save();
        }

        if (!empty($draft) && $step === 5 && $request->input('go_next') === 'done') {
            $draft->status = 'pending';
            $draft->save();

            return redirect()
                ->route('panel.v1.instructor.courses')
                ->with('toast', [
                    'title' => 'تم',
                    'msg' => 'تم إرسال الدورة للمراجعة',
                    'type' => 'success',
                ]);
        }

        $params = ['step' => $step];
        if (!empty($draft)) {
            $params['draft'] = $draft->id;
        }

        if ($request->filled('go_next') && $request->input('go_next') !== 'done') {
            $params['step'] = max(1, min(5, (int) $request->input('go_next')));
        }

        return redirect()
            ->route('panel.v1.instructor.courses.create', $params)
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم حفظ المسودة بنجاح',
                'type' => 'success',
            ]);
    }

    private function draftOrFail($user, $draftId)
    {
        return \App\Models\Webinar::where('id', $draftId)
            ->where('teacher_id', $user->id)
            ->where('status', 'is_draft')
            ->firstOrFail();
    }

    public function chapterStore(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'draft_id' => 'required|integer',
            'title' => 'required|string|max:255',
        ]);

        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        $chapter = new \App\Models\WebinarChapter();
        $chapter->user_id = $user->id;
        $chapter->webinar_id = $draft->id;
        $chapter->status = 'active';
        $chapter->created_at = time();
        $chapter->save();

        $translation = $chapter->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        return $this->backToDraftStep($request, $draft, 2, 'تمت إضافة الوحدة');
    }

    public function chapterDelete(Request $request, int $chapterId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer']);
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\WebinarChapter::where('id', $chapterId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->backToDraftStep($request, $draft, 2, 'تم حذف الوحدة');
    }

    public function curriculumSessionStore(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'draft_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'topic' => 'required|string|max:255',
            'date' => 'required|date',
            'duration' => 'required|integer|min:1',
        ]);

        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        $chapter = \App\Models\WebinarChapter::where('id', $request->input('chapter_id'))
            ->where('webinar_id', $draft->id)
            ->firstOrFail();

        $session = new \App\Models\Session();
        $session->creator_id = $user->id;
        $session->webinar_id = $draft->id;
        $session->chapter_id = $chapter->id;
        $session->date = strtotime($request->input('date'));
        $session->duration = (int) $request->input('duration');
        $session->status = 'active';
        $session->created_at = time();
        $session->updated_at = time();
        $session->save();

        $translation = $session->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('topic');
        $translation->save();

        return $this->backToDraftStep($request, $draft, 2, 'تمت إضافة الجلسة');
    }

    public function curriculumSessionDelete(Request $request, int $sessionId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer']);
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\Session::where('id', $sessionId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->backToDraftStep($request, $draft, 2, 'تم حذف الجلسة');
    }

    public function curriculumFileStore(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'draft_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'upload' => 'required|file|max:102400',
        ]);

        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        $chapter = \App\Models\WebinarChapter::where('id', $request->input('chapter_id'))
            ->where('webinar_id', $draft->id)
            ->firstOrFail();

        $path = $request->file('upload')->store('webinars/files', 'public');

        $file = new \App\Models\File();
        $file->creator_id = $user->id;
        $file->webinar_id = $draft->id;
        $file->chapter_id = $chapter->id;
        $file->accessibility = 'paid';
        $file->downloadable = 1;
        $file->storage = 'upload';
        $file->file = '/storage/' . $path;
        $file->volume = (string) $request->file('upload')->getSize();
        $file->file_type = explode('/', $request->file('upload')->getMimeType())[0] ?? 'file';
        $file->status = 'active';
        $file->created_at = time();
        $file->updated_at = time();
        $file->save();

        $translation = $file->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        return $this->backToDraftStep($request, $draft, 2, 'تم رفع الملف');
    }

    public function curriculumFileDelete(Request $request, int $fileId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer']);
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\File::where('id', $fileId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->backToDraftStep($request, $draft, 2, 'تم حذف الملف');
    }

    public function curriculumTextStore(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'draft_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
        ]);

        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        $chapter = \App\Models\WebinarChapter::where('id', $request->input('chapter_id'))
            ->where('webinar_id', $draft->id)
            ->firstOrFail();

        $text = new \App\Models\TextLesson();
        $text->creator_id = $user->id;
        $text->webinar_id = $draft->id;
        $text->chapter_id = $chapter->id;
        $text->status = 'active';
        $text->created_at = time();
        $text->updated_at = time();
        $text->save();

        $translation = $text->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->summary = $request->input('summary');
        $translation->save();

        return $this->backToDraftStep($request, $draft, 2, 'تمت إضافة الدرس النصي');
    }

    public function curriculumTextDelete(Request $request, int $textId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer']);
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\TextLesson::where('id', $textId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->backToDraftStep($request, $draft, 2, 'تم حذف الدرس');
    }

    private function backToDraftStep(Request $request, $draft, int $step, string $message)
    {
        return redirect()
            ->route('panel.v1.instructor.courses.create', ['step' => $step, 'draft' => $draft->id])
            ->with('toast', ['title' => 'تم', 'msg' => $message, 'type' => 'success']);
    }

    private function curriculumUnits($draft): array
    {
        if (empty($draft)) {
            return [];
        }

        return \App\Models\WebinarChapter::with(['sessions', 'files', 'textLessons'])
            ->where('webinar_id', $draft->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($chapter) {
                $lessons = [];

                foreach ($chapter->sessions as $session) {
                    $lessons[] = [
                        'kind' => 'session',
                        'id' => $session->id,
                        'title' => $session->title,
                        'duration' => ($session->duration ?? 0) . ' دقيقة',
                    ];
                }

                foreach ($chapter->files as $file) {
                    $lessons[] = [
                        'kind' => 'file',
                        'id' => $file->id,
                        'title' => $file->title,
                        'duration' => 'ملف',
                    ];
                }

                foreach ($chapter->textLessons as $text) {
                    $lessons[] = [
                        'kind' => 'text',
                        'id' => $text->id,
                        'title' => $text->title,
                        'duration' => 'نصي',
                    ];
                }

                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'lessons' => $lessons,
                ];
            })->all();
    }

    public function courseWatch(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);

        $firstSession = \App\Models\Session::where('webinar_id', $webinar->id)
            ->orderBy('date')->orderBy('id')->first();
        $firstQuiz = \App\Models\Quiz::where('webinar_id', $webinar->id)
            ->orderBy('id')->first();
        $firstAssignment = \App\Models\WebinarAssignment::where('webinar_id', $webinar->id)
            ->orderBy('id')->first();
        $files = \App\Models\File::where('webinar_id', $webinar->id)
            ->orderBy('id')->limit(10)->get();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-watch',
            'مشاهدة المحاضرة',
            array_merge(
                InstructorMockData::courseWatch($webinar->slug),
                [
                    'webinar' => $webinar,
                    'courseSlug' => $webinar->slug,
                    'lesson' => ['title' => $firstSession->title ?? $webinar->title],
                    'files' => $files->map(function ($file) {
                        return ['name' => $file->title, 'size' => $file->volume ?? ''];
                    })->all(),
                    'hasFiles' => $files->isNotEmpty(),
                ],
                $firstQuiz ? ['lectureQuiz' => [
                    'title' => $firstQuiz->title,
                    'subtitle' => $webinar->title,
                    'duration' => !empty($firstQuiz->time) ? $firstQuiz->time . ' دقيقة' : '—',
                    'questions_count' => \App\Models\QuizzesQuestion::where('quiz_id', $firstQuiz->id)->count() . ' أسئلة',
                    'pass_score' => $firstQuiz->pass_mark . '%',
                    'attempts' => $firstQuiz->attempt ? $firstQuiz->attempt . ' محاولات' : '—',
                ], 'hasLectureQuiz' => true] : ['hasLectureQuiz' => false],
                $firstAssignment ? ['lectureAssignment' => [
                    'title' => 'تكليف الدورة',
                    'subtitle' => $webinar->title,
                    'deadline' => $firstAssignment->deadline ? date('Y/m/d', (int) $firstAssignment->deadline) : 'غير محدود',
                    'attempts' => $firstAssignment->attempts ?? 'غير محدود',
                    'grade' => $firstAssignment->grade ?? '—',
                    'pass_grade' => $firstAssignment->pass_grade ?? '—',
                    'description' => '',
                    'file_name' => '',
                    'file_size' => '',
                ], 'hasLectureAssignment' => true] : ['hasLectureAssignment' => false]
            )
        );
    }

    public function courseAssignment(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        return $this->render(
            $request,
            'panel_v1.instructor.pages.assignment-review',
            'تقييم التكليف',
            array_merge(InstructorMockData::assignmentReview(1, $webinar->slug), ['webinar' => $webinar, 'courseSlug' => $webinar->slug])
        );
    }

    public function coursePerformance(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-performance',
            'لوحة أداء الدورة',
            array_merge(
                InstructorMockData::coursePerformance($webinar->slug),
                ['webinar' => $webinar, 'courseSlug' => $webinar->slug],
                $this->performanceData($webinar)
            )
        );
    }

    public function courseAssignments(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-assignments',
            'متطلبات الدورات',
            array_merge(InstructorMockData::courseAssignments($webinar->slug), ['webinar' => $webinar, 'courseSlug' => $webinar->slug])
        );
    }

    public function assignments(Request $request)
    {
        return $this->render($request, 'panel_v1.instructor.pages.assignments', 'إدارة الواجبات والتكليفات', InstructorMockData::assignments());
    }

    public function assignmentReview(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $history = \App\Models\WebinarAssignmentHistory::with(['assignment.webinar', 'student', 'messages'])
            ->findOrFail($id);

        $webinar = $history->assignment->webinar ?? null;
        if (empty($webinar) || (int) $webinar->teacher_id !== (int) $guardUser->id) {
            abort(404);
        }

        $messages = $history->messages->pluck('message')->filter()->values()->all();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.assignment-review',
            'تقييم التكليف',
            array_merge(InstructorMockData::assignmentReview($id, $webinar->slug), [
                'webinar' => $webinar,
                'courseSlug' => $webinar->slug,
                'historyId' => $history->id,
                'historyStatus' => $history->status,
                'historyGrade' => $history->grade,
                'reviewStudentName' => $history->student->full_name ?? '',
                'studentAnswerParagraphs' => !empty($messages) ? $messages : (InstructorMockData::assignmentReview($id, $webinar->slug)['studentAnswerParagraphs'] ?? []),
                'maxGrade' => $history->assignment->grade ?? 50,
                'passGrade' => $history->assignment->pass_grade ?? 25,
            ])
        );
    }

    public function gradeAssignment(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $history = \App\Models\WebinarAssignmentHistory::with(['assignment.webinar'])
            ->findOrFail($id);

        $webinar = $history->assignment->webinar ?? null;
        if (empty($webinar) || (int) $webinar->teacher_id !== (int) $guardUser->id) {
            abort(404);
        }

        $maxGrade = (int) ($history->assignment->grade ?? 50);

        $request->validate([
            'grade' => 'required|numeric|min:0|max:' . $maxGrade,
        ]);

        $grade = (int) $request->input('grade');
        $passGrade = (int) ($history->assignment->pass_grade ?? 0);

        $history->grade = $grade;
        $history->status = $grade >= $passGrade ? 'passed' : 'not_passed';
        $history->save();

        return redirect()
            ->route('panel.v1.instructor.assignments.review', ['id' => $history->id])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم اعتماد درجة الطالب بنجاح',
                'type' => 'success',
            ]);
    }

    public function consultations(Request $request)
    {
        $user = $request->user();

        $rows = [];
        if ($user) {
            $meetingIds = \App\Models\Meeting::where('creator_id', $user->id)->pluck('id')->all();
            $timeIds = !empty($meetingIds)
                ? \App\Models\MeetingTime::whereIn('meeting_id', $meetingIds)->pluck('id')->all()
                : [];
            $reservations = !empty($timeIds)
                ? \App\Models\ReserveMeeting::with(['user'])
                    ->whereIn('meeting_time_id', $timeIds)
                    ->orderBy('id', 'desc')
                    ->limit(50)
                    ->get()
                : collect();

            foreach ($reservations as $reservation) {
                $rows[] = [
                    'initials' => mb_substr($reservation->user->full_name ?? '?', 0, 2),
                    'name' => $reservation->user->full_name ?? '',
                    'email' => $reservation->user->email ?? '',
                    'joinType' => 'أونلاين',
                    'day' => $reservation->day ?? '',
                    'date' => !empty($reservation->reserved_at) ? date('Y/m/d', (int) $reservation->reserved_at) : '',
                    'time' => !empty($reservation->reserved_at) ? date('H:i', (int) $reservation->reserved_at) : '',
                    'amount' => handlePrice($reservation->paid_amount),
                ];
            }
        }

        $upcoming = !empty($meetingIds)
            ? \App\Models\MeetingTime::whereIn('meeting_id', $meetingIds)
                ->where('date', '>=', time())
                ->orderBy('date')
                ->first()
            : null;

        return $this->render(
            $request,
            'panel_v1.instructor.pages.consultations',
            'الجلسات الاستشارية',
            array_merge(InstructorMockData::consultations(), [
                'attendees' => $rows,
                'session' => $upcoming ? [
                    'title' => $upcoming->title ?? 'جلسة استشارية',
                    'status' => 'مجدولة',
                    'price' => handlePrice($upcoming->amount ?? 0),
                    'instructor' => $user->full_name,
                    'instructorInitials' => mb_substr($user->full_name ?? '?', 0, 1),
                    'date' => date('Y/m/d', (int) $upcoming->date),
                    'time' => date('H:i', (int) $upcoming->date),
                    'linkLabel' => 'لقاء أونلاين',
                ] : [],
            ])
        );
    }

    public function quizzes(Request $request)
    {
        $user = $request->user();

        $webinarIds = $this->teacherWebinars($user)->pluck('id')->all();

        $quizzes = !empty($webinarIds)
            ? \App\Models\Quiz::with(['webinar'])
                ->whereIn('webinar_id', $webinarIds)
                ->orderBy('id', 'desc')
                ->get()
            : collect();

        $quizIds = $quizzes->pluck('id')->all();

        $results = !empty($quizIds)
            ? \App\Models\QuizzesResult::with(['user', 'quiz.webinar'])
                ->whereIn('quiz_id', $quizIds)
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get()
            : collect();

        $waiting = $results->where('status', 'waiting')->values();
        $graded = $results->where('status', '!=', 'waiting')->values();
        $passRate = $graded->isNotEmpty()
            ? (int) round($graded->where('status', 'passed')->count() / $graded->count() * 100)
            : 0;

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quizzes',
            'إدارة الاختبارات',
            [
                'quizStats' => [
                    ['value' => $quizzes->count() . ' اختبار', 'label' => 'إجمالي الاختبارات'],
                    ['value' => $passRate . '%', 'label' => 'متوسط نسبة النجاح'],
                    ['value' => $waiting->count() . ' إجابة', 'label' => 'إجابات بانتظار التصحيح'],
                ],
                'pendingQuizzes' => $waiting->take(6)->map(function ($result) {
                    return [
                        'result_id' => $result->id,
                        'name' => $result->user->full_name ?? '',
                        'status' => 'بانتظار التصحيح',
                        'title' => $result->quiz->title ?? '',
                        'course' => $result->quiz->webinar->title ?? '',
                        'date' => date('Y/m/d', (int) $result->created_at),
                    ];
                })->all(),
                'quizRows' => $quizzes->map(function ($quiz) {
                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'course' => $quiz->webinar->title ?? '',
                        'questions' => \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->count(),
                        'duration' => !empty($quiz->time) ? $quiz->time . ' دقيقة' : 'مفتوح',
                        'fullGrade' => \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->sum('grade'),
                        'passGrade' => $quiz->pass_mark,
                        'students' => \App\Models\QuizzesResult::where('quiz_id', $quiz->id)->distinct('user_id')->count('user_id'),
                        'status' => $quiz->status === 'active' ? 'نشط' : 'معطل',
                        'created_at' => date('Y/m/d', (int) $quiz->created_at),
                    ];
                })->all(),
                'quizStudentRows' => $results->take(20)->map(function ($result) {
                    $attempts = \App\Models\QuizzesResult::where('quiz_id', $result->quiz_id)
                        ->where('user_id', $result->user_id)->count();
                    return [
                        'name' => $result->user->full_name ?? '',
                        'title' => $result->quiz->title ?? '',
                        'course' => $result->quiz->webinar->title ?? '',
                        'grade' => $result->user_grade . ' / ' . $result->quiz->total_mark,
                        'attempts' => '1 / ' . ($result->quiz->attempt ?? '—'),
                        'attempted_at' => date('Y/m/d', (int) $result->created_at),
                        'status' => $result->status === 'passed' ? 'ناجح' : ($result->status === 'waiting' ? 'بانتظار التصحيح' : 'راسب'),
                        'result_id' => $result->id,
                    ];
                })->all(),
            ]
        );
    }

    public function quizView(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);
        $webinar = $quiz->webinar;

        $questions = \App\Models\QuizzesQuestion::with(['quizzesQuestionsAnswers'])
            ->where('quiz_id', $quiz->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'title' => $question->title,
                    'type' => $question->type,
                    'grade' => $question->grade,
                    'options' => $question->quizzesQuestionsAnswers->map(function ($answer) {
                        return ['id' => $answer->id, 'text' => $answer->title, 'correct' => (bool) $answer->correct];
                    })->all(),
                ];
            })->all();

        $waitingResults = \App\Models\QuizzesResult::with(['user'])
            ->where('quiz_id', $quiz->id)
            ->where('status', 'waiting')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quiz-view',
            'عرض الاختبار',
            array_merge(InstructorMockData::quizView($id), [
                'quizId' => $quiz->id,
                'slug' => $webinar->slug ?? 'demo',
                'webinar' => $webinar,
                'quizTitle' => $quiz->title,
                'quizMeta' => [
                    'pass_mark' => $quiz->pass_mark,
                    'time' => $quiz->time,
                    'attempt' => $quiz->attempt,
                    'status' => $quiz->status,
                ],
                'realQuestions' => $questions,
                'waitingResults' => $waitingResults,
            ])
        );
    }

    public function quizCreate(Request $request)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quiz-form',
            'اختبار جديد',
            [
                'quiz' => null,
                'webinars' => $this->teacherWebinars($guardUser)->map(function ($webinar) {
                    return ['id' => $webinar->id, 'title' => $webinar->title];
                })->all(),
            ]
        );
    }

    public function quizStore(Request $request)
    {
        $guardUser = $request->user();
        if (!$guardUser || !$guardUser->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'webinar_id' => 'required|exists:webinars,id',
            'title' => 'required|string|max:255',
            'pass_mark' => 'required|integer|min:0',
            'time' => 'nullable|integer|min:0',
            'attempt' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $webinar = \App\Models\Webinar::where('id', $request->input('webinar_id'))
            ->where('teacher_id', $guardUser->id)
            ->firstOrFail();

        $quiz = new \App\Models\Quiz();
        $quiz->webinar_id = $webinar->id;
        $quiz->creator_id = $guardUser->id;
        $quiz->pass_mark = $request->input('pass_mark');
        $quiz->time = $request->input('time', 0);
        $quiz->attempt = $request->input('attempt');
        $quiz->certificate = 0;
        $quiz->status = $request->input('status', 'active');
        $quiz->created_at = time();
        $quiz->updated_at = time();
        $quiz->save();

        $translation = $quiz->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        return redirect()
            ->route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم إنشاء الاختبار، أضف الأسئلة الآن', 'type' => 'success']);
    }

    public function quizEdit(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quiz-form',
            'تعديل الاختبار',
            [
                'quiz' => $quiz,
                'webinars' => $this->teacherWebinars($guardUser)->map(function ($webinar) {
                    return ['id' => $webinar->id, 'title' => $webinar->title];
                })->all(),
            ]
        );
    }

    public function quizUpdate(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser || !$guardUser->isTeacher()) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);

        $request->validate([
            'title' => 'required|string|max:255',
            'pass_mark' => 'required|integer|min:0',
            'time' => 'nullable|integer|min:0',
            'attempt' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $quiz->pass_mark = $request->input('pass_mark');
        $quiz->time = $request->input('time', 0);
        $quiz->attempt = $request->input('attempt');
        $quiz->status = $request->input('status');
        $quiz->updated_at = time();
        $quiz->save();

        $translation = $quiz->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        return redirect()
            ->route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ التعديلات', 'type' => 'success']);
    }

    public function quizDelete(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser || !$guardUser->isTeacher()) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);
        $quiz->delete();

        return redirect()
            ->route('panel.v1.instructor.quizzes')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حذف الاختبار', 'type' => 'success']);
    }

    public function questionStore(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser || !$guardUser->isTeacher()) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);

        $request->validate([
            'title' => 'required|string|max:1000',
            'type' => 'required|in:multiple,descriptive',
            'grade' => 'required|integer|min:1',
        ]);

        $question = new \App\Models\QuizzesQuestion();
        $question->quiz_id = $quiz->id;
        $question->creator_id = $guardUser->id;
        $question->grade = $request->input('grade');
        $question->type = $request->input('type');
        $question->created_at = time();
        $question->updated_at = time();
        $question->save();

        $translation = $question->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $request->input('title');
        $translation->save();

        if ($question->type === 'multiple') {
            $options = array_filter(array_map('trim', (array) $request->input('options', [])));
            $correctIndex = (int) $request->input('correct_index', 0);
            foreach (array_values($options) as $index => $optionTitle) {
                $answer = new \App\Models\QuizzesQuestionsAnswer();
                $answer->question_id = $question->id;
                $answer->creator_id = $guardUser->id;
                $answer->correct = $index === $correctIndex;
                $answer->created_at = time();
                $answer->updated_at = time();
                $answer->save();

                $answerTranslation = $answer->translateOrNew('ar');
                $answerTranslation->locale = 'ar';
                $answerTranslation->title = mb_substr($optionTitle, 0, 1000);
                $answerTranslation->save();
            }
        }

        return redirect()
            ->route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة السؤال', 'type' => 'success']);
    }

    public function questionDelete(Request $request, int $id, int $questionId)
    {
        $guardUser = $request->user();
        if (!$guardUser || !$guardUser->isTeacher()) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);

        \App\Models\QuizzesQuestion::where('id', $questionId)
            ->where('quiz_id', $quiz->id)
            ->delete();

        return redirect()
            ->route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حذف السؤال', 'type' => 'success']);
    }

    public function gradeQuizResult(Request $request, int $resultId)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $result = \App\Models\QuizzesResult::with(['quiz.webinar', 'user'])->findOrFail($resultId);

        if ((int) ($result->quiz->webinar->teacher_id ?? 0) !== (int) $guardUser->id) {
            abort(404);
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quiz-result-grade',
            'تصحيح نتيجة',
            ['quizResult' => $result]
        );
    }

    public function storeQuizResultGrade(Request $request, int $resultId)
    {
        $guardUser = $request->user();
        if (!$guardUser || !$guardUser->isTeacher()) {
            return redirect('/login');
        }

        $result = \App\Models\QuizzesResult::with(['quiz'])->findOrFail($resultId);

        $quiz = $this->teacherQuizOrFail($guardUser, $result->quiz_id);

        $request->validate([
            'user_grade' => 'required|integer|min:0',
            'status' => 'required|in:passed,failed,waiting',
        ]);

        $result->user_grade = $request->input('user_grade');
        $result->status = $request->input('status');
        $result->save();

        return redirect()
            ->route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم اعتماد النتيجة', 'type' => 'success']);
    }

    private function teacherQuizOrFail($user, int $id)
    {
        $quiz = \App\Models\Quiz::with(['webinar'])->findOrFail($id);

        if ((int) ($quiz->webinar->teacher_id ?? 0) !== (int) $user->id) {
            abort(404);
        }

        return $quiz;
    }

    public function certificates(Request $request)
    {
        $user = $request->user();
        $webinarIds = $user ? $this->teacherWebinars($user)->pluck('id')->all() : [];

        $issued = !empty($webinarIds)
            ? \App\Models\Certificate::with(['student', 'webinar'])
                ->whereIn('webinar_id', $webinarIds)
                ->orderBy('id', 'desc')
                ->limit(30)
                ->get()
            : collect();

        $completionRows = !empty($webinarIds)
            ? \App\Models\Webinar::whereIn('id', $webinarIds)
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($webinar) {
                    $generated = \App\Models\Certificate::where('webinar_id', $webinar->id)->count();
                    $last = \App\Models\Certificate::where('webinar_id', $webinar->id)->orderBy('id', 'desc')->first();
                    return [
                        'title' => $webinar->title,
                        'course' => $webinar->category->title ?? '',
                        'generated' => $generated,
                        'last_at' => $last ? date('Y/m/d', (int) $last->created_at) : '—',
                    ];
                })->all()
            : [];

        return $this->render(
            $request,
            'panel_v1.instructor.pages.certificates',
            'إدارة الشهادات',
            array_merge(InstructorMockData::certificates(), [
                'certificateStats' => [
                    ['value' => (string) $issued->count(), 'label' => 'شهادات مصدرة'],
                    ['value' => (string) count($webinarIds), 'label' => 'دورات'],
                ],
                'recentCertificates' => $issued->take(8)->map(function ($certificate) {
                    return [
                        'title' => $certificate->webinar->title ?? '',
                        'student' => $certificate->student->full_name ?? '',
                    ];
                })->all(),
                'completionRows' => $completionRows,
            ])
        );
    }

    public function finance(Request $request)
    {
        $guardUser = $request->user();

        $salesRows = [];
        if ($guardUser) {
            $salesRows = \App\Models\Sale::with(['buyer', 'webinar'])
                ->where('seller_id', $guardUser->id)
                ->whereNull('refund_at')
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($sale) {
                    $isCourse = $sale->type === 'webinar';
                    return [
                        'name' => $sale->buyer->full_name ?? '',
                        'email' => $sale->buyer->email ?? '',
                        'service' => $sale->webinar->title ?? $sale->type,
                        'service_id' => $sale->id,
                        'original_price' => handlePrice($sale->amount),
                        'discount' => handlePrice($sale->discount),
                        'total' => handlePrice($sale->total_amount),
                        'net' => handlePrice($sale->total_amount - ($sale->commission ?? 0)),
                        'type' => $isCourse ? 'course' : 'meeting',
                        'type_label' => $isCourse ? 'دورة' : 'استشارة',
                        'date' => date('Y/m/d', (int) $sale->created_at),
                        'time' => date('H:i', (int) $sale->created_at),
                    ];
                })->all();
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.finance',
            'المالية والأرباح',
            array_merge(InstructorMockData::finance(), ['salesRows' => $salesRows])
        );
    }

    public function requestPayout(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $readyPayout = $user->getPayout();
        $financialSettings = getFinancialSettings();

        if (!empty($financialSettings['minimum_payout']) && $readyPayout < $financialSettings['minimum_payout']) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'الرصيد أقل من الحد الأدنى للسحب',
                'status' => 'error',
            ]]);
        }

        if (!$user->financial_approval) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'بياناتك المالية غير معتمدة من الإدارة بعد',
                'status' => 'error',
            ]]);
        }

        if (empty($user->selectedBank)) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'حدد حسابك البنكي من الإعدادات أولاً',
                'status' => 'error',
            ]]);
        }

        \App\Models\Payout::create([
            'user_id' => $user->id,
            'user_selected_bank_id' => $user->selectedBank->id,
            'amount' => $readyPayout,
            'status' => \App\Models\Payout::$waiting,
            'created_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.payouts')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم تسجيل طلب السحب بنجاح', 'type' => 'success']);
    }

    public function payouts(Request $request)    {
        $guardUser = $request->user();

        $summary = InstructorMockData::payouts()['payoutSummary'] ?? [];
        $payoutRows = [];

        if ($guardUser) {
            try {
                $summary['available'] = handlePrice($guardUser->getPayout());
                $summary['total_income'] = handlePrice($guardUser->getIncome());
            } catch (\Throwable $e) {
            }

            $statusLabels = ['waiting' => 'قيد المعالجة', 'done' => 'مكتمل', 'reject' => 'مرفوض'];

            $payoutRows = \App\Models\Payout::where('user_id', $guardUser->id)
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($payout) use ($statusLabels) {
                    return [
                        'id' => '#' . $payout->id,
                        'datetime' => date('Y/m/d H:i', (int) $payout->created_at),
                        'type_line1' => 'طلب سحب أرباح',
                        'type_line2' => null,
                        'amount' => handlePrice($payout->amount),
                        'status' => $statusLabels[$payout->status] ?? $payout->status,
                    ];
                })->all();
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.payouts',
            'ادارة المستحقات والسحب',
            array_merge(InstructorMockData::payouts(), [
                'payoutSummary' => $summary,
                'payoutRows' => $payoutRows,
            ])
        );
    }

    public function marketing(Request $request)
    {
        $user = $request->user();

        $discountRows = [];
        if ($user) {
            $codes = \App\Models\Discount::where('creator_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($discount) {
                    return [
                        'name' => $discount->title,
                        'email' => 'كود: ' . $discount->code,
                        'course' => 'كوبون خصم ' . $discount->percent . '%',
                        'course_id' => $discount->id,
                        'original_price' => '—',
                        'discount' => $discount->percent . '%',
                        'total' => '—',
                        'net' => '—',
                        'type' => 'كوبون',
                        'date' => date('Y/m/d', (int) $discount->created_at),
                        'time' => '',
                    ];
                })->all();

            $discountRows = array_merge(
                $codes,
                \App\Models\Sale::with(['buyer', 'webinar'])
                ->where('seller_id', $user->id)
                ->whereNull('refund_at')
                ->where(function ($query) {
                    $query->where('discount', '>', 0)->orWhereNotNull('promotion_id');
                })
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($sale) {
                    return [
                        'name' => $sale->buyer->full_name ?? '',
                        'email' => $sale->buyer->email ?? '',
                        'course' => $sale->webinar->title ?? $sale->type,
                        'course_id' => $sale->webinar_id ?? $sale->id,
                        'original_price' => handlePrice($sale->amount),
                        'discount' => handlePrice($sale->discount),
                        'total' => handlePrice($sale->total_amount),
                        'net' => handlePrice($sale->total_amount - ($sale->commission ?? 0)),
                        'type' => 'خصم',
                        'date' => date('Y/m/d', (int) $sale->created_at),
                        'time' => date('H:i', (int) $sale->created_at),
                    ];
                })->all()
            );
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.marketing',
            'إدارة التسويق والعروض',
            array_merge(InstructorMockData::marketing(), [
                'discountRows' => $discountRows,
                'promoRows' => [],
                'couponRows' => [],
            ])
        );
    }

    public function discountStore(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'percent' => 'required|integer|min:1|max:100',
        ]);

        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (\App\Models\Discount::where('code', $code)->exists());

        \App\Models\Discount::create([
            'creator_id' => $user->id,
            'title' => $request->input('title'),
            'discount_type' => 'percentage',
            'source' => 'all',
            'code' => $code,
            'percent' => (int) $request->input('percent'),
            'count' => 100,
            'user_type' => 'all_users',
            'expired_at' => time() + 30 * 86400,
            'created_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.marketing')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم إنشاء كود الخصم: ' . $code, 'type' => 'success']);
    }

    public function support(Request $request)
    {
        $user = $request->user();

        $tickets = $user
            ? \App\Models\Support::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => '#' . $ticket->id,
                        'raw_id' => $ticket->id,
                        'subject' => $ticket->title,
                        'status' => $ticket->status === 'open' ? 'مفتوحة' : 'مغلقة',
                        'date' => date('Y/m/d', (int) $ticket->created_at),
                    ];
                })->all()
            : [];

        return $this->render(
            $request,
            'panel_v1.instructor.pages.support',
            'مركز الدعم الفني وإدارة التذاكر',
            array_merge(InstructorMockData::support(), [
                'supportTickets' => $tickets,
                'courseSupportRows' => [],
            ])
        );
    }

    public function settings(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.settings',
            'إعدادات الملف الشخصي',
            array_merge(InstructorMockData::settings(), $this->profileExtraViewData($request, $user), $this->profileAboutData($user), $this->profileFinancialData($user), [
                'loginHistories' => $this->profileLoginHistories($user),
            ])
        );
    }

    public function updateExtra(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'country_id' => 'nullable|integer|exists:regions,id',
            'province_id' => 'nullable|integer|exists:regions,id',
            'city_id' => 'nullable|integer|exists:regions,id',
            'district_id' => 'nullable|integer|exists:regions,id',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:man,woman',
            'meeting_type' => 'nullable|in:in_person,online,all',
        ]);

        $this->saveProfileExtra($request, $user);

        return redirect()->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ المعلومات الإضافية', 'type' => 'success']);
    }

    public function updateFinancial(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $this->saveProfileFinancial($request, $user);

        return redirect()->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ بيانات الهوية والمالية', 'type' => 'success']);
    }

    public function updateImages(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'avatar' => 'nullable|image|max:5120',
            'cover_img' => 'nullable|image|max:5120',
            'profile_secondary_image' => 'nullable|image|max:5120',
            'profile_video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:51200',
            'signature_img' => 'nullable|image|max:5120',
        ]);

        $this->saveProfileMedia($request, $user);

        return redirect()->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ الصور', 'type' => 'success']);
    }

    public function deleteMedia(string $type)
    {
        $user = request()->user();

        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        if (!$this->deleteProfileMedia($user, $type)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف الملف', 'type' => 'success']);
    }

    public function updateAbout(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'about' => 'nullable|string|max:5000',
            'bio' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'occupations' => 'nullable|array|max:10',
            'occupations.*' => 'integer|exists:categories,id',
        ]);

        $this->saveProfileAbout($request, $user);

        return redirect()->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ بيانات "حول"', 'type' => 'success']);
    }

    public function storeMeta(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$this->storeProfileMeta($user, $request->input('name'), $request->input('value'))) {
            return response()->json([], 422);
        }

        return response()->json(['code' => 200], 200);
    }

    public function updateMeta(Request $request, $metaId)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$this->updateProfileMeta($user, $metaId, $request->input('name'), $request->input('value'))) {
            return response()->json([], 422);
        }

        return response()->json(['code' => 200], 200);
    }

    public function deleteMeta(Request $request, $metaId)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$this->deleteProfileMeta($user, $metaId)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم الحذف', 'type' => 'success']);
    }

    public function storeAttachment(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $this->storeProfileAttachment($request, $user);

        return redirect()->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة المرفق', 'type' => 'success']);
    }

    public function updateAttachment(Request $request, $attachmentId)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (empty($this->updateProfileAttachment($request, $user, $attachmentId))) {
            abort(404);
        }

        return redirect()->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم تحديث المرفق', 'type' => 'success']);
    }

    public function deleteAttachment($attachmentId)
    {
        $user = request()->user();

        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        if (!$this->deleteProfileAttachment($user, $attachmentId)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم حذف المرفق', 'type' => 'success']);
    }

    public function endSession($sessionId)
    {
        $user = request()->user();

        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        if (!$this->endProfileSession($user, $sessionId)) {
            abort(404);
        }

        return back()->with('toast', ['title' => 'تم', 'msg' => 'تم إنهاء الجلسة', 'type' => 'success']);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate([
            'full_name' => 'required|string|max:128',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:32|unique:users,mobile,' . $user->id,
            'language' => 'nullable|string|max:128',
            'timezone' => 'nullable|string|max:255',
            'offline_message' => 'nullable|string|max:2000',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->full_name = $request->input('full_name');
        $user->email = $request->input('email');
        $user->mobile = $request->input('mobile');
        $user->language = $request->input('language', $user->language);
        $user->timezone = $request->input('timezone', $user->timezone);

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->input('password'));
        }

        $user->save();

        $this->saveProfileAccountOptions($request, $user);

        // Also persist any extra/about/financial/images fields that were submitted together
        // (the view has a single outer form wrapping all tabs).
        try {
            if ($request->hasAny(['birthday', 'gender', 'meeting_type', 'level_of_training', 'country_id', 'province_id', 'city_id', 'district_id', 'address', 'latitude', 'longitude', 'socials'])) {
                $this->saveProfileExtra($request, $user);
            }
        } catch (\Throwable $e) {
        }
        try {
            if ($request->hasAny(['headline', 'bio', 'about', 'occupations'])) {
                $this->saveProfileAbout($request, $user);
            }
        } catch (\Throwable $e) {
        }
        try {
            if ($request->hasAny(['bank_id', 'identity_scan', 'certificate'])) {
                $this->saveProfileFinancial($request, $user);
            }
        } catch (\Throwable $e) {
        }
        try {
            if ($request->hasFile('avatar') || $request->hasFile('cover_img') || $request->hasFile('profile_secondary_image') || $request->hasFile('profile_video') || $request->hasFile('signature_img')) {
                $this->saveProfileMedia($request, $user);
            }
        } catch (\Throwable $e) {
        }

        return redirect()
            ->route('panel.v1.instructor.settings')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ الإعدادات بنجاح', 'type' => 'success']);
    }

    private function teacherWebinarOrFail($user, string $slug)
    {
        return Webinar::where('slug', $slug)
            ->where('teacher_id', $user->id)
            ->firstOrFail();
    }

    private function render(Request $request, string $view, string $pageTitle, array $data = [])
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view($view, array_merge($data, [
            'pageTitle' => $pageTitle,
            'authUser' => $user,
        ]));
    }

    private function teacherWebinars($user)
    {
        return Webinar::with(['category'])
            ->where('teacher_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    private function homeData($user): array
    {
        $webinars = $this->teacherWebinars($user);
        $webinarIds = $webinars->pluck('id')->all();

        $sales = !empty($webinarIds)
            ? Sale::whereIn('webinar_id', $webinarIds)->whereNull('refund_at')->get()
            : collect();

        $earnings = $sales->where('seller_id', $user->id)->sum('total_amount');
        $students = $sales->pluck('buyer_id')->filter()->unique()->count();

        $assignmentIds = !empty($webinarIds)
            ? WebinarAssignment::whereIn('webinar_id', $webinarIds)->pluck('id')->all()
            : [];

        $pendingGradingCount = !empty($assignmentIds)
            ? WebinarAssignmentHistory::whereIn('assignment_id', $assignmentIds)->where('status', 'pending')->count()
            : 0;

        $activeQuizzes = !empty($webinarIds)
            ? Quiz::whereIn('webinar_id', $webinarIds)->where('status', 'active')->count()
            : 0;

        $upcomingLectures = !empty($webinarIds)
            ? Session::with('webinar')
                ->whereIn('webinar_id', $webinarIds)
                ->where('status', 'active')
                ->where('date', '>=', time())
                ->orderBy('date')
                ->limit(3)
                ->get()
                ->map(function ($session) {
                    return [
                        'title' => $session->webinar->title ?? 'محاضرة مباشرة',
                        'when' => date('Y/m/d H:i', (int) $session->date),
                    ];
                })->all()
            : [];

        $pendingGrading = !empty($assignmentIds)
            ? WebinarAssignmentHistory::with(['student'])
                ->whereIn('assignment_id', $assignmentIds)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($history) {
                    return [
                        'title' => 'تكليف بانتظار التقييم',
                        'student' => $history->student->full_name ?? '',
                        'time' => date('Y/m/d', (int) $history->created_at),
                    ];
                })->all()
            : [];

        return array_merge(InstructorMockData::common(), [
            'stats' => [
                ['label' => 'إجمالي الأرباح', 'value' => handlePrice($earnings)],
                ['label' => 'إجمالي الطلاب', 'value' => (string) $students],
                ['label' => 'الدورات النشطة', 'value' => (string) $webinars->where('status', 'active')->count()],
                ['label' => 'تكليفات وواجبات', 'value' => (string) $pendingGradingCount],
                ['label' => 'الاختبارات النشطة', 'value' => (string) $activeQuizzes],
            ],
            'courses' => $webinars->take(3)->map(function ($webinar) {
                return [
                    'title' => $webinar->title,
                    'subtitle' => $webinar->category->title ?? '',
                    'progress' => $this->webinarFinishedProgress($webinar),
                ];
            })->all(),
            'quickActions' => InstructorMockData::home()['quickActions'],
            'upcomingLectures' => $upcomingLectures,
            'pendingGrading' => $pendingGrading,
        ]);
    }

    private function courseCards($user): array
    {
        return $this->teacherWebinars($user)->map(function ($webinar) {
            return [
                'title' => $webinar->title,
                'subtitle' => $webinar->category->title ?? '',
                'slug' => $webinar->slug,
                'type' => 'دورة مسجلة',
                'activity' => '—',
                'duration' => !empty($webinar->duration) ? $webinar->duration . ' دقيقة' : '—',
                'lectures' => $webinar->sessions->count() + $webinar->files->count(),
                'assignments' => WebinarAssignment::where('webinar_id', $webinar->id)->count(),
                'progress' => $this->webinarFinishedProgress($webinar),
            ];
        })->all();
    }

    private function performanceData($webinar): array
    {
        $sales = \App\Models\Sale::with(['buyer'])
            ->where('webinar_id', $webinar->id)
            ->whereNull('refund_at')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $students = $sales->map(function ($sale) use ($webinar) {
            $progress = 0;
            try {
                $progress = (int) $webinar->getProgress(false, $sale->buyer);
            } catch (\Throwable $e) {
                $progress = 0;
            }
            $passedExams = \App\Models\QuizzesResult::where('user_id', $sale->buyer_id)
                ->where('status', 'passed')
                ->count();
            return [
                'name' => $sale->buyer->full_name ?? '',
                'email' => $sale->buyer->email ?? '',
                'progress' => $progress,
                'activity' => '—',
                'exams' => $passedExams,
                'assignments' => \App\Models\WebinarAssignmentHistory::where('student_id', $sale->buyer_id)
                    ->where('status', 'passed')->count(),
                'certificates' => \App\Models\Certificate::where('student_id', $sale->buyer_id)
                    ->where('webinar_id', $webinar->id)->count(),
            ];
        })->all();

        $avgProgress = !empty($students)
            ? (int) round(collect($students)->avg('progress'))
            : 0;

        return [
            'perfStats' => [
                ['value' => count($students) . ' طالب', 'label' => 'طلاب الدورة', 'tone' => 'green'],
                ['value' => $avgProgress . '%', 'label' => 'متوسط التقدم', 'tone' => 'yellow'],
                ['value' => count($students) . ' مبيعات', 'label' => 'إجمالي المبيعات', 'tone' => 'green'],
            ],
            'students' => $students,
        ];
    }

    private function webinarFinishedProgress($webinar): int
    {
        try {
            $total = $webinar->sessions->count();
            if ($total < 1) {
                return 0;
            }
            $finished = $webinar->sessions->where('status', 'finished')->count();
            return (int) round($finished / $total * 100);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @return \App\User|\Illuminate\Http\RedirectResponse
     */
    private function resolveInstructor(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isTeacher()) {
            if ($user->isUser()) {
                return redirect()->route('panel.v1.student.home');
            }

            if ($user->isAdmin()) {
                return redirect()->route('panel.v1.admin.home');
            }

            if ($user->isOrganization()) {
                return redirect()->route('panel.v1.organization.home');
            }

            return redirect('/panel');
        }

        return $user;
    }
}
