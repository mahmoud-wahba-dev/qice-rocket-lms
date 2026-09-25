<?php

namespace App\Http\Controllers\PanelV1\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Shared 5-step course create/edit wizard for Instructor + Admin panel_v1.
 */
trait CourseWizardTrait
{
    protected function courseWizardFieldNames(): array
    {
        return [
            'title' => 'عنوان الدورة',
            'category_id' => 'التصنيف الرئيسي',
            'course_type' => 'نوع الدورة',
            'seo_description' => 'الوصف المختصر',
            'description' => 'الوصف التفصيلي',
            'video_demo_link' => 'رابط الفيديو الترويجي',
            'video_demo_file' => 'ملف الفيديو الترويجي',
            'image_thumbnail' => 'الصورة المصغرة',
            'image_cover' => 'غلاف الدورة',
            'tags' => 'الوسوم',
            'locale' => 'لغة الدورة',
            'downloadable' => 'السماح بتحميل الملفات',
            'partner_instructor' => 'مدرب مشارك',
            'partners' => 'المدرب المشارك',
            'teacher_id' => 'المدرب الرئيسي',
            'quiz_id' => 'الاختبار',
            'certificate' => 'الشهادة',
            'price' => 'السعر',
            'capacity' => 'سعة الطلاب',
            'access_duration' => 'مدة الوصول',
            'access_days' => 'عدد أيام الوصول',
            'confirm_rights' => 'تأكيد حقوق الملكية',
            'confirm_terms' => 'الموافقة على الشروط',
            'draft_id' => 'المسودة',
            'chapter_id' => 'الوحدة',
            'topic' => 'عنوان الجلسة',
            'date' => 'تاريخ الجلسة',
            'duration' => 'مدة الجلسة',
            'upload' => 'الملف',
            'summary' => 'ملخص الدرس',
        ];
    }

