<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\QuizzesResult;
use Illuminate\Http\Request;

class CoursePlayerController extends Controller
{
    public function watch(Request $request, string $slug)
    {
        $resolved = $this->resolveCourse($request, $slug);
        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }
        [$user, $webinar] = $resolved;

        $player = $this->buildPlayerData($webinar, $user, $request);

        // Visiting an item marks it complete (same CourseLearning store as old learning page)
        if (!empty($player['currentLesson']['key'])) {
            $this->markLessonVisited($user, $player['currentLesson']);
            // Rebuild so progress + chapter checks reflect the new visit
            $player = $this->buildPlayerData($webinar, $user, $request);
        }

        $lesson = $player['currentLesson'] ?? ['title' => $this->localizedTitle($webinar) ?: $webinar->title];
        $hasQuiz = !empty($player['lectureQuiz']);
        $hasAssignment = !empty($player['lectureAssignment']);
        $files = $player['files'] ?? collect();
        $hasFiles = $files->isNotEmpty();

        return view('panel_v1.student.course-player.pages.watch', array_merge($player, [
            'pageTitle' => 'مشاهدة الدورة',
            'authUser' => $user,
            'webinar' => $webinar,
            'courseTitle' => $player['course']['title'] ?? $webinar->title,
            'lesson' => $lesson,
            'currentMedia' => $player['currentMedia'] ?? null,
            'hasLectureQuiz' => $hasQuiz,
            'hasLectureAssignment' => $hasAssignment,
            'hasComments' => false,
            'hasFiles' => $hasFiles,
            'files' => $files->map(function ($f) use ($webinar) {
                $isDownloadable = (bool) ($f->downloadable ?? false);
                $url = $isDownloadable
                    ? url('/course/' . $webinar->slug . '/file/' . $f->id . '/download')
                    : ($f->file ?? '');

                return [
                    'id' => $f->id,
                    'name' => $this->localizedTitle($f) ?: ($f->title ?: 'ملف'),
                    'size' => $f->volume ?? '',
                    'url' => $url,
                    'downloadable' => $isDownloadable,
                ];
            })->all(),
            'lectureQuiz' => $player['lectureQuiz'] ?? null,
            'lectureAssignment' => $player['lectureAssignment'] ?? null,
        ]));
    }

    private function buildPlayerData($webinar, $user, ?Request $request = null): array
    {
        $progress = 0;
        try {
            $progress = (int) round($webinar->getProgress(true, $user));
        } catch (\Throwable $e) {
            $progress = 0;
        }
        $progress = max(0, min(100, $progress));

        $learned = \App\Models\CourseLearning::where('user_id', $user->id)->get();
        $learnedFileIds = $learned->pluck('file_id')->filter()->map(fn ($id) => (int) $id)->all();
        $learnedSessionIds = $learned->pluck('session_id')->filter()->map(fn ($id) => (int) $id)->all();
        $learnedTextIds = $learned->pluck('text_lesson_id')->filter()->map(fn ($id) => (int) $id)->all();

        $chapters = \App\Models\WebinarChapter::with([
            'sessions.translations',
            'files.translations',
            'textLessons.translations',
            'translations',
        ])
            ->where('webinar_id', $webinar->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $requestedItem = $request ? (string) $request->get('item', '') : '';
        $currentLesson = null;
        $currentMedia = null;
        $activeChapterId = null;
        $chapterList = [];
        $fallbackLesson = null;
        $fallbackMedia = null;
        $fallbackChapterId = null;

        foreach ($chapters as $chapterIndex => $chapter) {
            $items = [];
            $chapterTitle = $this->localizedTitle($chapter) ?: ('المحاضرة ' . ($chapterIndex + 1));

            $contentRows = [];
            foreach ($chapter->files->sortBy('order') as $file) {
                $contentRows[] = ['kind' => 'file', 'model' => $file];
            }
            foreach ($chapter->sessions->sortBy('id') as $session) {
                $contentRows[] = ['kind' => 'session', 'model' => $session];
            }
            foreach ($chapter->textLessons->sortBy('id') as $text) {
                $contentRows[] = ['kind' => 'text', 'model' => $text];
            }

            $completedInChapter = 0;

            foreach ($contentRows as $row) {
                $model = $row['model'];
                $kind = $row['kind'];
                $itemKey = $kind . '_' . $model->id;
                $title = $this->localizedTitle($model) ?: ($kind === 'file' ? 'محتوى' : 'عنصر');
                $type = $kind === 'file'
                    ? (($model->file_type === 'video' || $model->storage === 'youtube' || $model->storage === 'vimeo') ? 'video' : 'file')
                    : ($kind === 'session' ? 'video' : 'text');

                $isCompleted = match ($kind) {
                    'file' => in_array((int) $model->id, $learnedFileIds, true),
                    'session' => in_array((int) $model->id, $learnedSessionIds, true),
                    'text' => in_array((int) $model->id, $learnedTextIds, true),
                    default => false,
                };
                if ($isCompleted) {
                    $completedInChapter++;
                }

                $isActive = $requestedItem !== '' && $requestedItem === $itemKey;
                $media = $this->buildMediaPayload($kind, $model, $webinar);

                if ($isActive) {
                    $currentLesson = [
                        'title' => $title,
                        'type' => $type,
                        'kind' => $kind,
                        'id' => $model->id,
                        'key' => $itemKey,
                        'chapter_title' => $chapterTitle,
                        'completed' => $isCompleted,
                    ];
                    $currentMedia = $media;
                    $activeChapterId = $chapter->id;
                }

                if ($fallbackLesson === null && ($type === 'video' || $kind === 'file')) {
                    $fallbackLesson = [
                        'title' => $title,
                        'type' => $type,
                        'kind' => $kind,
                        'id' => $model->id,
                        'key' => $itemKey,
                        'chapter_title' => $chapterTitle,
                        'completed' => $isCompleted,
                    ];
                    $fallbackMedia = $media;
                    $fallbackChapterId = $chapter->id;
                }

                $items[] = [
                    'title' => $title,
                    'type' => $type,
                    'kind' => $kind,
                    'active' => $isActive,
                    'completed' => $isCompleted,
                    'id' => $model->id,
                    'key' => $itemKey,
                    'url' => route('panel.v1.student.course.watch', [
                        'slug' => $webinar->slug,
                        'item' => $itemKey,
                    ]),
                ];
            }

            $isExpanded = $activeChapterId
                ? ((int) $activeChapterId === (int) $chapter->id)
                : ($chapterIndex === 0);

            $itemCount = count($items);
            $chapterCompleted = $itemCount > 0 && $completedInChapter >= $itemCount;

            $chapterList[] = [
                'title' => $chapterTitle,
                'subtitle' => $items[0]['title'] ?? 'هنا عنوان المحاضرة',
                'expanded' => $isExpanded,
                'completed' => $chapterCompleted,
                'completed_count' => $completedInChapter,
                'items_count' => $itemCount,
                'items' => $items,
            ];
        }

        if (!$currentLesson && $fallbackLesson) {
            $currentLesson = $fallbackLesson;
            $currentMedia = $fallbackMedia;
            $activeChapterId = $fallbackChapterId;
            foreach ($chapterList as &$chapterRow) {
                $chapterRow['expanded'] = false;
                foreach ($chapterRow['items'] as &$itemRow) {
                    if (($itemRow['key'] ?? '') === ($fallbackLesson['key'] ?? '')) {
                        $itemRow['active'] = true;
                        $chapterRow['expanded'] = true;
                    }
                }
            }
            unset($chapterRow, $itemRow);
        }

        if (!$currentLesson) {
            $currentLesson = [
                'title' => $this->localizedTitle($webinar) ?: $webinar->title,
                'type' => 'empty',
                'kind' => null,
                'id' => null,
                'key' => null,
                'chapter_title' => '',
                'completed' => false,
            ];
        }

        // Attachments for files tab: non-video files of the current chapter (or all downloadable)
        $filesQuery = \App\Models\File::with('translations')
            ->where('webinar_id', $webinar->id)
            ->where(function ($q) {
                $q->where('file_type', '!=', 'video')
                    ->orWhere('downloadable', true);
            })
            ->orderBy('chapter_id')
            ->orderBy('order')
            ->orderBy('id');

        if ($activeChapterId) {
            $chapterFiles = (clone $filesQuery)->where('chapter_id', $activeChapterId)->get();
            $files = $chapterFiles->isNotEmpty() ? $chapterFiles : $filesQuery->limit(10)->get();
        } else {
            $files = $filesQuery->limit(10)->get();
        }

        $lectureQuiz = null;
        $quiz = \App\Models\Quiz::where('webinar_id', $webinar->id)->where('status', 'active')->orderBy('id')->first();
        if ($quiz) {
            $lectureQuiz = [
                'title' => $this->localizedTitle($quiz) ?: $quiz->title,
                'subtitle' => $this->localizedTitle($webinar) ?: $webinar->title,
                'duration' => !empty($quiz->time) ? $quiz->time . ' دقيقة' : '—',
                'questions_count' => \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->count() . ' أسئلة',
                'pass_score' => $quiz->pass_mark . '%',
                'attempts' => $quiz->attempt ? $quiz->attempt . ' محاولات' : '—',
            ];
        }

        $lectureAssignment = null;
        $assignment = \App\Models\WebinarAssignment::where('webinar_id', $webinar->id)->where('status', 'active')->orderBy('id')->first();
        if ($assignment) {
            $lectureAssignment = [
                'title' => $this->localizedTitle($assignment) ?: ($assignment->title ?? 'تكليف الدورة'),
                'subtitle' => $this->localizedTitle($webinar) ?: $webinar->title,
                'deadline' => $assignment->deadline ? date('Y/m/d', (int) $assignment->deadline) : 'غير محدود',
                'attempts' => $assignment->attempts ?? 'غير محدود',
                'grade' => $assignment->grade ?? '—',
                'pass_grade' => $assignment->pass_grade ?? '—',
                'description' => $assignment->description ?? '',
                'file_name' => '',
                'file_size' => '',
            ];
        }

        $courseTitle = $this->localizedTitle($webinar) ?: $webinar->title;
        $courseSubtitle = $webinar->translate('ar')?->summary
            ?: ($webinar->category->title ?? 'الادارة والتنفيذ');

        return [
            'slug' => $webinar->slug,
            'course' => [
                'title' => $courseTitle,
                'subtitle' => $courseSubtitle,
                'progress' => $progress,
                'progress_label' => 'نسبة الإنجاز',
            ],
            'chapters' => $chapterList,
            'currentLesson' => $currentLesson,
            'currentMedia' => $currentMedia,
            'files' => $files,
            'lectureQuiz' => $lectureQuiz,
            'lectureAssignment' => $lectureAssignment,
        ];
    }

    private function markLessonVisited($user, array $lesson): void
    {
        $kind = $lesson['kind'] ?? null;
        $id = (int) ($lesson['id'] ?? 0);
        if ($id < 1 || empty($kind)) {
            return;
        }

        $column = match ($kind) {
            'file' => 'file_id',
            'session' => 'session_id',
            'text' => 'text_lesson_id',
            default => null,
        };

        if (!$column) {
            return;
        }

        $exists = \App\Models\CourseLearning::where('user_id', $user->id)
            ->where($column, $id)
            ->exists();

        if (!$exists) {
            \App\Models\CourseLearning::create([
                'user_id' => $user->id,
                $column => $id,
                'created_at' => time(),
            ]);
        }
    }

    private function localizedTitle($model): string
    {
        if (!$model) {
            return '';
        }

        foreach (['ar', app()->getLocale(), 'en'] as $locale) {
            try {
                $translated = $model->translate($locale);
                if (!empty($translated?->title)) {
                    return (string) $translated->title;
                }
            } catch (\Throwable $e) {
            }
        }

        return (string) ($model->title ?? '');
    }

    private function buildMediaPayload(string $kind, $model, $webinar): ?array
    {
        if ($kind === 'file') {
            $storage = $model->storage;
            $src = $model->file;
            $poster = method_exists($webinar, 'getImageCover') ? $webinar->getImageCover() : ($webinar->image_cover ?? null);

            if ($storage === 'youtube') {
                $embed = $this->youtubeEmbedUrl($src);

                return [
                    'mode' => 'youtube',
                    'src' => $embed,
                    'poster' => $poster,
                    'title' => $this->localizedTitle($model),
                    'downloadable' => false,
                ];
            }

            if ($storage === 'vimeo') {
                return [
                    'mode' => 'vimeo',
                    'src' => method_exists($model, 'getVimeoPath') ? $model->getVimeoPath() : $src,
                    'poster' => $poster,
                    'title' => $this->localizedTitle($model),
                    'downloadable' => false,
                ];
            }

            if (
                $model->isVideo()
                || (in_array($storage, ['upload', 'external_link'], true) && $model->file_type === 'video')
            ) {
                $playSrc = $src;
                if (!empty($src) && str_starts_with($src, '/') && !str_starts_with($src, '//')) {
                    $playSrc = url($src);
                }

                return [
                    'mode' => 'html5',
                    'src' => $playSrc,
                    'poster' => $poster,
                    'title' => $this->localizedTitle($model),
                    'downloadable' => (bool) $model->downloadable,
                ];
            }

            $downloadUrl = url('/course/' . $webinar->slug . '/file/' . $model->id . '/download');

            return [
                'mode' => 'download',
                'src' => $downloadUrl,
                'poster' => $poster,
                'title' => $this->localizedTitle($model),
                'downloadable' => true,
                'file_type' => $model->file_type,
                'volume' => $model->volume,
            ];
        }

        if ($kind === 'session') {
            return [
                'mode' => 'session',
                'src' => $model->link,
                'poster' => method_exists($webinar, 'getImageCover') ? $webinar->getImageCover() : null,
                'title' => $this->localizedTitle($model),
                'date' => !empty($model->date) ? date('Y/m/d H:i', (int) $model->date) : null,
                'duration' => $model->duration ?? null,
            ];
        }

        if ($kind === 'text') {
            return [
                'mode' => 'text',
                'src' => null,
                'title' => $this->localizedTitle($model),
                'content' => $model->content ?? ($model->description ?? ''),
            ];
        }

        return null;
    }

    private function youtubeEmbedUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?origin=' . urlencode(url('/'))
                . '&iv_load_policy=3&modestbranding=1&playsinline=1&showinfo=0&rel=0&enablejsapi=1';
        }

        return $url;
    }

    public function forum(Request $request, string $slug)
    {
        $resolved = $this->resolveCourse($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar] = $resolved;

        $threads = \App\Models\CourseForum::with(['user'])
            ->withCount(['answers'])
            ->where('webinar_id', $webinar->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $player = $this->buildPlayerData($webinar,$user,$request);

        return view('panel_v1.student.course-player.pages.forum', array_merge($player, [
            'pageTitle' => 'منتدى الدورة',
            'authUser' => $user,
            'webinar' => $webinar,
            'courseTitle' => $webinar->title,
            'forum' => [
                'banner_title' => 'منتدى دورة ' . $webinar->title,
                'posts' => $threads->map(function ($thread) {
                    $first = $thread->answers()->orderBy('id')->first();
                    return [
                        'initial' => mb_substr($thread->user->full_name ?? '?', 0, 1),
                        'author' => $thread->user->full_name ?? '',
                        'time' => date('Y/m/d H:i', (int) $thread->created_at),
                        'body' => $thread->title . ($first ? ' — ' . \Illuminate\Support\Str::limit(strip_tags($first->description ?? ''), 160) : ''),
                        'likes' => 0,
                        'comments' => $thread->answers_count,
                    ];
                })->all(),
            ],
        ]));
    }

    public function storeForumTopic(Request $request, string $slug)
    {
        $resolved = $this->resolveCourse($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar] = $resolved;

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $forum = new \App\Models\CourseForum();
        $forum->webinar_id = $webinar->id;
        $forum->user_id = $user->id;
        $forum->title = $request->input('title');
        $forum->description = $request->input('message');
        $forum->created_at = time();
        $forum->save();

        $answer = new \App\Models\CourseForumAnswer();
        $answer->forum_id = $forum->id;
        $answer->user_id = $user->id;
        $answer->description = $request->input('message');
        $answer->created_at = time();
        $answer->save();

        return redirect()
            ->route('panel.v1.student.course.forum', ['slug' => $slug])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم نشر سؤالك في المنتدى', 'type' => 'success']);
    }

    public function assignment(Request $request, string $slug)
    {
        $resolved = $this->resolveCourse($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar] = $resolved;

        $assignment = \App\Models\WebinarAssignment::where('webinar_id', $webinar->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        $existing = null;
        if ($assignment) {
            $existing = \App\Models\WebinarAssignmentHistory::where('assignment_id', $assignment->id)
                ->where('student_id', $user->id)
                ->orderBy('id', 'desc')
                ->first();
        }

        $player = $this->buildPlayerData($webinar,$user,$request);

        return view('panel_v1.student.course-player.pages.assignment', array_merge($player, [
            'pageTitle' => 'تقديم إجابة التكليف',
            'authUser' => $user,
            'webinar' => $webinar,
            'courseTitle' => $webinar->title,
            'assignmentPage' => [
                'title' => $assignment ? 'تكليف الدورة' : 'لا يوجد تكليف',
                'subtitle' => $webinar->title,
                'details_title' => 'تفاصيل التكليف',
                'details_body' => $assignment ? ('الدرجة العظمى: ' . ($assignment->grade ?? '—') . ' — درجة النجاح: ' . ($assignment->pass_grade ?? '—')) : 'لم يضف المدرب تكليفًا لهذه الدورة بعد.',
                'points_title' => 'تعليمات التسليم',
                'points' => ['اكتب إجابتك بوضوح', 'يمكن إرفاق ملف PDF أو DOCX'],
                'form_title' => 'إجابة التكليف والتسليم',
                'word_limit' => 2000,
            ],
            'assignment' => $assignment,
            'existingHistory' => $existing,
        ]));
    }

    public function submitAssignment(Request $request, string $slug)
    {
        $resolved = $this->resolveCourse($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar] = $resolved;

        $request->validate([
            'assignment_id' => 'required|integer',
            'answer' => 'required|string|max:20000',
            'upload' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $assignment = \App\Models\WebinarAssignment::where('id', $request->input('assignment_id'))
            ->where('webinar_id', $webinar->id)
            ->where('status', 'active')
            ->firstOrFail();

        $history = \App\Models\WebinarAssignmentHistory::firstOrNew([
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
        ]);
        $history->instructor_id = $webinar->teacher_id;
        $history->status = 'pending';
        $history->grade = null;
        $history->created_at = $history->created_at ?? time();
        $history->save();

        $message = new \App\Models\WebinarAssignmentHistoryMessage();
        $message->assignment_history_id = $history->id;
        $message->sender_id = $user->id;
        $message->message = $request->input('answer');
        $message->created_at = time();

        if ($request->hasFile('upload')) {
            $message->file_path = '/storage/' . $request->file('upload')->store('assignments', 'public');
            $message->file_title = $request->file('upload')->getClientOriginalName();
        }

        $message->save();

        return redirect()
            ->route('panel.v1.student.course.assignment', ['slug' => $slug])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إرسال إجابتك وهي بانتظار التقييم',
                'type' => 'success',
            ]);
    }

    public function quiz(Request $request, string $slug)
    {
        $resolved = $this->resolveQuiz($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar, $quiz] = $resolved;

        $questionsCount = QuizzesQuestion::where('quiz_id', $quiz->id)->count();

        $lastResult = QuizzesResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        $player = $this->buildPlayerData($webinar,$user,$request);

        return view('panel_v1.student.course-player.pages.quiz-start', array_merge($player, [
            'pageTitle' => 'بدء الاختبار',
            'authUser' => $user,
            'webinar' => $webinar,
            'courseTitle' => $webinar->title,
            'quiz' => [
                'title' => $quiz->title,
                'description' => '',
                'duration' => !empty($quiz->time) ? $quiz->time . ' دقيقة' : '—',
                'questions_count' => $questionsCount,
                'pass_score' => $quiz->pass_mark,
                'attempts' => $quiz->attempt ?? '—',
                'deadline' => '',
            ],
            'lastResult' => $lastResult ? [
                'grade' => $lastResult->user_grade,
                'status' => $lastResult->status,
            ] : null,
        ]));
    }

    public function quizTake(Request $request, string $slug)
    {
        $resolved = $this->resolveQuiz($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar, $quiz] = $resolved;

        if (!empty($quiz->attempt)) {
            $attemptsUsed = QuizzesResult::where('quiz_id', $quiz->id)->where('user_id', $user->id)->count();
            if ($attemptsUsed >= (int) $quiz->attempt) {
                return redirect()
                    ->route('panel.v1.student.course.quiz', ['slug' => $slug])
                    ->with('toast', ['title' => 'تنبيه', 'msg' => 'استنفدت عدد المحاولات المتاحة', 'type' => 'error']);
            }
        }

        $questions = QuizzesQuestion::with(['quizzesQuestionsAnswers'])
            ->where('quiz_id', $quiz->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($questions->isEmpty()) {
            return redirect()
                ->route('panel.v1.student.course.quiz', ['slug' => $slug])
                ->with('toast', ['title' => 'تنبيه', 'msg' => 'لا توجد أسئلة في هذا الاختبار بعد', 'type' => 'error']);
        }

        $index = max(1, min($questions->count(), (int) $request->get('q', 1)));
        $question = $questions[$index - 1];
        $saved = session()->get($this->quizSessionKey($quiz->id), []);

        $player = $this->buildPlayerData($webinar,$user,$request);

        return view('panel_v1.student.course-player.pages.quiz-take', array_merge($player, [
            'pageTitle' => 'الاختبار',
            'authUser' => $user,
            'webinar' => $webinar,
            'courseTitle' => $webinar->title,
            'quizTake' => [
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
                'quiz_title' => $quiz->title,
                'current' => $index,
                'total' => $questions->count(),
                'question_title' => $question->title,
                'instruction' => $question->type === 'descriptive' ? 'اكتب إجابتك في المساحة المخصصة' : 'اختر الإجابة الصحيحة',
                'is_descriptive' => $question->type === 'descriptive',
                'is_last' => $index >= $questions->count(),
                'saved_option' => $saved[$question->id]['answer'] ?? null,
                'saved_text' => $saved[$question->id]['text'] ?? null,
                'options' => $question->quizzesQuestionsAnswers->map(function ($answer) {
                    return ['id' => $answer->id, 'text' => $answer->title];
                })->all(),
            ],
        ]));
    }

    public function quizAnswer(Request $request, string $slug)
    {
        $resolved = $this->resolveQuiz($request, $slug);

        if ($resolved instanceof \Illuminate\Http\RedirectResponse) {
            return $resolved;
        }

        [$user, $webinar, $quiz] = $resolved;

        $request->validate([
            'quiz_id' => 'required|integer',
            'question_id' => 'required|integer',
            'q' => 'required|integer|min:1',
        ]);

        if ((int) $request->input('quiz_id') !== (int) $quiz->id) {
            abort(422);
        }

        $question = QuizzesQuestion::where('id', $request->input('question_id'))
            ->where('quiz_id', $quiz->id)
            ->firstOrFail();

        $questionsCount = QuizzesQuestion::where('quiz_id', $quiz->id)->count();
        $current = max(1, min($questionsCount, (int) $request->input('q')));

        $answers = session()->get($this->quizSessionKey($quiz->id), []);

        if ($question->type === 'descriptive') {
            $request->validate(['answer_text' => 'required|string|max:5000']);
            $answers[$question->id] = ['text' => $request->input('answer_text')];
        } else {
            $request->validate(['quiz_option' => 'required|integer']);
            $answer = QuizzesQuestionsAnswer::where('id', $request->input('quiz_option'))
                ->where('question_id', $question->id)
                ->firstOrFail();
            $answers[$question->id] = ['answer' => $answer->id];
        }

        session()->put($this->quizSessionKey($quiz->id), $answers);

        if ($current >= $questionsCount) {
            return $this->finishQuiz($user, $quiz, $slug);
        }

        return redirect()->route('panel.v1.student.course.quiz.take', ['slug' => $slug, 'q' => $current + 1]);
    }

    private function finishQuiz($user, $quiz, string $slug)
    {
        $answers = session()->get($this->quizSessionKey($quiz->id), []);
        $questions = QuizzesQuestion::where('quiz_id', $quiz->id)->get();

        $results = [];
        $totalMark = 0;
        $status = $questions->isEmpty() ? 'failed' : 'passed';

        foreach ($questions as $question) {
            $saved = $answers[$question->id] ?? null;
            $entry = ['grade' => (int) $question->grade, 'status' => false];

            if (!empty($saved['answer'])) {
                $answer = QuizzesQuestionsAnswer::where('id', $saved['answer'])
                    ->where('question_id', $question->id)
                    ->first();

                $entry['answer'] = $saved['answer'];

                if ($answer && $answer->correct) {
                    $entry['status'] = true;
                    $totalMark += (int) $question->grade;
                } elseif ($question->type === 'multiple' && !empty($question->negative_grade)) {
                    $totalMark -= (int) $question->negative_grade;
                }
            } elseif (!empty($saved['text'])) {
                $entry['text'] = $saved['text'];
            }

            if ($question->type === 'descriptive') {
                $status = 'waiting';
            }

            $results[$question->id] = $entry;
        }

        if ($status !== 'waiting') {
            $status = $totalMark >= (int) $quiz->pass_mark ? 'passed' : 'failed';
        }

        QuizzesResult::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'results' => json_encode($results),
            'user_grade' => max(0, $totalMark),
            'status' => $status,
            'created_at' => time(),
        ]);

        session()->forget($this->quizSessionKey($quiz->id));

        return redirect()
            ->route('panel.v1.student.course.quiz', ['slug' => $slug])
            ->with('toast', [
                'title' => $status === 'passed' ? 'أحسنت!' : 'تم',
                'msg' => $status === 'waiting' ? 'تم إرسال إجاباتك وهي بانتظار التصحيح' : ('درجتك: ' . max(0, $totalMark)),
                'type' => $status === 'failed' ? 'error' : 'success',
            ]);
    }

    private function quizSessionKey(int $quizId): string
    {
        return 'v1_quiz_answers_' . $quizId;
    }

    /**
     * @return array|\Illuminate\Http\RedirectResponse
     */
    private function resolveQuiz(Request $request, string $slug)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinar = \App\Models\Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($webinar)) {
            abort(404);
        }

        if (!$this->canAccess($user, $webinar)) {
            return redirect()->route('landing.v1.course-details', ['slug' => $webinar->slug]);
        }

        $quiz = Quiz::where('webinar_id', $webinar->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if (empty($quiz)) {
            return redirect()
                ->route('panel.v1.student.course.watch', ['slug' => $slug])
                ->with('toast', ['title' => 'تنبيه', 'msg' => 'لا يوجد اختبار نشط لهذه الدورة', 'type' => 'error']);
        }

        return [$user, $webinar, $quiz];
    }

    private function resolveCourse(Request $request, string $slug)
    {
        $user = $this->resolveStudent($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinar = \App\Models\Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($webinar)) {
            abort(404);
        }

        if (!$this->canAccess($user, $webinar)) {
            return redirect()->route('landing.v1.course-details', ['slug' => $webinar->slug]);
        }

        return [$user, $webinar];
    }

    private function render(Request $request, string $slug, string $view, string $pageTitle)
    {
        $user = $this->resolveStudent($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $webinar = \App\Models\Webinar::where('slug', $slug)->where('status','active')->first();
        if (empty($webinar)) { abort(404); }
        if (!$this->canAccess($user,$webinar)) {
            return redirect()->route('landing.v1.course-details',['slug'=>$webinar->slug]);
        }
        $player = $this->buildPlayerData($webinar,$user,$request);
        return view($view, array_merge($player, [
            'pageTitle'=>$pageTitle,
            'authUser'=>$user,
            'webinar'=>$webinar,
            'courseTitle'=>$webinar->title,
        ]));
    }

    private function canAccess($user, $webinar): bool
    {
        if (empty($webinar->price)) {
            return true;
        }

        return \App\Models\Sale::where('buyer_id', $user->id)
            ->where('webinar_id', $webinar->id)
            ->exists();
    }

    /**
     * @return \App\User|\Illuminate\Http\RedirectResponse
     */
    private function resolveStudent(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->isUser()) {
            if ($user->isTeacher()) {
                return redirect()->route('panel.v1.instructor.home');
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
