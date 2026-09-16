<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationStatus;
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

    public function students(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinarIds = $this->teacherWebinars($user)->pluck('id')->all();

        $sales = !empty($webinarIds)
            ? Sale::with(['buyer', 'webinar'])
                ->whereIn('webinar_id', $webinarIds)
                ->where('seller_id', $user->id)
                ->whereNull('refund_at')
                ->orderByDesc('id')
                ->get()
            : collect();

        $studentsByBuyer = [];
        foreach ($sales as $sale) {
            $buyerId = (int) $sale->buyer_id;
            if ($buyerId < 1 || empty($sale->buyer)) {
                continue;
            }

            if (!isset($studentsByBuyer[$buyerId])) {
                $studentsByBuyer[$buyerId] = [
                    'id' => $buyerId,
                    'name' => $sale->buyer->full_name ?? '—',
                    'email' => $sale->buyer->email ?? '—',
                    'courses' => [],
                    'courses_count' => 0,
                    'last_purchase' => null,
                    'spent' => 0,
                ];
            }

            $courseTitle = $sale->webinar->title ?? 'دورة';
            if (!in_array($courseTitle, $studentsByBuyer[$buyerId]['courses'], true)) {
                $studentsByBuyer[$buyerId]['courses'][] = $courseTitle;
            }
            $studentsByBuyer[$buyerId]['courses_count'] = count($studentsByBuyer[$buyerId]['courses']);
            $studentsByBuyer[$buyerId]['spent'] += (float) ($sale->total_amount ?? 0);

            $purchaseAt = (int) ($sale->created_at ?? 0);
            if (empty($studentsByBuyer[$buyerId]['last_purchase']) || $purchaseAt > (int) $studentsByBuyer[$buyerId]['last_purchase_ts']) {
                $studentsByBuyer[$buyerId]['last_purchase_ts'] = $purchaseAt;
                $studentsByBuyer[$buyerId]['last_purchase'] = $purchaseAt > 0 ? date('Y/m/d', $purchaseAt) : '—';
            }

            if (!empty($sale->webinar?->slug) && empty($studentsByBuyer[$buyerId]['course_slug'])) {
                $studentsByBuyer[$buyerId]['course_slug'] = $sale->webinar->slug;
            }
        }

        $students = collect($studentsByBuyer)
            ->map(function ($row) {
                $row['spent_label'] = handlePrice($row['spent']);
                $row['courses_label'] = implode(' · ', array_slice($row['courses'], 0, 3));
                unset($row['courses'], $row['last_purchase_ts']);
                return $row;
            })
            ->sortBy('name')
            ->values()
            ->all();

        return $this->render($request, 'panel_v1.instructor.pages.students', 'قائمة الطلاب', [
            'students' => $students,
            'studentStats' => [
                ['label' => 'إجمالي الطلاب', 'value' => (string) count($students)],
                ['label' => 'إجمالي التسجيلات', 'value' => (string) $sales->count()],
                ['label' => 'الدورات المرتبطة', 'value' => (string) collect($students)->sum('courses_count')],
            ],
        ]);
    }

    public function courses(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $courseCards = $this->courseCards($user);

        return $this->render($request, 'panel_v1.instructor.pages.courses', 'إدارة الدورات', [
            'courseCards' => $courseCards,
            'liveCards' => array_values(array_filter(
                $courseCards,
                fn ($card) => ($card['type_key'] ?? '') === Webinar::$webinar
                    && ($card['status'] ?? '') !== Webinar::$isDraft
            )),
            'recordedCards' => array_values(array_filter(
                $courseCards,
                fn ($card) => in_array($card['type_key'] ?? '', [Webinar::$course, Webinar::$textLesson], true)
                    && ($card['status'] ?? '') !== Webinar::$isDraft
            )),
            'draftCards' => array_values(array_filter(
                $courseCards,
                fn ($card) => ($card['status'] ?? '') === Webinar::$isDraft
            )),
        ]);
    }

    public function deleteCourse(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinar = Webinar::query()
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->firstOrFail();

        $webinar->update([
            'status' => Webinar::$inactive,
            'updated_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.courses')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حذف الدورة بنجاح', 'type' => 'success']);
    }

    public function createCourse(Request $request, ?int $step = 1)
    {
        $user = $request->user();
        if ($request->filled('step')) {
            $step = (int) $request->input('step');
        }
        $step = max(1, min(5, $step ?? 1));

        $draft = null;
        if ($request->filled('draft')) {
            $draft = \App\Models\Webinar::with(['tags', 'translations'])
                ->where('id', $request->input('draft'))
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

        $typeReverse = ['course' => 'recorded', 'webinar' => 'live', 'text_lesson' => 'text'];
        $tagTitles = $draft ? $draft->tags->pluck('title')->filter()->values()->all() : [];
        $draftLocaleTitle = null;
        $draftLocaleSeo = null;
        $draftLocaleDescription = null;
        if ($draft) {
            $tr = $draft->translate('ar') ?: $draft->translate(app()->getLocale()) ?: $draft->translations->first();
            $draftLocaleTitle = $tr->title ?? null;
            $draftLocaleSeo = $tr->seo_description ?? null;
            $draftLocaleDescription = $tr->description ?? null;
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.create-course',
            'إنشاء دورة جديدة',
            [
                'wizardSteps' => [
                    1=>['label'=>'البيانات الأساسية','title'=>'البيانات الأساسية والتصنيف','next'=>'التالي: المنهج والمحتوى','progress'=>20],
                    2=>['label'=>'المنهج والمحتوى','title'=>'المنهج والمحتوى التعليمي','next'=>'التالي: الاختبارات والشهادات','prev'=>'السابق','progress'=>40],
                    3=>['label'=>'الاختبارات والشهادات','title'=>'الاختبارات والشهادات','next'=>'التالي: التسعير والسعة','prev'=>'السابق','progress'=>60],
                    4=>['label'=>'التسعير والسعة','title'=>'التسعير والسعة','next'=>'التالي: النشر والمراجعة','prev'=>'السابق','progress'=>80],
                    5=>['label'=>'النشر والمراجعة','title'=>'النشر والمراجعة','next'=>'إرسال للمراجعة','prev'=>'السابق','progress'=>100],
                ],
                'courseTypes' => [
                    ['key'=>'recorded','label'=>'دورة فيديو مسجلة','hint'=>'محتوى مسجل يشاهده الطالب في أي وقت'],
                    ['key'=>'live','label'=>'دورة تفاعلية مباشرة','hint'=>'جلسات مباشرة عبر Zoom أو Teams'],
                    ['key'=>'text','label'=>'دورة نصية','hint'=>'محتوى مقروء ومواد مكتوبة'],
                ],
                'wizardStep' => $step,
                'draftId' => $draft->id ?? null,
                'draftTitle' => $draftLocaleTitle ?: 'دورة تدريبية بدون عنوان',
                'draft' => $draft ? [
                    'title' => $draftLocaleTitle,
                    'category_id' => $draft->category_id,
                    'course_type' => $typeReverse[$draft->type] ?? 'recorded',
                    'locale' => 'ar',
                    'seo_description' => $draftLocaleSeo,
                    'description' => $draftLocaleDescription,
                    'video_demo_link' => $draft->video_demo_source === 'external_link' ? $draft->video_demo : null,
                    'tags' => implode(',', $tagTitles),
                    'downloadable' => (bool) ($draft->downloadable ?? false),
                    'partner_instructor' => (bool) ($draft->partner_instructor ?? false),
                    'access_days' => $draft->access_days,
                    'thumbnail' => $draft->thumbnail,
                    'image_cover' => $draft->image_cover,
                ] : [],
                'tags' => $tagTitles,
                'categories' => !empty($categories) ? $categories : [],
                'languages' => [
                    ['key' => 'ar', 'label' => 'العربية'],
                    ['key' => 'en', 'label' => 'English'],
                ],
                'curriculumUnits' => $this->curriculumUnits($draft),
                'teacherQuizzes' => $teacherQuizzes,
                'draftPrice' => $draft->price ?? null,
                'draftCapacity' => $draft->capacity ?? null,
                'draftCertificate' => (bool) ($draft->certificate ?? false),
                'draftAccessDays' => $draft->access_days ?? null,
            ]
        );
    }

    public function storeCourse(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $step = max(1, min(5, (int) $request->input('wizard_step', 1)));
        $attrs = $this->courseWizardFieldNames();
        $soft = $request->boolean('autosave')
            || $request->boolean('save_only')
            || $request->input('go_next') === 'stay'
            || ($request->filled('go_next') && is_numeric($request->input('go_next')) && (int) $request->input('go_next') < $step);

        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = \App\Models\Webinar::where('id', $request->input('draft_id'))
                ->where('teacher_id', $user->id)
                ->where('status', 'is_draft')
                ->firstOrFail();
        }

        if ($step === 1) {
            $request->validate([
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
            ], $this->courseWizardMessages(), $attrs);

            $typeMap = ['recorded' => 'course', 'live' => 'webinar', 'text' => 'text_lesson'];
            $title = trim((string) $request->input('title', ''));
            if ($title === '') {
                $title = 'دورة تدريبية بدون عنوان';
            }

            if (empty($draft)) {
                $draft = new \App\Models\Webinar();
                $draft->teacher_id = $user->id;
                $draft->creator_id = $user->id;
                $draft->status = 'is_draft';
                $slugBase = \Illuminate\Support\Str::slug($title);
                if ($slugBase === '') {
                    $slugBase = 'course';
                }
                $draft->slug = $slugBase . '-' . time();
                $draft->created_at = time();
            }

            $draft->type = $typeMap[$request->input('course_type', 'recorded')] ?? 'course';
            $draft->category_id = $request->input('category_id') ?: null;
            $draft->downloadable = $request->boolean('downloadable');
            $draft->partner_instructor = $request->boolean('partner_instructor');
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
                $quiz = \App\Models\Quiz::where('id', $request->input('quiz_id'))
                    ->where('creator_id', $user->id)
                    ->firstOrFail();
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

        if ($isDone) {
            $request->validate([
                'confirm_rights' => 'accepted',
                'confirm_terms' => 'accepted',
            ], array_merge($this->courseWizardMessages(), [
                'confirm_rights.accepted' => 'يجب تأكيد حقوق الملكية الفكرية قبل الإرسال.',
                'confirm_terms.accepted' => 'يجب الموافقة على شروط المدربين قبل الإرسال.',
            ]), $attrs);

            $draft->status = 'pending';
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

        $progressMap = [1 => 20, 2 => 40, 3 => 60, 4 => 80, 5 => 100];
        $draftTitle = null;
        if (!empty($draft)) {
            $tr = $draft->translate('ar') ?: $draft->translate(app()->getLocale()) ?: $draft->translations()->first();
            $draftTitle = $tr->title ?? null;
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'ok' => true,
                'draft_id' => $draft->id ?? null,
                'draft_title' => $draftTitle ?: 'دورة تدريبية بدون عنوان',
                'step' => $step,
                'next_step' => $isDone ? null : $nextStep,
                'progress' => $progressMap[$isDone ? 5 : $nextStep] ?? 20,
                'message' => $isDone ? 'تم إرسال الدورة للمراجعة' : 'تم حفظ المسودة',
                'done' => $isDone,
                'redirect' => $isDone ? route('panel.v1.instructor.courses') : null,
            ]);
        }

        if ($isDone) {
            return redirect()
                ->route('panel.v1.instructor.courses')
                ->with('toast', [
                    'title' => 'تم',
                    'msg' => 'تم إرسال الدورة للمراجعة',
                    'type' => 'success',
                ]);
        }

        $params = ['step' => $nextStep];
        if (!empty($draft)) {
            $params['draft'] = $draft->id;
        }

        return redirect()
            ->route('panel.v1.instructor.courses.create', $params)
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم حفظ المسودة بنجاح',
                'type' => 'success',
            ]);
    }

    private function courseWizardFieldNames(): array
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

    private function courseWizardMessages(): array
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
        ], $this->courseWizardMessages(), $this->courseWizardFieldNames());

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
        if (app()->getLocale() !== 'ar') {
            $tEn = $chapter->translateOrNew(app()->getLocale());
            $tEn->locale = app()->getLocale();
            $tEn->title = $request->input('title');
            $tEn->save();
        }

        return $this->curriculumResponse($request, $draft, 'تمت إضافة الوحدة', [
            'unit' => [
                'id' => $chapter->id,
                'title' => $request->input('title'),
                'lessons' => [],
                'delete_url' => route('panel.v1.instructor.curriculum.chapters.delete', ['chapterId' => $chapter->id]),
                'session_store_url' => route('panel.v1.instructor.curriculum.sessions.store'),
                'file_store_url' => route('panel.v1.instructor.curriculum.files.store'),
                'text_store_url' => route('panel.v1.instructor.curriculum.texts.store'),
            ],
        ]);
    }

    public function chapterDelete(Request $request, int $chapterId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer'], $this->courseWizardMessages(), $this->courseWizardFieldNames());
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\WebinarChapter::where('id', $chapterId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->curriculumResponse($request, $draft, 'تم حذف الوحدة', [
            'deleted' => ['kind' => 'chapter', 'id' => $chapterId],
        ]);
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
        ], $this->courseWizardMessages(), $this->courseWizardFieldNames());

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
        if (app()->getLocale() !== 'ar') {
            $tEn = $session->translateOrNew(app()->getLocale());
            $tEn->locale = app()->getLocale();
            $tEn->title = $request->input('topic');
            $tEn->save();
        }

        return $this->curriculumResponse($request, $draft, 'تمت إضافة الجلسة', [
            'chapter_id' => $chapter->id,
            'lesson' => [
                'kind' => 'session',
                'id' => $session->id,
                'title' => $request->input('topic'),
                'duration' => ((int) $request->input('duration')) . ' دقيقة',
                'delete_url' => route('panel.v1.instructor.curriculum.sessions.delete', ['sessionId' => $session->id]),
            ],
        ]);
    }

    public function curriculumSessionDelete(Request $request, int $sessionId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer'], $this->courseWizardMessages(), $this->courseWizardFieldNames());
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\Session::where('id', $sessionId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->curriculumResponse($request, $draft, 'تم حذف الجلسة', [
            'deleted' => ['kind' => 'session', 'id' => $sessionId],
        ]);
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
        ], $this->courseWizardMessages(), $this->courseWizardFieldNames());

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
        if (app()->getLocale() !== 'ar') {
            $tEn = $file->translateOrNew(app()->getLocale());
            $tEn->locale = app()->getLocale();
            $tEn->title = $request->input('title');
            $tEn->save();
        }

        return $this->curriculumResponse($request, $draft, 'تم رفع الملف', [
            'chapter_id' => $chapter->id,
            'lesson' => [
                'kind' => 'file',
                'id' => $file->id,
                'title' => $request->input('title'),
                'duration' => 'ملف',
                'delete_url' => route('panel.v1.instructor.curriculum.files.delete', ['fileId' => $file->id]),
            ],
        ]);
    }

    public function curriculumFileDelete(Request $request, int $fileId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer'], $this->courseWizardMessages(), $this->courseWizardFieldNames());
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\File::where('id', $fileId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->curriculumResponse($request, $draft, 'تم حذف الملف', [
            'deleted' => ['kind' => 'file', 'id' => $fileId],
        ]);
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
        ], $this->courseWizardMessages(), $this->courseWizardFieldNames());

        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        $chapter = \App\Models\WebinarChapter::where('id', $request->input('chapter_id'))
            ->where('webinar_id', $draft->id)
            ->firstOrFail();

        $text = new \App\Models\TextLesson();
        $text->creator_id = $user->id;
        $text->webinar_id = $draft->id;
        $text->chapter_id = $chapter->id;
        $text->accessibility = 'paid';
        $text->status = 'active';
        $text->created_at = time();
        $text->updated_at = time();
        $text->save();

        $summary = (string) $request->input('summary', '');
        $locales = array_values(array_unique(array_filter(['ar', app()->getLocale()])));
        foreach ($locales as $loc) {
            $translation = $text->translateOrNew($loc);
            $translation->locale = $loc;
            $translation->title = $request->input('title');
            $translation->summary = $summary;
            $translation->content = $summary !== '' ? $summary : $request->input('title');
            $translation->save();
        }

        return $this->curriculumResponse($request, $draft, 'تمت إضافة الدرس النصي', [
            'chapter_id' => $chapter->id,
            'lesson' => [
                'kind' => 'text',
                'id' => $text->id,
                'title' => $request->input('title'),
                'duration' => 'نصي',
                'delete_url' => route('panel.v1.instructor.curriculum.texts.delete', ['textId' => $text->id]),
            ],
        ]);
    }

    public function curriculumTextDelete(Request $request, int $textId)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) {
            return redirect('/login');
        }

        $request->validate(['draft_id' => 'required|integer'], $this->courseWizardMessages(), $this->courseWizardFieldNames());
        $draft = $this->draftOrFail($user, $request->input('draft_id'));

        \App\Models\TextLesson::where('id', $textId)
            ->where('webinar_id', $draft->id)
            ->delete();

        return $this->curriculumResponse($request, $draft, 'تم حذف الدرس', [
            'deleted' => ['kind' => 'text', 'id' => $textId],
        ]);
    }

    private function curriculumResponse(Request $request, $draft, string $message, array $payload = [])
    {
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(array_merge([
                'ok' => true,
                'message' => $message,
                'draft_id' => $draft->id,
            ], $payload));
        }

        return $this->backToDraftStep($request, $draft, 2, $message);
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
            ->map(function ($chapter) use ($translatedTitle) {
                $lessons = [];

                foreach ($chapter->sessions as $session) {
                    $lessons[] = [
                        'kind' => 'session',
                        'id' => $session->id,
                        'title' => $translatedTitle($session),
                        'duration' => ($session->duration ?? 0) . ' دقيقة',
                        'delete_url' => route('panel.v1.instructor.curriculum.sessions.delete', ['sessionId' => $session->id]),
                    ];
                }

                foreach ($chapter->files as $file) {
                    $lessons[] = [
                        'kind' => 'file',
                        'id' => $file->id,
                        'title' => $translatedTitle($file),
                        'duration' => 'ملف',
                        'delete_url' => route('panel.v1.instructor.curriculum.files.delete', ['fileId' => $file->id]),
                    ];
                }

                foreach ($chapter->textLessons as $text) {
                    $lessons[] = [
                        'kind' => 'text',
                        'id' => $text->id,
                        'title' => $translatedTitle($text),
                        'duration' => 'نصي',
                        'delete_url' => route('panel.v1.instructor.curriculum.texts.delete', ['textId' => $text->id]),
                    ];
                }

                return [
                    'id' => $chapter->id,
                    'title' => $translatedTitle($chapter),
                    'lessons' => $lessons,
                    'delete_url' => route('panel.v1.instructor.curriculum.chapters.delete', ['chapterId' => $chapter->id]),
                    'session_store_url' => route('panel.v1.instructor.curriculum.sessions.store'),
                    'file_store_url' => route('panel.v1.instructor.curriculum.files.store'),
                    'text_store_url' => route('panel.v1.instructor.curriculum.texts.store'),
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

        // Real instructor shell (chapters with sessions/files/textLessons)
        $shell = $this->instructorCourseShell($webinar, $request);
        $firstSession = \App\Models\Session::where('webinar_id', $webinar->id)->orderBy('date')->orderBy('id')->first();
        $firstQuiz = \App\Models\Quiz::where('webinar_id', $webinar->id)->orderBy('id')->first();
        $firstAssignment = \App\Models\WebinarAssignment::where('webinar_id', $webinar->id)->orderBy('id')->first();
        $files = \App\Models\File::where('webinar_id', $webinar->id)->orderBy('id')->limit(10)->get();
        $courseComments = \App\Models\Comment::with(['user'])
            ->where('webinar_id', $webinar->id)
            ->whereNull('reply_id')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($comment) {
                return [
                    'author' => $comment->user->full_name ?? 'طالب',
                    'body' => $comment->comment,
                    'status' => $comment->status,
                    'time' => !empty($comment->created_at) ? date('Y/m/d H:i', (int) $comment->created_at) : '',
                ];
            })->all();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-watch',
            'مشاهدة المحاضرة',
            array_merge($shell, [
                'webinar' => $webinar,
                'courseSlug' => $webinar->slug,
                'lesson' => ['title' => $firstSession->title ?? $webinar->title],
                'files' => $files->map(fn($f)=>['name'=>$f->title,'size'=>$f->volume ?? ''])->all(),
                'hasFiles' => $files->isNotEmpty(),
                'hasLectureQuiz' => !empty($firstQuiz),
                'hasLectureAssignment' => !empty($firstAssignment),
                'courseComments' => $courseComments,
                'lectureQuiz' => $firstQuiz ? [
                    'title'=>$firstQuiz->title,
                    'subtitle'=>$webinar->title,
                    'duration'=>!empty($firstQuiz->time)?$firstQuiz->time.' دقيقة':'—',
                    'questions_count'=>\App\Models\QuizzesQuestion::where('quiz_id',$firstQuiz->id)->count().' أسئلة',
                    'pass_score'=>$firstQuiz->pass_mark.'%',
                    'attempts'=>$firstQuiz->attempt?$firstQuiz->attempt.' محاولات':'—',
                ] : null,
                'lectureAssignment' => $firstAssignment ? [
                    'title'=>$firstAssignment->title ?? 'تكليف الدورة',
                    'subtitle'=>$webinar->title,
                    'deadline'=> !empty($firstAssignment->deadline) ? ((int)$firstAssignment->deadline).' يوم من الشراء' : 'غير محدود',
                    'attempts'=>$firstAssignment->attempts ?? 'غير محدود',
                    'grade'=>$firstAssignment->grade ?? '—',
                    'pass_grade'=>$firstAssignment->pass_grade ?? '—',
                    'description'=>$firstAssignment->description ?? '',
                    'file_name'=>'',
                    'file_size'=>'',
                ] : null,
            ])
        );
    }

    private function instructorCourseShell($webinar, ?Request $request = null): array
    {
        $chapters = \App\Models\WebinarChapter::with(['sessions','files','textLessons'])->where('webinar_id',$webinar->id)->orderBy('order')->orderBy('id')->get();
        $requested = $request ? $request->get('item') : null;
        $list=[]; $first=true;
        foreach($chapters as $ch){
            $items=[];
            foreach($ch->sessions as $s){ $active=$first && empty($requested) ? true : ($requested=='session_'.$s->id); $items[]=['title'=>$s->title,'type'=>'video','active'=>$active,'route'=>'panel.v1.instructor.courses.watch']; $first=false; }
            foreach($ch->files as $f){ $active=$requested=='file_'.$f->id; $items[]=['title'=>$f->title,'type'=>'file','active'=>$active,'route'=>'panel.v1.instructor.courses.watch']; }
            foreach($ch->textLessons as $t){ $active=$requested=='text_'.$t->id; $items[]=['title'=>$t->title,'type'=>'text','active'=>$active,'route'=>'panel.v1.instructor.courses.watch']; }
            $list[]=['title'=>$ch->title ?: 'الوحدة','completed'=>false,'expanded'=>false,'subtitle'=>'محتوى الوحدة','items'=>$items];
        }
        if(empty($list)){
            $list[]=['title'=>'المحاضرة الأولى','completed'=>false,'expanded'=>true,'subtitle'=>'لا يوجد محتوى بعد','items'=>[]];
        } else {
            $list[0]['expanded']=true;
        }
        return [
            'slug'=>$webinar->slug,
            'course'=>['title'=>$webinar->title,'subtitle'=>$webinar->category->title ?? '','progress'=>0,'progress_label'=>'نسبة الإنجاز'],
            'chapters'=>$list,
        ];
    }

    public function courseAssignment(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) { return redirect('/login'); }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        $assignment = \App\Models\WebinarAssignment::where('webinar_id',$webinar->id)->orderBy('id')->first();
        $shell = $this->instructorCourseShell($webinar,$request);
        return $this->render(
            $request,
            'panel_v1.instructor.pages.assignment-review',
            'تقييم التكليف',
            array_merge($shell, [
                'webinar'=>$webinar,
                'courseSlug'=>$webinar->slug,
                'assignmentId'=>$assignment->id ?? 0,
                'reviewTitle'=> $assignment->title ?? 'تكليف الدورة',
                'detailsTitle'=>'تفاصيل التكليف',
                'detailsBody'=> $assignment->description ?? 'لا يوجد وصف',
                'points'=>['التزام بالموعد','جودة المحتوى','الالتزام بالمعايير'],
                'pointsTitle'=>'نقاط التقييم',
                'maxGrade'=>$assignment->grade ?? 50,
                'passGrade'=>$assignment->pass_grade ?? 25,
                'studentAnswerParagraphs'=>[],
                'studentAnswerPoints'=>[],
            ])
        );
    }

    public function coursePerformance(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) { return redirect('/login'); }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        $perf = $this->performanceData($webinar);
        $salesCount = \App\Models\Sale::where('webinar_id',$webinar->id)->whereNull('refund_at')->count();
        $pending = \App\Models\WebinarAssignmentHistory::whereIn('assignment_id', \App\Models\WebinarAssignment::where('webinar_id',$webinar->id)->pluck('id'))->where('status','pending')->count();
        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-performance',
            'لوحة أداء الدورة',
            array_merge($perf, [
                'webinar'=>$webinar,
                'courseSlug'=>$webinar->slug,
                'slug'=>$webinar->slug,
                'courseTitle'=>$webinar->title,
                'courseSubtitle'=>$webinar->category->title ?? '',
                'alertText'=> $pending>0 ? "لديك $pending واجبات بانتظار التصحيح" : "لا توجد مهام عاجلة",
                'perfStats'=>[
                    ['value'=>$salesCount.' طالب','label'=>'مسجلون','tone'=>'green'],
                    ['value'=>$pending.' واجبات','label'=>'بانتظار التصحيح','tone'=>'red'],
                    ['value'=>count($perf['students'] ?? []).' طالب','label'=>'إجمالي','tone'=>'yellow'],
                ],
            ])
        );
    }

    public function courseAssignments(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) { return redirect('/login'); }
        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        $assignments = \App\Models\WebinarAssignment::where('webinar_id',$webinar->id)->orderBy('id','desc')->get();
        $histories = \App\Models\WebinarAssignmentHistory::with(['student'])->whereIn('assignment_id',$assignments->pluck('id'))->orderBy('id','desc')->limit(30)->get();
        $total = $histories->count();
        $passed = $histories->where('status','passed')->count();
        $pending = $histories->where('status','pending')->count();
        $rate = $total>0 ? (int) round($passed/$total*100) : 0;
        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-assignments',
            'متطلبات الدورات',
            [
                'webinar'=>$webinar,
                'courseSlug'=>$webinar->slug,
                'slug'=>$webinar->slug,
                'pageTitleMain'=>'متطلبات دورة '. $webinar->title,
                'pageSubtitle'=>$webinar->category->title ?? '',
                'summaryCards'=>[
                    ['label'=>'إجمالي التسليمات','value'=>(string)$total,'edge'=>'#0f4c45','valueClass'=>'text-primary'],
                    ['label'=>'التسليمات المجتازة','value'=>(string)$passed,'edge'=>'#0FC787','valueClass'=>'text-[#0FC787]'],
                    ['label'=>'قيد المراجعة','value'=>(string)$pending,'edge'=>'#F59E0B','valueClass'=>'text-[#F59E0B]'],
                    ['label'=>'معدل النجاح','value'=>$rate.'%','edge'=>'#6366F1','valueClass'=>'text-[#6366F1]'],
                ],
                'submissions'=> $histories->map(fn($h)=>[
                    'name'=>$h->student->full_name ?? '',
                    'joined_at'=> $h->created_at ? date('Y/m/d',(int)$h->created_at) : '—',
                    'latest_at'=> $h->updated_at ? date('Y/m/d',(int)$h->updated_at) : '—',
                    'last_at'=>'—',
                    'attempts'=>'1 / '.($h->assignment->attempts ?? '—'),
                    'grade'=> ($h->grade ?? '—').' / '.($h->assignment->grade ?? '—'),
                    'status'=> $h->status==='passed' ? 'مجتاز' : ($h->status==='pending' ? 'قيد المراجعة' : $h->status),
                ])->all(),
            ]
        );
    }

    public function assignments(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isTeacher()) { return redirect('/login'); }
        $webinarIds = $this->teacherWebinars($user)->pluck('id')->all();
        $assignments = !empty($webinarIds) ? \App\Models\WebinarAssignment::with(['webinar'])->whereIn('webinar_id',$webinarIds)->orderBy('id','desc')->limit(20)->get() : collect();
        $histories = !empty($webinarIds) ? \App\Models\WebinarAssignmentHistory::with(['assignment.webinar','student'])->whereIn('assignment_id',$assignments->pluck('id'))->orderBy('id','desc')->limit(30)->get() : collect();
        $pending = $histories->where('status','pending')->count();
        $graded = $histories->where('status','!=','pending')->count();
        return $this->render($request, 'panel_v1.instructor.pages.assignments', 'إدارة الواجبات والتكليفات', [
            'assignmentStats'=>[
                ['value'=>$pending.' تكليف','label'=>'بانتظار التصحيح'],
                ['value'=>$graded.' تكليف','label'=>'تم تصحيحها'],
                ['value'=>$histories->count().' تكليف','label'=>'تسليم'],
            ],
            'currentAssignments'=> $assignments->take(4)->map(fn($a)=>[
                'title'=>$a->title ?? 'تكليف',
                'course'=>$a->webinar->title ?? '',
                'deadline'=> !empty($a->deadline) ? ((int)$a->deadline).' يوم من الشراء' : 'غير محدود',
                'submissions'=> \App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->count().' / '.$a->webinar->sales->count() ?? 0,
                'pending'=> \App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->where('status','pending')->count().' طالب',
                'graded'=> \App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->where('status','!=','pending')->count().' طالب',
                'progress'=> 0,
                'points'=>$a->grade ?? 0,
                'badge'=>0,
                'cta'=>'عرض التسليمات',
            ])->all(),
            'resultsRows'=> $assignments->take(6)->map(fn($a)=>[
                'title'=>$a->title ?? '',
                'course'=>$a->webinar->title ?? '',
                'grade'=>$a->grade ?? 0,
                'passGrade'=>$a->pass_grade ?? 0,
                'submissions'=>\App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->count(),
                'pending'=>\App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->where('status','pending')->count(),
                'passed'=>\App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->where('status','passed')->count(),
                'failed'=>\App\Models\WebinarAssignmentHistory::where('assignment_id',$a->id)->where('status','not_passed')->count(),
                'deadline'=> !empty($a->deadline) ? ((int)$a->deadline).' يوم من الشراء' : '—',
                'status'=>'نشط',
            ])->all(),
            'studentResultsRows'=> $histories->take(8)->map(fn($h)=>[
                'name'=>$h->student->full_name ?? '',
                'title'=>$h->assignment->title ?? '',
                'course'=>$h->assignment->webinar->title ?? '',
                'first_at'=> $h->created_at ? date('Y/m/d',(int)$h->created_at) : '—',
                'last_at'=>'—',
                'attempts'=>'—',
                'grade'=>$h->grade ?? '—',
                'created_day'=> $h->created_at ? date('d',(int)$h->created_at) : '—',
                'created_month'=> $h->created_at ? date('F Y',(int)$h->created_at) : '—',
                'status'=> $h->status==='passed' ? 'تم التسليم' : ($h->status==='pending' ? 'بانتظار التصحيح' : $h->status),
            ])->all(),
        ]);
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
            [
                'webinar' => $webinar,
                'courseSlug' => $webinar->slug,
                'historyId' => $history->id,
                'historyStatus' => $history->status,
                'historyGrade' => $history->grade,
                'reviewStudentName' => $history->student->full_name ?? '',
                'studentAnswerParagraphs' => !empty($messages) ? $messages : [],
                'maxGrade' => $history->assignment->grade ?? 50,
                'passGrade' => $history->assignment->pass_grade ?? 25,
                'reviewTitle' => 'تقييم التكليف',
                'detailsTitle' => 'تفاصيل التكليف',
                'assignmentId' => $history->assignment->id ?? $id,
            ]
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
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $dayLabels = [
            'saturday' => 'السبت',
            'sunday' => 'الأحد',
            'monday' => 'الإثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
        ];

        $meeting = \App\Models\Meeting::where('creator_id', $user->id)->first();
        $meetingIds = $meeting ? [$meeting->id] : [];

        $timeIds = !empty($meetingIds)
            ? \App\Models\MeetingTime::whereIn('meeting_id', $meetingIds)->pluck('id')->all()
            : [];

        $reservations = !empty($timeIds)
            ? \App\Models\ReserveMeeting::with(['user', 'meetingTime'])
                ->whereIn('meeting_time_id', $timeIds)
                ->orderByDesc('id')
                ->limit(50)
                ->get()
            : collect();

        $rows = [];
        foreach ($reservations as $reservation) {
            $slot = $reservation->meetingTime;
            $dayKey = $slot->day_label ?? null;
            $joinType = match ($reservation->meeting_type) {
                'in_person' => 'وجهاً لوجه',
                'all' => 'الكل',
                default => 'أونلاين',
            };
            $statusLabel = match ($reservation->status) {
                \App\Models\ReserveMeeting::$finished => 'منتهية',
                \App\Models\ReserveMeeting::$canceled => 'ملغاة',
                \App\Models\ReserveMeeting::$pending => 'قيد الانتظار',
                default => 'مفتوحة',
            };

            $ts = (int) ($reservation->date ?: $reservation->start_at ?: $reservation->reserved_at);
            $timeLabel = $slot->time ?? (!empty($reservation->start_at) ? date('H:i', (int) $reservation->start_at) : '');

            $rows[] = [
                'initials' => mb_substr($reservation->user->full_name ?? '?', 0, 2),
                'name' => $reservation->user->full_name ?? '',
                'email' => $reservation->user->email ?? '',
                'joinType' => $joinType,
                'day' => $dayLabels[$dayKey] ?? ($reservation->day ?? '—'),
                'date' => $ts > 0 ? date('Y/m/d', $ts) : ($reservation->day ?? '—'),
                'time' => $timeLabel,
                'amount' => handlePrice($reservation->paid_amount),
                'students' => (int) ($reservation->student_count ?? 1),
                'status' => $statusLabel,
                'link' => $reservation->link,
            ];
        }

        $now = time();
        $upcoming = !empty($timeIds)
            ? \App\Models\ReserveMeeting::with(['meetingTime', 'user'])
                ->whereIn('meeting_time_id', $timeIds)
                ->where(function ($q) use ($now) {
                    $q->where('date', '>=', $now)
                        ->orWhere('start_at', '>=', $now);
                })
                ->whereIn('status', [
                    \App\Models\ReserveMeeting::$open,
                    \App\Models\ReserveMeeting::$pending,
                ])
                ->orderByRaw('COALESCE(start_at, date) asc')
                ->first()
            : null;

        $session = [];
        if ($upcoming) {
            $slot = $upcoming->meetingTime;
            $ts = (int) ($upcoming->date ?: $upcoming->start_at ?: $upcoming->reserved_at);
            $session = [
                'title' => $slot->description ?: 'جلسة استشارية',
                'status' => $upcoming->status === \App\Models\ReserveMeeting::$pending ? 'قيد الانتظار' : 'مجدولة',
                'price' => handlePrice($upcoming->paid_amount ?: ($meeting->amount ?? 0)),
                'instructor' => $user->full_name,
                'instructorInitials' => mb_substr($user->full_name ?? '?', 0, 1),
                'date' => $ts > 0 ? date('Y/m/d', $ts) : ($upcoming->day ?? ''),
                'time' => $slot->time ?? ($upcoming->start_at ? date('H:i', (int) $upcoming->start_at) : ''),
                'linkLabel' => ($upcoming->meeting_type === 'in_person') ? 'لقاء حضوري' : 'لقاء أونلاين',
                'link' => $upcoming->link,
            ];
        } elseif (!empty($meeting) && empty($rows)) {
            // Show meeting package card when no upcoming reservation yet.
            $session = [
                'title' => 'الجلسات الاستشارية',
                'status' => !empty($meeting->disabled) ? 'متوقفة' : 'متاحة للحجز',
                'price' => handlePrice($meeting->amount ?? 0),
                'instructor' => $user->full_name,
                'instructorInitials' => mb_substr($user->full_name ?? '?', 0, 1),
                'date' => '—',
                'time' => 'حدد مواعيدك من إعدادات الجلسات',
                'linkLabel' => 'لقاء أونلاين',
                'link' => null,
            ];
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.consultations',
            'الجلسات الاستشارية',
            [
                'attendees' => $rows,
                'session' => $session,
            ]
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
            [
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
            ]
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
            [
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
            ]
        );
    }

    public function finance(Request $request)
    {
        $guardUser = $this->resolveInstructor($request);

        if ($guardUser instanceof \Illuminate\Http\RedirectResponse) {
            return $guardUser;
        }

        $sales = \App\Models\Sale::with(['buyer', 'webinar'])
            ->where('seller_id', $guardUser->id)
            ->whereNull('refund_at')
            ->orderBy('id', 'desc')
            ->limit(40)
            ->get();

        $salesRows = $sales->map(function ($sale) {
            $isCourse = $sale->type === 'webinar';
            return [
                'name' => $sale->buyer->full_name ?? '—',
                'email' => $sale->buyer->email ?? '',
                'service' => $sale->webinar->title ?? $sale->type,
                'service_id' => $sale->webinar_id ?: $sale->id,
                'original_price' => handlePrice($sale->amount),
                'discount' => handlePrice($sale->discount ?? 0),
                'total' => handlePrice($sale->total_amount),
                'net' => handlePrice(($sale->total_amount ?? 0) - ($sale->commission ?? 0)),
                'type' => $isCourse ? 'course' : 'meeting',
                'type_label' => $isCourse ? 'دورة' : 'استشارة',
                'date' => date('Y/m/d', (int) $sale->created_at),
                'time' => date('H:i', (int) $sale->created_at),
            ];
        })->all();

        $totalSales = (float) $sales->sum('total_amount');
        $totalDiscount = (float) $sales->sum('discount');
        $totalCommission = (float) $sales->sum('commission');
        $totalNet = $totalSales - $totalCommission;

        try {
            $available = (float) $guardUser->getPayout();
            $income = (float) $guardUser->getIncome();
        } catch (\Throwable $e) {
            $available = $totalNet;
            $income = $totalNet;
        }

        $walletRows = \App\Models\Accounting::where('user_id', $guardUser->id)
            ->where('type_account', \App\Models\Accounting::$income)
            ->where('system', false)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($row) {
                $isCredit = $row->type === \App\Models\Accounting::$addiction;
                return [
                    'title' => $row->description ?: ($isCredit ? 'إضافة رصيد' : 'خصم'),
                    'amount' => handlePrice($row->amount),
                    'type' => $isCredit ? 'credit' : 'debit',
                    'type_label' => $isCredit ? 'دخل' : 'خصم',
                    'date' => date('Y/m/d', (int) $row->created_at),
                    'time' => date('H:i', (int) $row->created_at),
                ];
            })->all();

        $summaryCards = [
            ['label' => 'إجمالي المبيعات', 'value' => handlePrice($totalSales), 'icon' => 'tabler--shopping-cart'],
            ['label' => 'صافي الدخل', 'value' => handlePrice($totalNet), 'icon' => 'tabler--currency-riyal'],
            ['label' => 'الرصيد المتاح', 'value' => handlePrice($available), 'icon' => 'tabler--wallet'],
            ['label' => 'إجمالي الدخل المحاسبي', 'value' => handlePrice($income), 'icon' => 'tabler--chart-bar'],
            ['label' => 'إجمالي الخصومات', 'value' => handlePrice($totalDiscount), 'icon' => 'tabler--receipt'],
            ['label' => 'عمولة المنصة', 'value' => handlePrice($totalCommission), 'icon' => 'tabler--credit-card'],
        ];

        return $this->render(
            $request,
            'panel_v1.instructor.pages.finance',
            'المالية والأرباح',
            [
                'salesRows' => $salesRows,
                'walletRows' => $walletRows,
                'summaryCards' => $summaryCards,
            ]
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

        $summary = [
            'available' => '0.00',
            'total_income' => '0.00',
            'next_payout' => '—',
            'min_withdraw' => '—',
            'held' => '0.00',
        ];
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
            [
                'payoutSummary' => $summary,
                'payoutRows' => $payoutRows,
            ]
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

        // Real promotions / coupons - if tables exist
        $couponRows = $discountRows; // coupons are discounts with codes
        $promoRows = [];
        try {
            $promoRows = \App\Models\Promotion::where('creator_id',$user->id)->orderBy('id','desc')->limit(20)->get()->map(fn($p)=>[
                'name'=>$p->title ?? 'ترويج #'.$p->id,
                'email'=>'',
                'course'=> $p->webinar->title ?? '',
                'course_id'=>$p->webinar_id ?? $p->id,
                'original_price'=>'—',
                'discount'=> $p->discount ?? '—',
                'total'=>'—',
                'net'=>'—',
                'type'=>'ترويج',
                'date'=>date('Y/m/d',(int)$p->created_at),
                'time'=>'',
            ])->all();
        } catch(\Throwable $e) {}

        return $this->render(
            $request,
            'panel_v1.instructor.pages.marketing',
            'إدارة التسويق والعروض',
            [
                'marketingActions' => [
                    ['title' => 'إنشاء قسيمة خصم جديدة', 'subtitle' => 'إنشاء كوبون لطلابك', 'href' => '#'],
                    ['title' => 'إنشاء تخفيض لدورتك', 'subtitle' => 'تخفيض مباشر على الدورة', 'href' => '#'],
                    ['title' => 'إنشاء خطط ترويجية', 'subtitle' => 'حملة تسويقية', 'href' => '#'],
                ],
                'discountRows' => $discountRows,
                'promoRows' => $promoRows,
                'couponRows' => $couponRows,
            ]
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
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $myTickets = \App\Models\Support::with(['department'])
            ->where('user_id', $user->id)
            ->whereNotNull('department_id')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(fn ($t) => [
                'id' => '#' . $t->id,
                'raw_id' => $t->id,
                'subject' => $t->title,
                'department' => $t->department->title ?? '—',
                'status' => $t->status === 'open' ? 'مفتوحة' : ($t->status === 'close' ? 'مغلقة' : 'تم الرد'),
                'status_key' => $t->status,
                'date' => date('Y/m/d', (int) $t->created_at),
            ])->all();

        $teacherWebinarIds = \App\Models\Webinar::where('teacher_id', $user->id)
            ->orWhere('creator_id', $user->id)
            ->pluck('id')
            ->all();

        $courseRows = [];
        if (!empty($teacherWebinarIds)) {
            $courseRows = \App\Models\Support::with(['user', 'webinar'])
                ->whereIn('webinar_id', $teacherWebinarIds)
                ->whereNull('department_id')
                ->orderBy('id', 'desc')
                ->limit(30)
                ->get()
                ->map(fn ($s) => [
                    'student' => $s->user->full_name ?? '',
                    'course' => $s->webinar->title ?? '',
                    'id' => $s->id,
                    'title' => $s->title,
                    'status' => $s->status === 'open' ? 'مفتوحة' : ($s->status === 'close' ? 'مغلقة' : 'تم الرد'),
                    'date' => date('Y/m/d', (int) $s->created_at),
                ])->all();
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.support',
            'مركز الدعم الفني وإدارة التذاكر',
            [
                'supportTickets' => $myTickets,
                'courseSupportRows' => $courseRows,
                'departments' => \App\Models\SupportDepartment::orderBy('id')->get(),
                'supportStats' => [
                    ['label' => 'إجمالي التذاكر', 'value' => count($myTickets) + count($courseRows)],
                    ['label' => 'قيد الانتظار', 'value' => collect($myTickets)->where('status_key', 'open')->count()],
                ],
            ]
        );
    }

    public function storeSupport(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'title' => 'required|string|min:2|max:255',
            'department_id' => 'required|exists:support_departments,id',
            'message' => 'required|string|min:2|max:5000',
        ]);

        $support = \App\Models\Support::create([
            'user_id' => $user->id,
            'department_id' => $request->input('department_id'),
            'webinar_id' => null,
            'title' => $request->input('title'),
            'status' => 'open',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        \App\Models\SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'attach' => null,
            'created_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.support')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إرسال تذكرة الدعم بنجاح',
                'type' => 'success',
            ]);
    }

    public function supportConversations(Request $request, $id = null)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinarIds = $this->instructorSupportWebinarIds($user);
        $selectSupport = null;

        if (!empty($id) && is_numeric($id)) {
            $selectSupport = \App\Models\Support::query()
                ->where('id', $id)
                ->where(function ($query) use ($user, $webinarIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $webinarIds);
                })
                ->with([
                    'user',
                    'department',
                    'webinar.teacher',
                    'conversations' => function ($query) {
                        $query->with(['sender', 'supporter'])->orderBy('created_at', 'asc');
                    },
                ])
                ->first();

            if (empty($selectSupport)) {
                return redirect()
                    ->route('panel.v1.instructor.support')
                    ->with('toast', ['title' => 'تنبيه', 'msg' => 'التذكرة غير موجودة أو غير مسموح بها', 'type' => 'error']);
            }
        }

        $isTicketMode = !empty($selectSupport) && !empty($selectSupport->department_id);

        $supportsQuery = \App\Models\Support::query()
            ->with([
                'user',
                'department',
                'webinar.teacher',
                'conversations' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
            ]);

        if ($isTicketMode) {
            $supportsQuery->whereNotNull('department_id')->where('user_id', $user->id);
        } else {
            $supportsQuery->whereNull('department_id')
                ->where(function ($query) use ($user, $webinarIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $webinarIds);
                });
        }

        $supports = $supportsQuery
            ->orderBy('created_at', 'desc')
            ->orderBy('status', 'asc')
            ->get();

        $supportsCount = $supports->count();
        $openSupportsCount = $supports->where('status', '!=', 'close')->count();
        $closeSupportsCount = $supports->where('status', 'close')->count();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.support-conversations',
            $isTicketMode ? 'تذاكر الدعم' : 'دعم الصفوف',
            [
                'supports' => $supports,
                'selectSupport' => $selectSupport,
                'supportsCount' => $supportsCount,
                'openSupportsCount' => $openSupportsCount,
                'closeSupportsCount' => $closeSupportsCount,
                'isTicketMode' => $isTicketMode,
            ]
        );
    }

    public function storeSupportConversation(Request $request, $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'message' => 'required|string|min:2|max:5000',
            'attach' => 'nullable|file|max:10240',
        ]);

        $support = $this->findInstructorSupportOrFail($user, $id);

        if ($support->status === 'close') {
            return back()->with('toast', ['title' => 'تنبيه', 'msg' => 'التذكرة مغلقة', 'type' => 'error']);
        }

        $support->update([
            'status' => ((int) $support->user_id === (int) $user->id) ? 'open' : 'supporter_replied',
            'updated_at' => time(),
        ]);

        $conversation = \App\Models\SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'attach' => null,
            'created_at' => time(),
        ]);

        if ($request->hasFile('attach')) {
            $path = $this->uploadFile(
                $request->file('attach'),
                "supports/{$support->id}/conversations",
                "attach_{$conversation->id}",
                $user->id
            );
            $conversation->update(['attach' => $path]);
        }

        if (!empty($support->webinar_id)) {
            $webinar = \App\Models\Webinar::find($support->webinar_id);
            if ($webinar) {
                sendNotification('support_message_replied', [
                    '[c.title]' => $webinar->title,
                ], ((int) $support->user_id === (int) $user->id) ? $webinar->teacher_id : $support->user_id);
            }
        }

        if (!empty($support->department_id)) {
            sendNotification('support_message_replied_admin', [
                '[s.t.title]' => $support->title,
            ], 1);
        }

        return redirect()
            ->route('panel.v1.instructor.support.conversations', ['id' => $support->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم إرسال الرد', 'type' => 'success']);
    }

    public function closeSupport(Request $request, $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $support = $this->findInstructorSupportOrFail($user, $id);
        $support->update([
            'status' => 'close',
            'updated_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.support.conversations', ['id' => $support->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم إغلاق المحادثة', 'type' => 'success']);
    }

    private function instructorSupportWebinarIds($user): array
    {
        return \App\Models\Webinar::query()
            ->where(function ($query) use ($user) {
                $query->where('teacher_id', $user->id)
                    ->orWhere('creator_id', $user->id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function findInstructorSupportOrFail($user, $id): \App\Models\Support
    {
        $webinarIds = $this->instructorSupportWebinarIds($user);

        $support = \App\Models\Support::query()
            ->where('id', $id)
            ->where(function ($query) use ($user, $webinarIds) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('webinar_id', $webinarIds);
            })
            ->first();

        if (empty($support)) {
            abort(404);
        }

        return $support;
    }

    public function notifications(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $notifications = $this->instructorNotificationsQuery($user)
            ->orderBy('notifications.id', 'desc')
            ->limit(40)
            ->get();

        $seenIds = NotificationStatus::where('user_id', $user->id)
            ->pluck('notification_id')
            ->flip();

        $notifications->each(function ($notification) use ($seenIds) {
            $notification->is_seen = isset($seenIds[$notification->id]);
        });

        return $this->render($request, 'panel_v1.instructor.pages.notifications', 'الإشعارات', [
            'notifications' => $notifications,
            'hasNotifications' => $notifications->isNotEmpty(),
        ]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $ids = $this->instructorNotificationsQuery($user)->pluck('notifications.id');

        $existing = NotificationStatus::where('user_id', $user->id)
            ->whereIn('notification_id', $ids)
            ->pluck('notification_id')
            ->all();

        $now = time();
        foreach ($ids->diff($existing) as $notificationId) {
            NotificationStatus::create([
                'user_id' => $user->id,
                'notification_id' => $notificationId,
                'seen_at' => $now,
            ]);
        }

        return redirect()
            ->back()
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم وضع علامة مقروء على جميع الإشعارات',
                'type' => 'success',
            ]);
    }

    public function settings(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $user->load([
            'selectedBank.bank.specifications',
            'selectedBank.specifications',
            'userMetas',
            'occupations',
            'profileAttachments',
        ]);

        return $this->render(
            $request,
            'panel_v1.instructor.pages.settings',
            'إعدادات الملف الشخصي',
            array_merge($this->profileExtraViewData($request, $user), $this->profileAboutData($user), $this->profileFinancialData($user), [
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
            'level_of_training' => 'nullable|array',
            'level_of_training.*' => 'in:beginner,middle,expert',
            'birthday' => 'nullable|date',
            'socials' => 'nullable|array',
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

        return redirect()->to(route('panel.v1.instructor.settings') . '#settings-tabs-5')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ بيانات "حول"', 'type' => 'success']);
    }

    public function storeMeta(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $meta = $this->storeProfileMeta($user, $request->input('name'), $request->input('value'));

        if (!$meta) {
            return response()->json([], 422);
        }

        return response()->json([
            'code' => 200,
            'id' => $meta->id,
            'name' => $meta->name,
            'value' => $meta->value,
            'delete_url' => route('panel.v1.instructor.metas.delete', ['metaId' => $meta->id]),
        ], 200);
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['code' => 200, 'id' => (int) $metaId], 200);
        }

        return redirect()->to(route('panel.v1.instructor.settings') . '#settings-tabs-5')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم الحذف بنجاح', 'type' => 'success']);
    }

    public function storeAttachment(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $this->storeProfileAttachment($request, $user);

        return redirect()->to(route('panel.v1.instructor.settings') . '#settings-tabs-5')
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

        return redirect()->to(route('panel.v1.instructor.settings') . '#settings-tabs-5')
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

        return redirect()->to(route('panel.v1.instructor.settings') . '#settings-tabs-5')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حذف المرفق', 'type' => 'success']);
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
        return Webinar::with(['category', 'sessions', 'files', 'textLessons'])
            ->where('teacher_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
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
            ? WebinarAssignmentHistory::with(['student', 'assignment'])
                ->whereIn('assignment_id', $assignmentIds)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'title' => $history->assignment->title ?? 'تكليف بانتظار التقييم',
                        'student' => $history->student->full_name ?? '',
                        'time' => date('Y/m/d', (int) $history->created_at),
                    ];
                })->all()
            : [];

        $homeCourses = $webinars
            ->sortByDesc(fn ($webinar) => $webinar->status === Webinar::$active ? 1 : 0)
            ->take(3)
            ->values()
            ->map(function ($webinar) {
                return [
                    'id' => $webinar->id,
                    'title' => $webinar->title,
                    'subtitle' => $webinar->category->title ?? '',
                    'progress' => $this->webinarFinishedProgress($webinar),
                    'slug' => $webinar->slug,
                    'status' => $webinar->status,
                ];
            })->all();

        return [
            'instructorName' => $user->full_name,
            'instructorEmail' => $user->email,
            'stats' => [
                ['label' => 'إجمالي الأرباح', 'value' => handlePrice($earnings)],
                ['label' => 'إجمالي الطلاب', 'value' => (string) $students],
                ['label' => 'الدورات النشطة', 'value' => (string) $webinars->where('status', 'active')->count()],
                ['label' => 'تكليفات وواجبات', 'value' => (string) $pendingGradingCount],
                ['label' => 'الاختبارات النشطة', 'value' => (string) $activeQuizzes],
            ],
            'courses' => $homeCourses,
            'quickActions' => [
                ['label' => 'انشاء دورة جديدة', 'route' => 'panel.v1.instructor.courses.create'],
                ['label' => 'انشاء اختبار جديد', 'route' => 'panel.v1.instructor.quizzes.create'],
                ['label' => 'عرض جميع التكليفات', 'route' => 'panel.v1.instructor.assignments'],
                ['label' => 'عرض الطلاب', 'route' => 'panel.v1.instructor.students'],
            ],
            'upcomingLectures' => $upcomingLectures,
            'pendingGrading' => $pendingGrading,
        ];
    }

    private function courseCards($user): array
    {
        $webinars = $this->teacherWebinars($user);
        $webinarIds = $webinars->pluck('id')->all();

        $assignmentCounts = !empty($webinarIds)
            ? WebinarAssignment::query()
                ->whereIn('webinar_id', $webinarIds)
                ->selectRaw('webinar_id, COUNT(*) as aggregate')
                ->groupBy('webinar_id')
                ->pluck('aggregate', 'webinar_id')
            : collect();

        $quizCounts = !empty($webinarIds)
            ? Quiz::query()
                ->whereIn('webinar_id', $webinarIds)
                ->selectRaw('webinar_id, COUNT(*) as aggregate')
                ->groupBy('webinar_id')
                ->pluck('aggregate', 'webinar_id')
            : collect();

        return $webinars->map(function ($webinar) use ($assignmentCounts, $quizCounts) {
            $typeKey = $webinar->type ?: Webinar::$course;
            $typeLabels = [
                Webinar::$webinar => 'محاضرة مباشرة',
                Webinar::$course => 'دورة مسجلة',
                Webinar::$textLesson => 'دورة نصية',
            ];

            $statusLabel = match ($webinar->status) {
                Webinar::$isDraft => 'مسودة',
                Webinar::$pending => 'قيد المراجعة',
                Webinar::$inactive => 'غير نشطة',
                default => null,
            };

            $subtitle = $webinar->category?->title ?? '';
            if (!empty($statusLabel)) {
                $subtitle = trim($subtitle . ($subtitle !== '' ? ' · ' : '') . $statusLabel);
            }

            $sessionsCount = $webinar->sessions->count();
            $filesCount = $webinar->files->count();
            $textsCount = $webinar->textLessons->count();
            $quizCount = (int) ($quizCounts[$webinar->id] ?? 0);
            $assignmentCount = (int) ($assignmentCounts[$webinar->id] ?? 0);
            $lectures = $sessionsCount + $filesCount + $textsCount + $quizCount;

            $durationMinutes = (int) ($webinar->duration ?? 0);
            if ($durationMinutes < 1 && $sessionsCount > 0) {
                $durationMinutes = (int) $webinar->sessions->sum('duration');
            }

            $activityHours = $durationMinutes > 0
                ? rtrim(rtrim(number_format($durationMinutes / 60, 1), '0'), '.') . ' س'
                : '—';

            return [
                'id' => $webinar->id,
                'title' => $webinar->title ?: 'دورة بدون عنوان',
                'subtitle' => $subtitle,
                'slug' => $webinar->slug,
                'thumbnail' => $webinar->thumbnail,
                'type_key' => $typeKey,
                'status' => $webinar->status,
                'type' => $typeLabels[$typeKey] ?? 'دورة',
                'activity' => $activityHours,
                'duration' => $durationMinutes > 0 ? $durationMinutes . ' دقيقة' : '—',
                'lectures' => $lectures,
                'assignments' => $assignmentCount,
                'progress' => $this->webinarAverageProgress($webinar),
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

        if ($avgProgress < 1) {
            $avgProgress = $this->webinarAverageProgress($webinar);
        }

        return [
            'perfStats' => [
                ['value' => count($students) . ' طالب', 'label' => 'طلاب الدورة', 'tone' => 'green'],
                ['value' => $avgProgress . '%', 'label' => 'متوسط التقدم', 'tone' => 'yellow'],
                ['value' => count($students) . ' مبيعات', 'label' => 'إجمالي المبيعات', 'tone' => 'green'],
            ],
            'students' => $students,
        ];
    }

    private function webinarAverageProgress($webinar): int
    {
        try {
            $avg = (int) round((float) $webinar->getAverageLearning());
            if ($avg > 0) {
                return min(100, max(0, $avg));
            }
        } catch (\Throwable $e) {
            // fall through
        }

        $buyerIds = Sale::query()
            ->where('webinar_id', $webinar->id)
            ->whereNull('refund_at')
            ->pluck('buyer_id')
            ->unique()
            ->filter()
            ->values();

        if ($buyerIds->isEmpty()) {
            return 0;
        }

        $assignmentIds = WebinarAssignment::query()->where('webinar_id', $webinar->id)->pluck('id');
        $quizIds = Quiz::query()->where('webinar_id', $webinar->id)->pluck('id');
        $scores = [];

        foreach ($buyerIds as $buyerId) {
            $parts = [];

            if ($assignmentIds->isNotEmpty()) {
                $done = WebinarAssignmentHistory::query()
                    ->whereIn('assignment_id', $assignmentIds)
                    ->where('student_id', $buyerId)
                    ->whereIn('status', ['passed', 'pending', 'not_passed'])
                    ->count();
                $parts[] = min(100, ($done / max(1, $assignmentIds->count())) * 100);
            }

            if ($quizIds->isNotEmpty()) {
                $done = \App\Models\QuizzesResult::query()
                    ->whereIn('quiz_id', $quizIds)
                    ->where('user_id', $buyerId)
                    ->count();
                $parts[] = min(100, ($done / max(1, $quizIds->count())) * 100);
            }

            $sessions = $webinar->relationLoaded('sessions') ? $webinar->sessions : $webinar->sessions()->get();
            if ($sessions->count() > 0) {
                $past = $sessions->filter(fn ($session) => (int) $session->date < time())->count();
                $parts[] = ($past / $sessions->count()) * 100;
            }

            $learned = \App\Models\CourseLearning::query()
                ->where('user_id', $buyerId)
                ->where(function ($q) use ($webinar) {
                    $q->whereIn('session_id', $webinar->sessions->pluck('id')->filter())
                        ->orWhereIn('file_id', $webinar->files->pluck('id')->filter())
                        ->orWhereIn('text_lesson_id', $webinar->textLessons->pluck('id')->filter());
                })
                ->count();
            $contentTotal = $webinar->sessions->count() + $webinar->files->count() + $webinar->textLessons->count();
            if ($contentTotal > 0) {
                $parts[] = min(100, ($learned / $contentTotal) * 100);
            }

            $scores[] = !empty($parts) ? (array_sum($parts) / count($parts)) : 0;
        }

        return (int) round(array_sum($scores) / max(1, count($scores)));
    }

    private function webinarFinishedProgress($webinar): int
    {
        return $this->webinarAverageProgress($webinar);
    }

    private function instructorNotificationsQuery($user)
    {
        $query = Notification::query()->where(function ($query) use ($user) {
            $query->where('notifications.user_id', $user->id)
                ->where('notifications.type', 'single');
        })->orWhere(function ($query) {
            $query->whereNull('notifications.user_id')
                ->whereNull('notifications.group_id')
                ->where('notifications.type', 'all_users');
        })->orWhere(function ($query) {
            $query->whereNull('notifications.user_id')
                ->whereNull('notifications.group_id')
                ->where('notifications.type', 'instructors');
        });

        $userGroup = $user->userGroup()->first();
        if (!empty($userGroup)) {
            $query->orWhere(function ($query) use ($userGroup) {
                $query->where('notifications.group_id', $userGroup->group_id)
                    ->where('notifications.type', 'group');
            });
        }

        return $query;
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