    protected function courseWizardMessages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب',
            'required_if' => 'حقل :attribute مطلوب',
            'accepted' => 'يجب الموافقة على :attribute',
            'in' => 'قيمة :attribute غير صحيحة',
            'exists' => ':attribute غير موجود',
            'integer' => 'حقل :attribute يجب أن يكون رقمًا',
            'numeric' => 'حقل :attribute يجب أن يكون رقمًا',
            'min.numeric' => 'حقل :attribute يجب ألا يقل عن :min',
            'min.integer' => 'حقل :attribute يجب ألا يقل عن :min',
            'max.string' => 'حقل :attribute يجب ألا يتجاوز :max حرفًا',
            'max.file' => 'حجم :attribute يجب ألا يتجاوز :max كيلوبايت',
            'image' => 'حقل :attribute يجب أن يكون صورة',
            'url' => 'حقل :attribute يجب أن يكون رابطًا صالحًا',
            'file' => 'حقل :attribute يجب أن يكون ملفًا',
            'mimetypes' => 'نوع ملف :attribute غير مدعوم',
            'boolean' => 'قيمة :attribute غير صحيحة',
            'date' => 'حقل :attribute يجب أن يكون تاريخًا صالحًا',
            'string' => 'حقل :attribute يجب أن يكون نصًا',
        ];
    }

    protected function courseWizardStepsMeta(): array
    {
        return [
            1 => ['label' => 'البيانات الأساسية', 'title' => 'البيانات الأساسية والتصنيف', 'next' => 'التالي: المنهج والمحتوى', 'progress' => 20],
            2 => ['label' => 'المنهج والمحتوى', 'title' => 'المنهج والمحتوى التعليمي', 'next' => 'التالي: الاختبارات والشهادات', 'prev' => 'السابق', 'progress' => 40],
            3 => ['label' => 'الاختبارات والشهادات', 'title' => 'الاختبارات والشهادات', 'next' => 'التالي: التسعير والسعة', 'prev' => 'السابق', 'progress' => 60],
            4 => ['label' => 'التسعير والسعة', 'title' => 'التسعير والسعة', 'next' => 'التالي: النشر والمراجعة', 'prev' => 'السابق', 'progress' => 80],
            5 => ['label' => 'النشر والمراجعة', 'title' => 'النشر والمراجعة', 'next' => 'إرسال للمراجعة', 'prev' => 'السابق', 'progress' => 100],
        ];
    }

    /**
     * Load a webinar for the wizard. Admins can open any course; teachers only their own.
     * Status is not restricted (draft / pending / active / inactive).
     */
    protected function wizardWebinarOrFail($user, $draftId)
    {
        $q = \App\Models\Webinar::where('id', $draftId);
        if (!$user->isAdmin()) {
            $q->where(function ($query) use ($user) {
                $query->where('teacher_id', $user->id)
                    ->orWhere('creator_id', $user->id)
                    ->orWhereHas('webinarPartnerTeacher', function ($partnerQuery) use ($user) {
                        $partnerQuery->where('teacher_id', $user->id);
                    });
            });
        }

        return $q->firstOrFail();
    }

    /**
     * Active teachers available as co-instructors (excludes course owner / current teacher).
     */
    protected function availablePartnerInstructors($user, $draft = null): array
    {
        $excludeIds = array_values(array_unique(array_filter([
            (int) optional($draft)->teacher_id,
            (int) optional($draft)->creator_id,
            $user->isAdmin() ? null : (int) $user->id,
        ])));

        return \App\User::query()
            ->where('role_name', \App\Models\Role::$teacher)
            ->where('status', 'active')
            ->when(!empty($excludeIds), function ($query) use ($excludeIds) {
                $query->whereNotIn('id', $excludeIds);
            })
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email'])
            ->map(function ($teacher) {
                return [
                    'id' => (int) $teacher->id,
                    'name' => (string) $teacher->full_name,
                    'email' => (string) ($teacher->email ?? ''),
                ];
            })
            ->all();
    }

    /**
     * Sync webinar_partner_teacher rows from the wizard request.
     */
    protected function syncWizardPartnerTeachers(Request $request, $draft, $user): void
    {
        if (empty($draft) || empty($draft->id)) {
            return;
        }

        if (!$request->boolean('partner_instructor')) {
            \App\Models\WebinarPartnerTeacher::where('webinar_id', $draft->id)->delete();

            return;
        }

        $ownerIds = array_values(array_unique(array_filter([
            (int) $draft->teacher_id,
            (int) $draft->creator_id,
        ])));

        $requestedIds = collect($request->input('partners', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->reject(fn ($id) => in_array($id, $ownerIds, true))
            ->values()
            ->all();

        $validIds = empty($requestedIds)
            ? []
            : \App\User::query()
                ->where('role_name', \App\Models\Role::$teacher)
                ->whereIn('id', $requestedIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        \App\Models\WebinarPartnerTeacher::where('webinar_id', $draft->id)->delete();

        foreach ($validIds as $partnerId) {
            \App\Models\WebinarPartnerTeacher::create([
                'webinar_id' => $draft->id,
                'teacher_id' => $partnerId,
            ]);
        }
    }

    protected function curriculumUnitsForWizard($draft, $user = null): array
    {
        if (empty($draft)) {
            return [];
        }

        $routes = $this->curriculumRouteNames($user);
        $translatedTitle = function ($model) {
            if (!$model) {
                return '';
            }
            $tr = $model->translate('ar') ?: $model->translate(app()->getLocale()) ?: $model->translations->first();

            return $tr->title ?? ($model->title ?? '');
        };

        return \App\Models\WebinarChapter::with(['sessions.translations', 'files.translations', 'textLessons.translations', 'translations'])
            ->where('webinar_id', $draft->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($chapter) use ($translatedTitle, $routes) {
                $lessons = [];

                foreach ($chapter->sessions as $session) {
                    $lessons[] = [
                        'kind' => 'session',
                        'id' => $session->id,
                        'title' => $translatedTitle($session),
                        'duration' => ($session->duration ?? 0) . ' دقيقة',
                        'delete_url' => route($routes['sessions.delete'], ['sessionId' => $session->id]),
                    ];
                }

                foreach ($chapter->files as $file) {
                    $meta = $this->curriculumFilePreviewMeta($file->file ?? null, $file->file_type ?? null);
                    $lessons[] = array_merge([
                        'kind' => 'file',
                        'id' => $file->id,
                        'title' => $translatedTitle($file),
                        'duration' => 'ملف',
                        'delete_url' => route($routes['files.delete'], ['fileId' => $file->id]),
                    ], $meta);
                }

                foreach ($chapter->textLessons as $text) {
                    $lessons[] = [
                        'kind' => 'text',
                        'id' => $text->id,
                        'title' => $translatedTitle($text),
                        'duration' => 'نصي',
                        'delete_url' => route($routes['texts.delete'], ['textId' => $text->id]),
                    ];
                }

                return [
                    'id' => $chapter->id,
                    'title' => $translatedTitle($chapter),
                    'lessons' => $lessons,
                    'delete_url' => route($routes['chapters.delete'], ['chapterId' => $chapter->id]),
                    'session_store_url' => route($routes['sessions.store']),
                    'file_store_url' => route($routes['files.store']),
                    'text_store_url' => route($routes['texts.store']),
                ];
            })->all();
    }

    /**
     * Curriculum AJAX route names — admin must not hit /v1/instructor/* (panel middleware rejects admins).
     */
    protected function curriculumRouteNames($user = null): array
    {
        $admin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        if ($admin) {
            return [
                'chapters.store' => 'panel.v1.admin.education.curriculum.chapters.store',
                'chapters.delete' => 'panel.v1.admin.education.curriculum.chapters.delete',
                'sessions.store' => 'panel.v1.admin.education.curriculum.sessions.store',
                'sessions.delete' => 'panel.v1.admin.education.curriculum.sessions.delete',
                'files.store' => 'panel.v1.admin.education.curriculum.files.store',
                'files.delete' => 'panel.v1.admin.education.curriculum.files.delete',
                'texts.store' => 'panel.v1.admin.education.curriculum.texts.store',
                'texts.delete' => 'panel.v1.admin.education.curriculum.texts.delete',
            ];
        }

        return [
            'chapters.store' => 'panel.v1.instructor.curriculum.chapters.store',
            'chapters.delete' => 'panel.v1.instructor.curriculum.chapters.delete',
            'sessions.store' => 'panel.v1.instructor.curriculum.sessions.store',
            'sessions.delete' => 'panel.v1.instructor.curriculum.sessions.delete',
            'files.store' => 'panel.v1.instructor.curriculum.files.store',
            'files.delete' => 'panel.v1.instructor.curriculum.files.delete',
            'texts.store' => 'panel.v1.instructor.curriculum.texts.store',
            'texts.delete' => 'panel.v1.instructor.curriculum.texts.delete',
        ];
    }

    protected function curriculumFilePublicUrl(?string $path): ?string
    {
        return function_exists('panelV1PublicUrl') ? panelV1PublicUrl($path) : (
            empty($path) ? null : url('/' . ltrim($path, '/'))
        );
    }

    protected function curriculumFilePreviewMeta(?string $path, ?string $fileType = null): array
    {
        $url = $this->curriculumFilePublicUrl($path);
        $kind = 'file';
        if (function_exists('panelV1FileKind')) {
            $kind = panelV1FileKind($path, $fileType);
        } elseif (!empty($path)) {
            if (preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', $path)) {
                $kind = 'image';
            } elseif (preg_match('/\.pdf$/i', $path)) {
                $kind = 'pdf';
            } elseif (preg_match('/\.(mp4|webm|mov|m4v)$/i', $path)) {
                $kind = 'video';
            }
        }

        return [
            'view_url' => $url,
            'preview_kind' => $kind,
            'preview_url' => in_array($kind, ['image', 'video', 'pdf'], true) ? $url : null,
        ];
    }

    protected function buildCourseWizardViewData(Request $request, $draft, int $step, $user): array
    {
        $categories = \App\Models\Category::whereNull('parent_id')
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                return ['id' => $category->id, 'title' => $category->title];
            })->all();

        $quizQuery = \App\Models\Quiz::query()->orderBy('id', 'desc');
        if (!$user->isAdmin()) {
            $quizQuery->where('creator_id', optional($user)->id);
        }
        $teacherQuizzes = $quizQuery->limit(200)->get()->map(function ($quiz) {
            return ['id' => $quiz->id, 'title' => $quiz->title, 'webinar_id' => $quiz->webinar_id];
        })->all();

        $typeReverse = ['course' => 'recorded', 'webinar' => 'live', 'text_lesson' => 'text'];
        $tagTitles = $draft ? $draft->tags->pluck('title')->filter()->values()->all() : [];
        $partnerIds = $draft
            ? $draft->webinarPartnerTeacher()->pluck('teacher_id')->map(fn ($id) => (int) $id)->values()->all()
            : [];
        $draftLocaleTitle = null;
        $draftLocaleSeo = null;
        $draftLocaleDescription = null;
        if ($draft) {
            $tr = $draft->translate('ar') ?: $draft->translate(app()->getLocale()) ?: $draft->translations->first();
            $draftLocaleTitle = $tr->title ?? null;
            $draftLocaleSeo = $tr->seo_description ?? null;
            $draftLocaleDescription = $tr->description ?? null;
        }

        return [
            'wizardSteps' => $this->courseWizardStepsMeta(),
            'courseTypes' => [
                ['key' => 'recorded', 'label' => 'دورة فيديو مسجلة', 'hint' => 'محتوى مسجل يشاهده الطالب في أي وقت'],
                ['key' => 'live', 'label' => 'دورة تفاعلية مباشرة', 'hint' => 'جلسات مباشرة عبر Zoom أو Teams'],
                ['key' => 'text', 'label' => 'دورة نصية', 'hint' => 'محتوى مقروء ومواد مكتوبة'],
            ],
            'wizardStep' => $step,
            'draftId' => $draft->id ?? null,
            'draftTitle' => $draftLocaleTitle ?: 'دورة تدريبية بدون عنوان',
            'draft' => $draft ? [
                'title' => $draftLocaleTitle,
                'category_id' => $draft->category_id,
                'teacher_id' => $draft->teacher_id,
                'course_type' => $typeReverse[$draft->type] ?? 'recorded',
                'locale' => 'ar',
                'seo_description' => $draftLocaleSeo,
                'description' => $draftLocaleDescription,
                'video_demo_link' => $draft->video_demo_source === 'external_link' ? $draft->video_demo : null,
                'tags' => implode(',', $tagTitles),
                'downloadable' => (bool) ($draft->downloadable ?? false),
                'partner_instructor' => (bool) ($draft->partner_instructor ?? false),
                'partners' => $partnerIds,
                'access_days' => $draft->access_days,
                'thumbnail' => $draft->thumbnail,
                'image_cover' => $draft->image_cover,
            ] : [],
            'tags' => $tagTitles,
            'availableInstructors' => $this->availablePartnerInstructors($user, $draft),
            'courseTeachers' => $user->isAdmin()
                ? \App\User::query()
                    ->where('role_name', \App\Models\Role::$teacher)
                    ->where('status', 'active')
                    ->orderBy('full_name')
                    ->get(['id', 'full_name'])
                    ->map(fn ($t) => ['id' => (int) $t->id, 'name' => (string) $t->full_name])
                    ->all()
                : [],
            'isAdminWizard' => (bool) $user->isAdmin(),
            'categories' => !empty($categories) ? $categories : [],
            'languages' => [
                ['key' => 'ar', 'label' => 'العربية'],
                ['key' => 'en', 'label' => 'English'],
            ],
            'curriculumUnits' => $this->curriculumUnitsForWizard($draft, $user),
            'curriculumChapterStoreUrl' => route($this->curriculumRouteNames($user)['chapters.store']),
            'teacherQuizzes' => $teacherQuizzes,
            'draftPrice' => $draft->price ?? null,
            'draftCapacity' => $draft->capacity ?? null,
            'draftCertificate' => (bool) ($draft->certificate ?? false),
            'draftAccessDays' => $draft->access_days ?? null,
        ];
    }

    /**
     * Persist one wizard step. Returns [draft, isDone, nextStep, draftTitle, doneMessage].
     *
     * @param  array{allowCreate?: bool, redirectCourses?: string, redirectCreate?: string}  $opts
     */
    protected function persistCourseWizardStep(Request $request, $user, $draft, array $opts = []): array
    {
        $allowCreate = $opts['allowCreate'] ?? true;
        $step = max(1, min(5, (int) $request->input('wizard_step', 1)));
        $attrs = $this->courseWizardFieldNames();
        $soft = $request->boolean('autosave')
            || $request->boolean('save_only')
            || $request->input('go_next') === 'stay'
            || ($request->filled('go_next') && is_numeric($request->input('go_next')) && (int) $request->input('go_next') < $step);

        if ($step === 1) {
            $partnerOn = $request->boolean('partner_instructor');
            $rules = [
                'title' => ($soft ? 'nullable' : 'required') . '|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'course_type' => 'nullable|in:recorded,live,text',
                'seo_description' => ($soft ? 'nullable' : 'required') . '|string|max:160',
                'description' => 'nullable|string',
                'video_demo_link' => 'nullable|url|max:2000',
                'video_demo_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
                'image_thumbnail' => 'nullable|image|max:5120',
                'image_cover' => 'nullable|image|max:5120',
                'tags' => 'nullable|string|max:1000',
                'locale' => 'nullable|in:ar,en',
                'downloadable' => 'nullable|boolean',
                'partner_instructor' => 'nullable|boolean',
                'partners' => ($soft || !$partnerOn ? 'nullable' : 'required') . '|array' . ($soft || !$partnerOn ? '' : '|min:1'),
                'partners.*' => 'integer|exists:users,id',
            ];
            if ($user->isAdmin()) {
                $rules['teacher_id'] = ($soft ? 'nullable' : 'required') . '|exists:users,id';
            }
            $request->validate($rules, $this->courseWizardMessages(), $attrs);

            $typeMap = ['recorded' => 'course', 'live' => 'webinar', 'text' => 'text_lesson'];
            $title = trim((string) $request->input('title', ''));
            if ($title === '') {
                $title = 'دورة تدريبية بدون عنوان';
            }

            if (empty($draft)) {
                if (!$allowCreate) {
                    abort(404);
                }
                $draft = new \App\Models\Webinar();
                $draft->teacher_id = $user->id;
                $draft->creator_id = $user->id;
                $draft->status = 'is_draft';
                $slugBase = Str::slug($title);
                if ($slugBase === '') {
                    $slugBase = 'course';
                }
                $draft->slug = $slugBase . '-' . time();
                $draft->created_at = time();
            }

            if ($user->isAdmin() && $request->filled('teacher_id')) {
                $teacherId = (int) $request->input('teacher_id');
                $teacherOk = \App\User::query()
                    ->where('id', $teacherId)
                    ->where('role_name', \App\Models\Role::$teacher)
                    ->exists();
                if ($teacherOk) {
                    $draft->teacher_id = $teacherId;
                }
            }

            $draft->type = $typeMap[$request->input('course_type', 'recorded')] ?? 'course';
            $draft->category_id = $request->input('category_id') ?: null;
            $draft->downloadable = $request->boolean('downloadable');
            $draft->partner_instructor = $partnerOn;
            $draft->updated_at = time();

            if ($request->hasFile('image_thumbnail')) {
                $draft->thumbnail = '/storage/' . $request->file('image_thumbnail')->store('webinars', 'public');
            }

            if ($request->hasFile('image_cover')) {
                $draft->image_cover = '/storage/' . $request->file('image_cover')->store('webinars', 'public');
            }

            if ($request->hasFile('video_demo_file')) {
                $draft->video_demo = '/storage/' . $request->file('video_demo_file')->store('webinars/videos', 'public');
                $draft->video_demo_source = 'upload';
            } elseif ($request->filled('video_demo_link')) {
                $draft->video_demo = $request->input('video_demo_link');
                $draft->video_demo_source = 'external_link';
            }

            $draft->save();

            $locale = $request->input('locale', 'ar') ?: 'ar';
            $locales = array_values(array_unique(array_filter([$locale, 'ar', app()->getLocale()])));
            foreach ($locales as $loc) {
                $translation = $draft->translateOrNew($loc);
                $translation->webinar_id = $draft->id;
                $translation->locale = $loc;
                $translation->title = $title;
                $translation->seo_description = $request->input('seo_description');
                $translation->description = $request->input('description');
                $translation->save();
            }

            $tags = array_filter(array_map('trim', explode(',', (string) $request->input('tags', ''))));
            \App\Models\Tag::where('webinar_id', $draft->id)->delete();
            foreach (array_slice(array_unique($tags), 0, 10) as $tagTitle) {
                \App\Models\Tag::create(['title' => mb_substr($tagTitle, 0, 64), 'webinar_id' => $draft->id]);
            }

            $this->syncWizardPartnerTeachers($request, $draft, $user);
        }

        if ($step === 2 && !empty($draft)) {
            $draft->updated_at = time();
            $draft->save();
        }

        if (!empty($draft) && $step === 3) {
            $request->validate([
                'quiz_id' => 'nullable|exists:quizzes,id',
                'certificate' => 'nullable|boolean',
            ], $this->courseWizardMessages(), $attrs);

            if ($request->filled('quiz_id')) {
                $quizQ = \App\Models\Quiz::where('id', $request->input('quiz_id'));
                if (!$user->isAdmin()) {
                    $quizQ->where('creator_id', $user->id);
                }
                $quiz = $quizQ->firstOrFail();
                $quiz->webinar_id = $draft->id;
                $quiz->save();
            }

            $draft->certificate = $request->boolean('certificate');
            $draft->updated_at = time();
            $draft->save();
        }

        if (!empty($draft) && $step === 4) {
            $request->validate([
                'price' => 'nullable|integer|min:0',
                'capacity' => 'nullable|integer|min:1',
                'access_duration' => 'nullable|in:lifetime,limited',
                'access_days' => 'nullable|integer|min:1|max:3650',
            ], $this->courseWizardMessages(), $attrs);

            if ($request->filled('price') && (int) $request->input('price') > 0) {
                $draft->price = (int) $request->input('price');
            } else {
                $draft->price = null;
            }

            $draft->capacity = $request->input('capacity') ?: null;

            if ($request->input('access_duration') === 'limited') {
                $draft->access_days = (int) ($request->input('access_days') ?: 30);
            } else {
                $draft->access_days = null;
            }

            $draft->updated_at = time();
            $draft->save();
        }

        $goNext = $request->input('go_next');
        $isDone = !empty($draft) && $step === 5 && $goNext === 'done';
        $doneMessage = 'تم حفظ التعديلات';

        if ($isDone) {
            $request->validate([
                'confirm_rights' => 'accepted',
                'confirm_terms' => 'accepted',
            ], array_merge($this->courseWizardMessages(), [
                'confirm_rights.accepted' => 'يجب تأكيد حقوق الملكية الفكرية قبل الإرسال.',
                'confirm_terms.accepted' => 'يجب الموافقة على شروط المدربين قبل الإرسال.',
            ]), $attrs);

            // Keep live courses active; otherwise submit for review
            if (($draft->status ?? '') !== 'active') {
                $draft->status = 'pending';
                $doneMessage = 'تم إرسال الدورة للمراجعة';
            }
            $draft->updated_at = time();
            $draft->save();
        }

        $nextStep = $step;
        if ($goNext === 'done') {
            $nextStep = 5;
        } elseif ($goNext === 'stay' || $request->boolean('autosave') || $request->boolean('save_only')) {
            $nextStep = $step;
        } elseif (is_numeric($goNext)) {
            $nextStep = max(1, min(5, (int) $goNext));
        }

        $draftTitle = null;
        if (!empty($draft)) {
            $tr = $draft->translate('ar') ?: $draft->translate(app()->getLocale()) ?: $draft->translations()->first();
            $draftTitle = $tr->title ?? null;
        }

        return [
            'draft' => $draft,
            'isDone' => $isDone,
            'nextStep' => $nextStep,
            'draftTitle' => $draftTitle,
            'doneMessage' => $doneMessage,
            'step' => $step,
        ];
    }
}
