<?php

namespace App\Http\Controllers\PanelV1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\Quiz;
use App\Models\Sale;
use App\Models\Session;
use App\Models\File;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function bundles(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundlesQuery = \App\Models\Bundle::query()
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            });

        $bundles = (clone $bundlesQuery)
            ->with([
                'category',
                'translations',
                'bundleWebinars.webinar',
                'sales' => function ($q) {
                    $q->where('type', 'bundle')->whereNull('refund_at');
                },
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $bundlesHours = 0;
        try {
            $bundlesHours = (float) \Illuminate\Support\Facades\DB::table('bundles')
                ->join('bundle_webinars', 'bundle_webinars.bundle_id', '=', 'bundles.id')
                ->join('webinars', 'webinars.id', '=', 'bundle_webinars.webinar_id')
                ->where(function ($q) use ($user) {
                    $q->where('bundles.teacher_id', $user->id)->orWhere('bundles.creator_id', $user->id);
                })
                ->sum('webinars.duration');
        } catch (\Throwable $e) {
            $bundlesHours = $bundles->sum(fn ($b) => method_exists($b, 'getBundleDuration') ? (float) $b->getBundleDuration() : 0);
        }

        $sales = \App\Models\Sale::query()
            ->where('seller_id', $user->id)
            ->where('type', 'bundle')
            ->whereNotNull('bundle_id')
            ->whereNull('refund_at')
            ->get();

        $cards = $bundles->map(function ($bundle) {
            $tr = $bundle->translate('ar')
                ?: $bundle->translate(app()->getLocale())
                ?: $bundle->translations->first();
            $status = $bundle->status ?? 'is_draft';
            $statusLabel = [
                'active' => 'منشورة',
                'pending' => 'قيد المراجعة',
                'is_draft' => 'مسودة',
                'inactive' => 'معطّلة',
            ][$status] ?? $status;

            $duration = method_exists($bundle, 'getBundleDuration') ? (float) $bundle->getBundleDuration() : 0;
            $image = null;
            try {
                $image = $bundle->getImage();
            } catch (\Throwable $e) {
                $image = $bundle->thumbnail;
            }

            return [
                'id' => $bundle->id,
                'title' => $tr->title ?? ('حزمة #' . $bundle->id),
                'summary' => $tr->summary ?? $tr->seo_description ?? '',
                'status' => $status,
                'status_label' => $statusLabel,
                'price' => $bundle->price,
                'price_label' => !empty($bundle->price) ? handlePrice($bundle->price) : 'مجانية',
                'courses_count' => $bundle->bundleWebinars->count(),
                'students_count' => $bundle->sales->count(),
                'sales_amount' => $bundle->sales->sum('amount'),
                'duration' => $duration,
                'duration_label' => convertMinutesToHourAndMinute($duration),
                'rate' => round(method_exists($bundle, 'getRate') ? (float) $bundle->getRate() : 0, 1),
                'rate_count' => method_exists($bundle, 'getRateCount') ? (int) $bundle->getRateCount() : 0,
                'thumbnail' => $image,
                'category' => $bundle->category->title ?? '—',
                'public_url' => !empty($bundle->slug) ? url('/bundles/' . $bundle->slug) : null,
                'edit_url' => route('panel.v1.instructor.bundles.edit', ['id' => $bundle->id]),
                'courses_url' => route('panel.v1.instructor.bundles.courses', ['id' => $bundle->id]),
                'preview_url' => route('panel.v1.instructor.bundles.preview', ['id' => $bundle->id]),
                'courses' => $bundle->bundleWebinars->map(function ($bw) {
                    $w = $bw->webinar;
                    if (!$w) {
                        return null;
                    }
                    $wTr = $w->translate('ar') ?: $w->translate(app()->getLocale()) ?: $w->translations->first();
                    return $wTr->title ?? ('دورة #' . $w->id);
                })->filter()->values()->all(),
            ];
        })->values()->all();

        $teacherCourses = Webinar::query()
            ->with('translations')
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->whereIn('status', [Webinar::$active, Webinar::$pending, 'active', 'pending'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(function ($w) {
                $tr = $w->translate('ar') ?: $w->translate(app()->getLocale()) ?: $w->translations->first();
                return [
                    'id' => $w->id,
                    'title' => $tr->title ?? ('دورة #' . $w->id),
                ];
            })->all();

        return $this->render($request, 'panel_v1.instructor.pages.bundles', 'حزم الدورات والباقات', [
            'bundleCards' => $cards,
            'bundleStats' => [
                'total' => $bundles->count(),
                'hours_label' => convertMinutesToHourAndMinute($bundlesHours),
                'sales_count' => $sales->count(),
                'sales_amount' => $sales->sum('amount'),
            ],
            'teacherCourses' => $teacherCourses,
        ]);
    }

    public function storeBundle(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'price' => 'nullable|integer|min:0',
            'webinar_ids' => 'nullable|array',
            'webinar_ids.*' => 'integer|exists:webinars,id',
            'publish' => 'nullable|boolean',
        ], [
            'required' => 'حقل :attribute مطلوب',
            'integer' => 'حقل :attribute يجب أن يكون رقمًا',
        ], [
            'title' => 'عنوان الحزمة',
            'summary' => 'الوصف المختصر',
            'price' => 'السعر',
            'webinar_ids' => 'الدورات',
        ]);

        $webinarIds = array_values(array_unique(array_map('intval', (array) $request->input('webinar_ids', []))));
        if (!empty($webinarIds)) {
            $owned = Webinar::query()
                ->whereIn('id', $webinarIds)
                ->where(function ($q) use ($user) {
                    $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
                })
                ->pluck('id')
                ->all();
            $webinarIds = array_values(array_intersect($webinarIds, $owned));
        }

        $title = trim((string) $request->input('title'));
        $slugBase = \Illuminate\Support\Str::slug($title);
        if ($slugBase === '') {
            $slugBase = 'bundle';
        }

        $bundle = new \App\Models\Bundle();
        $bundle->creator_id = $user->id;
        $bundle->teacher_id = $user->id;
        $bundle->slug = $slugBase . '-' . time();
        $bundle->price = $request->filled('price') ? (int) $request->input('price') : null;
        $bundle->status = $request->boolean('publish') ? \App\Models\Bundle::$pending : \App\Models\Bundle::$isDraft;
        $bundle->created_at = time();
        $bundle->updated_at = time();
        $bundle->save();

        foreach (array_unique(array_filter(['ar', app()->getLocale()])) as $loc) {
            $tr = $bundle->translateOrNew($loc);
            $tr->locale = $loc;
            $tr->title = $title;
            $tr->summary = $request->input('summary');
            $tr->seo_description = $request->input('summary');
            $tr->description = $request->input('summary');
            $tr->save();
        }

        foreach ($webinarIds as $order => $webinarId) {
            \App\Models\BundleWebinar::create([
                'creator_id' => $user->id,
                'bundle_id' => $bundle->id,
                'webinar_id' => $webinarId,
                'order' => $order + 1,
            ]);
        }

        return redirect()
            ->route('panel.v1.instructor.bundles')
            ->with('toast', [
                'title' => 'تم',
                'msg' => $request->boolean('publish') ? 'تم إنشاء الحزمة وإرسالها للمراجعة' : 'تم حفظ الحزمة كمسودة',
                'type' => 'success',
            ]);
    }

    public function deleteBundle(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = \App\Models\Bundle::query()
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->firstOrFail();

        $bundle->update([
            'status' => \App\Models\Bundle::$inactive,
            'updated_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.bundles')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم تعطيل الحزمة',
                'type' => 'success',
            ]);
    }

    public function editBundle(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = $this->teacherBundleOrFail($user, $id);
        $tr = $bundle->translate('ar') ?: $bundle->translate(app()->getLocale()) ?: $bundle->translations->first();

        $selectedIds = $bundle->bundleWebinars()->pluck('webinar_id')->all();
        $teacherCourses = Webinar::query()
            ->with('translations')
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->whereIn('status', [Webinar::$active, Webinar::$pending, Webinar::$isDraft, 'active', 'pending', 'is_draft'])
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(function ($w) use ($selectedIds) {
                $wTr = $w->translate('ar') ?: $w->translate(app()->getLocale()) ?: $w->translations->first();
                return [
                    'id' => $w->id,
                    'title' => $wTr->title ?? ('دورة #' . $w->id),
                    'selected' => in_array($w->id, $selectedIds, true),
                ];
            })->all();

        return $this->render($request, 'panel_v1.instructor.pages.bundle-edit', 'تعديل الحزمة', [
            'bundleEdit' => [
                'id' => $bundle->id,
                'title' => $tr->title ?? '',
                'seo_description' => $tr->seo_description ?? '',
                'summary' => $tr->summary ?? '',
                'description' => $tr->description ?? '',
                'price' => $bundle->price,
                'status' => $bundle->status,
                'thumbnail' => $bundle->thumbnail,
                'image_cover' => $bundle->image_cover,
                'video_demo' => $bundle->video_demo_source === 'external_link' ? $bundle->video_demo : null,
                'preview_url' => route('panel.v1.instructor.bundles.preview', ['id' => $bundle->id]),
                'courses_url' => route('panel.v1.instructor.bundles.courses', ['id' => $bundle->id]),
            ],
            'teacherCourses' => $teacherCourses,
        ]);
    }

    public function updateBundle(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = $this->teacherBundleOrFail($user, $id);

        $request->validate([
            'title' => 'required|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'summary' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'price' => 'nullable|integer|min:0',
            'status' => 'required|in:is_draft,pending,active,inactive',
            'video_demo' => 'nullable|url|max:2000',
            'thumbnail' => 'nullable|image|max:5120',
            'image_cover' => 'nullable|image|max:5120',
            'webinar_ids' => 'nullable|array',
            'webinar_ids.*' => 'integer|exists:webinars,id',
        ], [
            'required' => 'حقل :attribute مطلوب',
            'in' => 'قيمة :attribute غير صحيحة',
            'url' => 'حقل :attribute يجب أن يكون رابطًا صالحًا',
            'image' => 'حقل :attribute يجب أن يكون صورة',
        ], [
            'title' => 'عنوان الحزمة',
            'seo_description' => 'الوصف المختصر',
            'summary' => 'الملخص',
            'description' => 'الوصف التفصيلي',
            'price' => 'السعر',
            'status' => 'الحالة',
            'video_demo' => 'رابط الفيديو الترويجي',
            'thumbnail' => 'الصورة المصغرة',
            'image_cover' => 'غلاف الحزمة',
            'webinar_ids' => 'الدورات',
        ]);

        $webinarIds = array_values(array_unique(array_map('intval', (array) $request->input('webinar_ids', []))));
        if (!empty($webinarIds)) {
            $owned = Webinar::query()
                ->whereIn('id', $webinarIds)
                ->where(function ($q) use ($user) {
                    $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
                })
                ->pluck('id')
                ->all();
            $webinarIds = array_values(array_intersect($webinarIds, $owned));
        }

        if ($request->hasFile('thumbnail')) {
            $bundle->thumbnail = '/storage/' . $request->file('thumbnail')->store('bundles', 'public');
        }
        if ($request->hasFile('image_cover')) {
            $bundle->image_cover = '/storage/' . $request->file('image_cover')->store('bundles', 'public');
        }
        if ($request->filled('video_demo')) {
            $bundle->video_demo = $request->input('video_demo');
            $bundle->video_demo_source = 'external_link';
        }

        $bundle->price = $request->filled('price') ? (int) $request->input('price') : null;
        $bundle->status = $request->input('status');
        $bundle->updated_at = time();
        $bundle->save();

        $title = trim((string) $request->input('title'));
        foreach (array_unique(array_filter(['ar', app()->getLocale()])) as $loc) {
            $tr = $bundle->translateOrNew($loc);
            $tr->locale = $loc;
            $tr->title = $title;
            $tr->seo_description = $request->input('seo_description');
            $tr->summary = $request->input('summary');
            $tr->description = $request->input('description');
            $tr->save();
        }

        \App\Models\BundleWebinar::where('bundle_id', $bundle->id)->delete();
        foreach ($webinarIds as $order => $webinarId) {
            \App\Models\BundleWebinar::create([
                'creator_id' => $user->id,
                'bundle_id' => $bundle->id,
                'webinar_id' => $webinarId,
                'order' => $order + 1,
            ]);
        }

        return redirect()
            ->route('panel.v1.instructor.bundles.edit', ['id' => $bundle->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ تعديلات الحزمة', 'type' => 'success']);
    }

    public function bundleCourses(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = $this->teacherBundleOrFail($user, $id);
        $tr = $bundle->translate('ar') ?: $bundle->translate(app()->getLocale()) ?: $bundle->translations->first();

        $attachedIds = $bundle->bundleWebinars()->pluck('webinar_id')->all();
        $courses = Webinar::query()
            ->with(['translations', 'category'])
            ->whereIn('id', $attachedIds ?: [0])
            ->orderByDesc('id')
            ->get()
            ->map(function ($w) {
                $wTr = $w->translate('ar') ?: $w->translate(app()->getLocale()) ?: $w->translations->first();
                return [
                    'id' => $w->id,
                    'title' => $wTr->title ?? ('دورة #' . $w->id),
                    'thumbnail' => $w->thumbnail,
                    'status' => $w->status,
                    'category' => $w->category->title ?? '—',
                    'price_label' => !empty($w->price) ? handlePrice($w->price) : 'مجانية',
                    'watch_url' => !empty($w->slug) ? route('panel.v1.instructor.courses.watch', ['slug' => $w->slug]) : null,
                ];
            })->all();

        $availableCourses = Webinar::query()
            ->with('translations')
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->whereNotIn('id', $attachedIds ?: [0])
            ->whereIn('status', [Webinar::$active, Webinar::$pending, 'active', 'pending'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(function ($w) {
                $wTr = $w->translate('ar') ?: $w->translate(app()->getLocale()) ?: $w->translations->first();
                return [
                    'id' => $w->id,
                    'title' => $wTr->title ?? ('دورة #' . $w->id),
                ];
            })->all();

        return $this->render($request, 'panel_v1.instructor.pages.bundle-courses', 'دورات الحزمة', [
            'bundleMeta' => [
                'id' => $bundle->id,
                'title' => $tr->title ?? ('حزمة #' . $bundle->id),
                'edit_url' => route('panel.v1.instructor.bundles.edit', ['id' => $bundle->id]),
                'preview_url' => route('panel.v1.instructor.bundles.preview', ['id' => $bundle->id]),
            ],
            'bundleCourses' => $courses,
            'availableCourses' => $availableCourses,
        ]);
    }

    public function attachBundleCourse(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = $this->teacherBundleOrFail($user, $id);
        $request->validate([
            'webinar_id' => 'required|integer|exists:webinars,id',
        ], [], ['webinar_id' => 'الدورة']);

        $webinarId = (int) $request->input('webinar_id');
        $owned = Webinar::query()
            ->where('id', $webinarId)
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->exists();

        if (!$owned) {
            abort(404);
        }

        $exists = \App\Models\BundleWebinar::where('bundle_id', $bundle->id)->where('webinar_id', $webinarId)->exists();
        if (!$exists) {
            $order = (int) \App\Models\BundleWebinar::where('bundle_id', $bundle->id)->max('order') + 1;
            \App\Models\BundleWebinar::create([
                'creator_id' => $user->id,
                'bundle_id' => $bundle->id,
                'webinar_id' => $webinarId,
                'order' => $order,
            ]);
        }

        return redirect()
            ->route('panel.v1.instructor.bundles.courses', ['id' => $bundle->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة الدورة للحزمة', 'type' => 'success']);
    }

    public function detachBundleCourse(Request $request, int $id, int $webinarId)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = $this->teacherBundleOrFail($user, $id);
        \App\Models\BundleWebinar::where('bundle_id', $bundle->id)->where('webinar_id', $webinarId)->delete();

        return redirect()
            ->route('panel.v1.instructor.bundles.courses', ['id' => $bundle->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تم إزالة الدورة من الحزمة', 'type' => 'success']);
    }

    public function bundlePreview(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $bundle = $this->teacherBundleOrFail($user, $id);
        $bundle->load(['category', 'teacher', 'bundleWebinars.webinar.translations', 'translations']);
        $tr = $bundle->translate('ar') ?: $bundle->translate(app()->getLocale()) ?: $bundle->translations->first();

        $courses = $bundle->bundleWebinars->map(function ($bw) {
            $w = $bw->webinar;
            if (!$w) {
                return null;
            }
            $wTr = $w->translate('ar') ?: $w->translate(app()->getLocale()) ?: $w->translations->first();
            return [
                'id' => $w->id,
                'title' => $wTr->title ?? ('دورة #' . $w->id),
                'thumbnail' => $w->thumbnail,
                'summary' => $wTr->seo_description ?? '',
                'price_label' => !empty($w->price) ? handlePrice($w->price) : 'مجانية',
            ];
        })->filter()->values()->all();

        $salesCount = \App\Models\Sale::query()
            ->where('bundle_id', $bundle->id)
            ->where('type', 'bundle')
            ->whereNull('refund_at')
            ->count();

        return $this->render($request, 'panel_v1.instructor.pages.bundle-preview', 'عرض الحزمة', [
            'preview' => [
                'id' => $bundle->id,
                'title' => $tr->title ?? ('حزمة #' . $bundle->id),
                'summary' => $tr->summary ?? '',
                'description' => $tr->description ?? '',
                'seo_description' => $tr->seo_description ?? '',
                'cover' => $bundle->image_cover ?: $bundle->thumbnail,
                'thumbnail' => $bundle->thumbnail,
                'price_label' => !empty($bundle->price) ? handlePrice($bundle->price) : 'مجانية',
                'rate' => round(method_exists($bundle, 'getRate') ? (float) $bundle->getRate() : 0, 1),
                'rate_count' => method_exists($bundle, 'getRateCount') ? (int) $bundle->getRateCount() : 0,
                'students_count' => $salesCount,
                'courses_count' => count($courses),
                'duration_label' => convertMinutesToHourAndMinute(method_exists($bundle, 'getBundleDuration') ? $bundle->getBundleDuration() : 0),
                'category' => $bundle->category->title ?? null,
                'teacher_name' => $bundle->teacher->full_name ?? $user->full_name,
                'teacher_avatar' => method_exists($bundle->teacher ?? $user, 'getAvatar') ? ($bundle->teacher ?? $user)->getAvatar(80) : null,
                'public_url' => !empty($bundle->slug) ? url('/bundles/' . $bundle->slug) : null,
                'edit_url' => route('panel.v1.instructor.bundles.edit', ['id' => $bundle->id]),
                'courses_url' => route('panel.v1.instructor.bundles.courses', ['id' => $bundle->id]),
                'status' => $bundle->status,
                'courses' => $courses,
            ],
        ]);
    }

    private function teacherBundleOrFail($user, int $id): \App\Models\Bundle
    {
        return \App\Models\Bundle::query()
            ->with(['translations', 'bundleWebinars'])
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->firstOrFail();
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

    private function instructorCourseShell($webinar, ?Request $request = null, $progressUser = null, ?int $activeAssignmentId = null): array
    {
        $chapters = WebinarChapter::with(['sessions', 'files', 'textLessons', 'assignments'])
            ->where('webinar_id', $webinar->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $learnedFileIds = [];
        $learnedSessionIds = [];
        $learnedTextIds = [];
        $progress = 0;
        $completedLessons = 0;
        $totalLessons = 0;

        if (!empty($progressUser)) {
            $learned = \App\Models\CourseLearning::where('user_id', $progressUser->id)->get();
            $learnedFileIds = $learned->pluck('file_id')->filter()->map(fn ($id) => (int) $id)->all();
            $learnedSessionIds = $learned->pluck('session_id')->filter()->map(fn ($id) => (int) $id)->all();
            $learnedTextIds = $learned->pluck('text_lesson_id')->filter()->map(fn ($id) => (int) $id)->all();
            $progress = $this->studentLearningProgressPercent($webinar, $progressUser);
        }

        $requested = $request ? (string) $request->get('item', '') : '';
        $list = [];
        $activeChapterIndex = null;

        foreach ($chapters as $chapterIndex => $chapter) {
            $items = [];
            $completedInChapter = 0;

            foreach ($chapter->sessions->sortBy('id') as $session) {
                $key = 'session_' . $session->id;
                $done = in_array((int) $session->id, $learnedSessionIds, true);
                if ($done) {
                    $completedInChapter++;
                }
                $totalLessons++;
                if ($done) {
                    $completedLessons++;
                }
                $active = $requested !== '' ? $requested === $key : false;
                $items[] = [
                    'title' => $session->title ?: ('جلسة #' . $session->id),
                    'type' => 'video',
                    'kind' => 'session',
                    'key' => $key,
                    'active' => $active,
                    'completed' => $done,
                    'route' => 'panel.v1.instructor.courses.watch',
                    'url' => route('panel.v1.instructor.courses.watch', [
                        'slug' => $webinar->slug,
                        'item' => $key,
                    ]),
                ];
                if ($active) {
                    $activeChapterIndex = $chapterIndex;
                }
            }

            foreach ($chapter->files->sortBy('order') as $file) {
                $key = 'file_' . $file->id;
                $done = in_array((int) $file->id, $learnedFileIds, true);
                if ($done) {
                    $completedInChapter++;
                }
                $totalLessons++;
                if ($done) {
                    $completedLessons++;
                }
                $isVideo = ($file->file_type === 'video' || in_array($file->storage, ['youtube', 'vimeo'], true));
                $active = $requested !== '' ? $requested === $key : false;
                $items[] = [
                    'title' => $file->title ?: ('محتوى #' . $file->id),
                    'type' => $isVideo ? 'video' : 'file',
                    'kind' => 'file',
                    'key' => $key,
                    'active' => $active,
                    'completed' => $done,
                    'route' => 'panel.v1.instructor.courses.watch',
                    'url' => route('panel.v1.instructor.courses.watch', [
                        'slug' => $webinar->slug,
                        'item' => $key,
                    ]),
                ];
                if ($active) {
                    $activeChapterIndex = $chapterIndex;
                }
            }

            foreach ($chapter->textLessons->sortBy('id') as $text) {
                $key = 'text_' . $text->id;
                $done = in_array((int) $text->id, $learnedTextIds, true);
                if ($done) {
                    $completedInChapter++;
                }
                $totalLessons++;
                if ($done) {
                    $completedLessons++;
                }
                $active = $requested !== '' ? $requested === $key : false;
                $items[] = [
                    'title' => $text->title ?: ('نص #' . $text->id),
                    'type' => 'text',
                    'kind' => 'text',
                    'key' => $key,
                    'active' => $active,
                    'completed' => $done,
                    'route' => 'panel.v1.instructor.courses.watch',
                    'url' => route('panel.v1.instructor.courses.watch', [
                        'slug' => $webinar->slug,
                        'item' => $key,
                    ]),
                ];
                if ($active) {
                    $activeChapterIndex = $chapterIndex;
                }
            }

            $chapterAssignments = WebinarAssignment::where('webinar_id', $webinar->id)
                ->where('chapter_id', $chapter->id)
                ->orderBy('id')
                ->get();

            foreach ($chapterAssignments as $assignment) {
                $key = 'assignment_' . $assignment->id;
                $done = false;
                if (!empty($progressUser)) {
                    $done = WebinarAssignmentHistory::where('assignment_id', $assignment->id)
                        ->where('student_id', $progressUser->id)
                        ->whereIn('status', [
                            WebinarAssignmentHistory::$passed,
                            WebinarAssignmentHistory::$pending,
                            WebinarAssignmentHistory::$notPassed,
                        ])
                        ->exists();
                }
                if ($done) {
                    $completedInChapter++;
                }
                $totalLessons++;
                if ($done) {
                    $completedLessons++;
                }
                $active = $activeAssignmentId
                    ? ((int) $assignment->id === (int) $activeAssignmentId)
                    : ($requested !== '' && $requested === $key);
                $items[] = [
                    'title' => $assignment->title ?: ('تكليف #' . $assignment->id),
                    'type' => 'assignment',
                    'kind' => 'assignment',
                    'key' => $key,
                    'active' => $active,
                    'completed' => $done,
                    'route' => 'panel.v1.instructor.courses.assignments',
                    'url' => route('panel.v1.instructor.courses.assignments', ['slug' => $webinar->slug]),
                ];
                if ($active) {
                    $activeChapterIndex = $chapterIndex;
                }
            }

            $itemCount = count($items);
            $list[] = [
                'title' => $chapter->title ?: ('الوحدة ' . ($chapterIndex + 1)),
                'subtitle' => $items[0]['title'] ?? 'محتوى الوحدة',
                'completed' => $itemCount > 0 && $completedInChapter >= $itemCount,
                'expanded' => false,
                'completed_count' => $completedInChapter,
                'items_count' => $itemCount,
                'items' => $items,
            ];
        }

        if (empty($list)) {
            $list[] = [
                'title' => 'المحاضرة الأولى',
                'completed' => false,
                'expanded' => true,
                'subtitle' => 'لا يوجد محتوى بعد',
                'completed_count' => 0,
                'items_count' => 0,
                'items' => [],
            ];
        } else {
            $expandIndex = $activeChapterIndex ?? 0;
            foreach ($list as $i => &$row) {
                $row['expanded'] = ((int) $i === (int) $expandIndex);
            }
            unset($row);
        }

        $progressLabel = !empty($progressUser)
            ? ('إنجاز ' . ($progressUser->full_name ?? 'الطالب'))
            : 'نسبة الإنجاز';

        return [
            'slug' => $webinar->slug,
            'course' => [
                'title' => $webinar->title,
                'subtitle' => $webinar->category->title ?? '',
                'progress' => $progress,
                'progress_label' => $progressLabel,
            ],
            'chapters' => $list,
            'studentProgressMeta' => [
                'progress' => $progress,
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'student_name' => $progressUser->full_name ?? null,
                'student_id' => $progressUser->id ?? null,
            ],
        ];
    }

    private function studentLearningProgressPercent($webinar, $student): int
    {
        if (empty($student)) {
            return 0;
        }

        $userId = (int) $student->id;
        $filesStat = $webinar->getFilesLearningProgressStat($userId);
        $sessionsStat = $webinar->getSessionsLearningProgressStat($userId);
        $textLessonsStat = $webinar->getTextLessonsLearningProgressStat($userId);
        $assignmentsStat = $webinar->getAssignmentsLearningProgressStat($userId);
        $quizzesStat = $webinar->getQuizzesLearningProgressStat($userId);

        $passed = ($filesStat['passed'] ?? 0)
            + ($sessionsStat['passed'] ?? 0)
            + ($textLessonsStat['passed'] ?? 0)
            + ($assignmentsStat['passed'] ?? 0)
            + ($quizzesStat['passed'] ?? 0);
        $count = ($filesStat['count'] ?? 0)
            + ($sessionsStat['count'] ?? 0)
            + ($textLessonsStat['count'] ?? 0)
            + ($assignmentsStat['count'] ?? 0)
            + ($quizzesStat['count'] ?? 0);

        if ($count < 1 || $passed < 1) {
            return 0;
        }

        return (int) max(0, min(100, round(($passed * 100) / $count)));
    }

    public function courseAssignment(Request $request, string $slug)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        $assignmentIds = WebinarAssignment::where('webinar_id', $webinar->id)->pluck('id');

        $pending = WebinarAssignmentHistory::whereIn('assignment_id', $assignmentIds)
            ->where('status', WebinarAssignmentHistory::$pending)
            ->orderBy('id')
            ->first();

        if ($pending) {
            return redirect()->route('panel.v1.instructor.assignments.review', ['id' => $pending->id]);
        }

        return redirect()->route('panel.v1.instructor.courses.assignments', ['slug' => $webinar->slug]);
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
        if (!$guardUser) {
            return redirect('/login');
        }

        $webinar = $this->teacherWebinarOrFail($guardUser, $slug);
        $assignments = WebinarAssignment::where('webinar_id', $webinar->id)->orderByDesc('id')->get();
        $assignmentIds = $assignments->pluck('id');

        $histories = $assignmentIds->isEmpty()
            ? collect()
            : WebinarAssignmentHistory::with(['student', 'assignment', 'messages'])
                ->whereIn('assignment_id', $assignmentIds)
                ->where('status', '!=', WebinarAssignmentHistory::$notSubmitted)
                ->orderByDesc('id')
                ->limit(50)
                ->get();

        $total = $histories->count();
        $passed = $histories->where('status', WebinarAssignmentHistory::$passed)->count();
        $pending = $histories->where('status', WebinarAssignmentHistory::$pending)->count();
        $rate = $total > 0 ? (int) round($passed / $total * 100) : 0;

        return $this->render(
            $request,
            'panel_v1.instructor.pages.course-assignments',
            'متطلبات الدورات',
            [
                'webinar' => $webinar,
                'courseSlug' => $webinar->slug,
                'slug' => $webinar->slug,
                'pageTitleMain' => 'متطلبات دورة ' . $webinar->title,
                'pageSubtitle' => $webinar->category->title ?? '',
                'summaryCards' => [
                    ['label' => 'إجمالي التسليمات', 'value' => (string) $total, 'edge' => '#0f4c45', 'valueClass' => 'text-primary'],
                    ['label' => 'التسليمات المجتازة', 'value' => (string) $passed, 'edge' => '#0FC787', 'valueClass' => 'text-[#0FC787]'],
                    ['label' => 'قيد المراجعة', 'value' => (string) $pending, 'edge' => '#F59E0B', 'valueClass' => 'text-[#F59E0B]'],
                    ['label' => 'معدل النجاح', 'value' => $rate . '%', 'edge' => '#6366F1', 'valueClass' => 'text-[#6366F1]'],
                ],
                'submissions' => $histories->map(function ($history) {
                    $statusMeta = $this->assignmentHistoryStatusMeta($history->status);
                    $latestMessageAt = $history->messages->max('created_at');
                    $attemptCount = max(1, $history->messages->where('sender_id', $history->student_id)->count());
                    $maxAttempts = $history->assignment->attempts ?? null;

                    return [
                        'history_id' => $history->id,
                        'review_url' => route('panel.v1.instructor.assignments.review', ['id' => $history->id]),
                        'name' => $history->student->full_name ?? 'طالب',
                        'joined_at' => $this->formatAssignmentDate($history->created_at),
                        'latest_at' => $this->formatAssignmentDate($latestMessageAt ?: $history->created_at),
                        'last_at' => $this->formatAssignmentDate($latestMessageAt ?: $history->created_at),
                        'attempts' => $attemptCount . ' / ' . ($maxAttempts ?: '—'),
                        'grade' => ($history->grade ?? '—') . ' / ' . ($history->assignment->grade ?? '—'),
                        'status' => $statusMeta['label'],
                        'status_tone' => $statusMeta['tone'],
                    ];
                })->all(),
            ]
        );
    }

    public function assignments(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinarIds = $this->teacherWebinars($user)->pluck('id')->all();

        $assignments = empty($webinarIds)
            ? collect()
            : WebinarAssignment::with(['webinar'])
                ->withCount([
                    'instructorAssignmentHistories as submissions_count',
                    'instructorAssignmentHistories as pending_count' => function ($q) {
                        $q->where('status', WebinarAssignmentHistory::$pending);
                    },
                    'instructorAssignmentHistories as passed_count' => function ($q) {
                        $q->where('status', WebinarAssignmentHistory::$passed);
                    },
                    'instructorAssignmentHistories as failed_count' => function ($q) {
                        $q->where('status', WebinarAssignmentHistory::$notPassed);
                    },
                    'instructorAssignmentHistories as graded_count' => function ($q) {
                        $q->whereIn('status', [
                            WebinarAssignmentHistory::$passed,
                            WebinarAssignmentHistory::$notPassed,
                        ]);
                    },
                ])
                ->whereIn('webinar_id', $webinarIds)
                ->orderByDesc('id')
                ->get();

        $assignmentIds = $assignments->pluck('id')->all();

        $salesByWebinar = empty($webinarIds)
            ? collect()
            : Sale::whereIn('webinar_id', $webinarIds)
                ->whereNull('refund_at')
                ->selectRaw('webinar_id, COUNT(*) as c')
                ->groupBy('webinar_id')
                ->pluck('c', 'webinar_id');

        $firstPendingByAssignment = empty($assignmentIds)
            ? collect()
            : WebinarAssignmentHistory::whereIn('assignment_id', $assignmentIds)
                ->where('status', WebinarAssignmentHistory::$pending)
                ->orderBy('id')
                ->get()
                ->groupBy('assignment_id')
                ->map(fn ($group) => $group->first());

        $histories = empty($assignmentIds)
            ? collect()
            : WebinarAssignmentHistory::with(['assignment.webinar', 'student', 'messages'])
                ->whereIn('assignment_id', $assignmentIds)
                ->where('status', '!=', WebinarAssignmentHistory::$notSubmitted)
                ->orderByDesc('id')
                ->limit(50)
                ->get();

        $pendingTotal = (int) $assignments->sum('pending_count');
        $gradedTotal = (int) $assignments->sum('graded_count');
        $submissionsTotal = (int) $assignments->sum('submissions_count');

        $currentAssignments = $assignments->take(6)->map(function ($assignment) use ($salesByWebinar, $firstPendingByAssignment) {
            $slug = $assignment->webinar->slug ?? null;
            $studentsCount = (int) ($salesByWebinar[$assignment->webinar_id] ?? 0);
            $submissions = (int) ($assignment->submissions_count ?? 0);
            $pending = (int) ($assignment->pending_count ?? 0);
            $graded = (int) ($assignment->graded_count ?? 0);
            $progress = $studentsCount > 0 ? (int) min(100, round(($submissions / $studentsCount) * 100)) : 0;
            $courseAssignmentsUrl = $slug
                ? route('panel.v1.instructor.courses.assignments', ['slug' => $slug])
                : route('panel.v1.instructor.assignments');
            $pendingHistory = $firstPendingByAssignment->get($assignment->id);
            $reviewUrl = $pendingHistory
                ? route('panel.v1.instructor.assignments.review', ['id' => $pendingHistory->id])
                : $courseAssignmentsUrl;

            return [
                'id' => $assignment->id,
                'title' => $assignment->title ?: 'تكليف',
                'course' => $assignment->webinar->title ?? '',
                'slug' => $slug,
                'deadline' => !empty($assignment->deadline)
                    ? ((int) $assignment->deadline) . ' يوم من الشراء'
                    : 'غير محدود',
                'submissions' => $submissions . ' / ' . $studentsCount,
                'pending' => $pending . ' طالب',
                'graded' => $graded . ' طالب',
                'progress' => $progress,
                'points' => (int) ($assignment->grade ?? 0),
                'badge' => $pending,
                'cta' => 'عرض التسليمات',
                'review_url' => $reviewUrl,
                'course_assignments_url' => $courseAssignmentsUrl,
                'preview_url' => $courseAssignmentsUrl,
                'edit_url' => $slug
                    ? route('panel.v1.instructor.courses.watch', ['slug' => $slug])
                    : route('panel.v1.instructor.courses'),
            ];
        })->values()->all();

        $resultsRows = $assignments->map(function ($assignment) use ($firstPendingByAssignment) {
            $slug = $assignment->webinar->slug ?? null;
            $courseAssignmentsUrl = $slug
                ? route('panel.v1.instructor.courses.assignments', ['slug' => $slug])
                : route('panel.v1.instructor.assignments');
            $pendingHistory = $firstPendingByAssignment->get($assignment->id);
            $pendingUrl = $pendingHistory
                ? route('panel.v1.instructor.assignments.review', ['id' => $pendingHistory->id])
                : $courseAssignmentsUrl;

            return [
                'id' => $assignment->id,
                'title' => $assignment->title ?: 'تكليف',
                'course' => $assignment->webinar->title ?? '',
                'slug' => $slug,
                'grade' => (int) ($assignment->grade ?? 0),
                'passGrade' => (int) ($assignment->pass_grade ?? 0),
                'submissions' => (int) ($assignment->submissions_count ?? 0),
                'pending' => (int) ($assignment->pending_count ?? 0),
                'passed' => (int) ($assignment->passed_count ?? 0),
                'failed' => (int) ($assignment->failed_count ?? 0),
                'deadline' => !empty($assignment->deadline)
                    ? ((int) $assignment->deadline) . ' يوم من الشراء'
                    : '—',
                'status' => ($assignment->status ?? 'active') === 'active' ? 'نشط' : 'غير نشط',
                'status_tone' => ($assignment->status ?? 'active') === 'active' ? 'success' : 'muted',
                'pending_url' => $pendingUrl,
                'course_assignments_url' => $courseAssignmentsUrl,
                'edit_url' => $slug
                    ? route('panel.v1.instructor.courses.watch', ['slug' => $slug])
                    : route('panel.v1.instructor.courses'),
                'course_url' => $slug
                    ? route('panel.v1.instructor.courses.performance', ['slug' => $slug])
                    : route('panel.v1.instructor.courses'),
            ];
        })->values()->all();

        $studentResultsRows = $histories->map(function ($history) {
            $statusMeta = $this->assignmentHistoryStatusMeta($history->status);
            $latestMessageAt = $history->messages->max('created_at');
            $attemptCount = max(
                $history->status === WebinarAssignmentHistory::$notSubmitted ? 0 : 1,
                $history->messages->where('sender_id', $history->student_id)->count()
            );
            $maxAttempts = $history->assignment->attempts ?? null;
            $createdTs = (int) ($history->created_at ?: 0);

            return [
                'history_id' => $history->id,
                'review_url' => route('panel.v1.instructor.assignments.review', ['id' => $history->id]),
                'name' => $history->student->full_name ?? 'طالب',
                'title' => $history->assignment->title ?? 'تكليف',
                'course' => $history->assignment->webinar->title ?? '',
                'first_at' => $this->formatAssignmentDate($history->created_at),
                'last_at' => $this->formatAssignmentDate($latestMessageAt ?: $history->created_at),
                'attempts' => $attemptCount . ' / ' . ($maxAttempts ?: '—'),
                'grade' => $history->grade !== null
                    ? ((int) $history->grade) . ' / ' . ((int) ($history->assignment->grade ?? 0))
                    : '—',
                'created_day' => $createdTs > 0 ? date('d', $createdTs) : '—',
                'created_month' => $createdTs > 0 ? $this->formatAssignmentMonthYear($createdTs) : '—',
                'status' => $statusMeta['label'],
                'status_tone' => $statusMeta['tone'],
            ];
        })->values()->all();

        $assignmentCourses = $this->teacherWebinars($user)->map(function ($webinar) {
            $chapters = WebinarChapter::where('webinar_id', $webinar->id)
                ->orderBy('order')
                ->orderBy('id')
                ->get()
                ->map(fn ($chapter) => [
                    'id' => $chapter->id,
                    'title' => $chapter->title ?: ('وحدة #' . $chapter->id),
                ])
                ->values()
                ->all();

            return [
                'id' => $webinar->id,
                'title' => $webinar->title ?: ('دورة #' . $webinar->id),
                'chapters' => $chapters,
            ];
        })->values()->all();

        return $this->render($request, 'panel_v1.instructor.pages.assignments', 'إدارة الواجبات والتكليفات', [
            'assignmentStats' => [
                ['value' => $pendingTotal . ' تكليف', 'label' => 'بانتظار التصحيح'],
                ['value' => $gradedTotal . ' تكليف', 'label' => 'تم تصحيحها'],
                ['value' => $submissionsTotal . ' تكليف', 'label' => 'تسليم'],
            ],
            'currentAssignments' => $currentAssignments,
            'resultsRows' => $resultsRows,
            'studentResultsRows' => $studentResultsRows,
            'assignmentCourses' => $assignmentCourses,
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $data = $request->validate([
            'webinar_id' => 'required|integer',
            'chapter_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'grade' => 'required|integer|min:1|max:1000',
            'pass_grade' => 'required|integer|min:0|max:1000',
            'deadline' => 'nullable|integer|min:1|max:365',
            'attempts' => 'nullable|integer|min:1|max:50',
        ], [
            'webinar_id.required' => 'اختر الدورة',
            'title.required' => 'عنوان التكليف مطلوب',
            'description.required' => 'وصف التكليف مطلوب',
            'grade.required' => 'الدرجة العظمى مطلوبة',
            'pass_grade.required' => 'درجة النجاح مطلوبة',
        ]);

        if ((int) $data['pass_grade'] > (int) $data['grade']) {
            throw ValidationException::withMessages([
                'pass_grade' => 'درجة النجاح لا يمكن أن تتجاوز الدرجة العظمى',
            ]);
        }

        $webinar = Webinar::where('id', $data['webinar_id'])
            ->where('teacher_id', $user->id)
            ->firstOrFail();

        $chapterId = !empty($data['chapter_id']) ? (int) $data['chapter_id'] : null;
        if ($chapterId) {
            $chapter = WebinarChapter::where('id', $chapterId)
                ->where('webinar_id', $webinar->id)
                ->first();
            if (empty($chapter)) {
                throw ValidationException::withMessages([
                    'chapter_id' => 'الوحدة المحددة غير صحيحة',
                ]);
            }
        } else {
            $chapter = WebinarChapter::where('webinar_id', $webinar->id)
                ->orderBy('order')
                ->orderBy('id')
                ->first();

            if (empty($chapter)) {
                $chapter = WebinarChapter::create([
                    'user_id' => $user->id,
                    'webinar_id' => $webinar->id,
                    'order' => 1,
                    'status' => WebinarChapter::$chapterActive,
                    'created_at' => time(),
                ]);

                WebinarChapterTranslation::updateOrCreate(
                    [
                        'webinar_chapter_id' => $chapter->id,
                        'locale' => mb_strtolower(app()->getLocale() ?: getDefaultLocale()),
                    ],
                    ['title' => 'الوحدة الأولى']
                );
            }

            $chapterId = (int) $chapter->id;
        }

        $assignment = WebinarAssignment::create([
            'creator_id' => $user->id,
            'webinar_id' => $webinar->id,
            'chapter_id' => $chapterId,
            'grade' => (int) $data['grade'],
            'pass_grade' => (int) $data['pass_grade'],
            'deadline' => !empty($data['deadline']) ? (int) $data['deadline'] : null,
            'attempts' => !empty($data['attempts']) ? (int) $data['attempts'] : null,
            'check_previous_parts' => false,
            'access_after_day' => null,
            'status' => File::$Active,
            'created_at' => time(),
        ]);

        WebinarAssignmentTranslation::updateOrCreate(
            [
                'webinar_assignment_id' => $assignment->id,
                'locale' => mb_strtolower(app()->getLocale() ?: getDefaultLocale()),
            ],
            [
                'title' => $data['title'],
                'description' => $data['description'],
            ]
        );

        WebinarChapterItem::makeItem($user->id, $chapterId, $assignment->id, WebinarChapterItem::$chapterAssignment);

        $webinar->update(['updated_at' => time()]);

        return redirect()
            ->route('panel.v1.instructor.assignments')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إنشاء التكليف بنجاح',
                'type' => 'success',
            ]);
    }

    public function assignmentReview(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $history = WebinarAssignmentHistory::with(['assignment.webinar', 'student', 'messages'])
            ->findOrFail($id);

        $webinar = $history->assignment->webinar ?? null;
        if (empty($webinar) || (int) $webinar->teacher_id !== (int) $guardUser->id) {
            abort(404);
        }

        $studentMessages = $history->messages
            ->where('sender_id', $history->student_id)
            ->sortBy('id')
            ->values();

        $answerParagraphs = $studentMessages
            ->pluck('message')
            ->filter(fn ($msg) => filled(trim((string) $msg)))
            ->values()
            ->all();

        $attachment = $studentMessages->first(fn ($msg) => filled($msg->file_path) || filled($msg->file_title));
        $attachmentUrl = null;
        $attachmentSize = null;
        if ($attachment && filled($attachment->file_path)) {
            $publicPath = public_path($attachment->file_path);
            if (is_file($publicPath)) {
                $attachmentUrl = asset($attachment->file_path);
                $bytes = filesize($publicPath);
                if ($bytes !== false) {
                    $attachmentSize = $bytes >= 1048576
                        ? round($bytes / 1048576, 1) . ' MB'
                        : max(1, (int) round($bytes / 1024)) . ' KB';
                }
            } else {
                $attachmentUrl = url($attachment->getDownloadUrl($history->assignment_id));
            }
        }

        $description = trim(strip_tags((string) ($history->assignment->description ?? '')));
        $student = $history->student;
        $shell = $this->instructorCourseShell(
            $webinar,
            $request,
            $student,
            (int) ($history->assignment_id ?? 0)
        );
        $statusMeta = $this->assignmentHistoryStatusMeta($history->status);
        $progressMeta = $shell['studentProgressMeta'] ?? [];

        return $this->render(
            $request,
            'panel_v1.instructor.pages.assignment-review',
            'تقييم التكليف',
            array_merge($shell, [
                'webinar' => $webinar,
                'courseSlug' => $webinar->slug,
                'historyId' => $history->id,
                'historyStatus' => $history->status,
                'historyGrade' => $history->grade,
                'historyStatusLabel' => $statusMeta['label'],
                'reviewStudentName' => $student->full_name ?? 'طالب',
                'studentAnswerParagraphs' => $answerParagraphs,
                'studentAnswerPoints' => [],
                'attachmentName' => $attachment->file_title ?? null,
                'attachmentUrl' => $attachmentUrl,
                'attachmentSize' => $attachmentSize,
                'attachmentScan' => $attachmentUrl ? 'تم الفحص' : null,
                'maxGrade' => (int) ($history->assignment->grade ?? 50),
                'passGrade' => (int) ($history->assignment->pass_grade ?? 25),
                'reviewTitle' => 'تقييم تكليف: ' . ($history->assignment->title ?: 'تكليف'),
                'detailsTitle' => $history->assignment->title ?: 'تفاصيل التكليف',
                'detailsBody' => $description !== '' ? $description : 'لا يوجد وصف لهذا التكليف.',
                'pointsTitle' => 'معايير التقييم',
                'points' => [
                    'درجة النجاح: ' . ((int) ($history->assignment->pass_grade ?? 0)),
                    'الدرجة العظمى: ' . ((int) ($history->assignment->grade ?? 0)),
                    !empty($history->assignment->deadline)
                        ? 'الموعد النهائي: ' . ((int) $history->assignment->deadline) . ' يوم من الشراء'
                        : 'الموعد النهائي: غير محدود',
                    'عدد المحاولات المسموحة: ' . ((int) ($history->assignment->attempts ?? 1)),
                ],
                'assignmentId' => $history->assignment->id,
                'canGrade' => in_array($history->status, [
                    WebinarAssignmentHistory::$pending,
                    WebinarAssignmentHistory::$passed,
                    WebinarAssignmentHistory::$notPassed,
                ], true),
                'studentProgressCards' => [
                    [
                        'label' => 'نسبة إنجاز الطالب',
                        'value' => ((int) ($progressMeta['progress'] ?? 0)) . '%',
                        'tone' => 'primary',
                    ],
                    [
                        'label' => 'المحاضرات المكتملة',
                        'value' => ((int) ($progressMeta['completed_lessons'] ?? 0))
                            . ' / '
                            . ((int) ($progressMeta['total_lessons'] ?? 0)),
                        'tone' => 'green',
                    ],
                    [
                        'label' => 'حالة التكليف',
                        'value' => $statusMeta['label'],
                        'tone' => 'amber',
                    ],
                ],
            ])
        );
    }

    public function gradeAssignment(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $history = WebinarAssignmentHistory::with(['assignment.webinar'])
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
        $history->status = $grade >= $passGrade
            ? WebinarAssignmentHistory::$passed
            : WebinarAssignmentHistory::$notPassed;
        $history->save();

        return redirect()
            ->route('panel.v1.instructor.assignments.review', ['id' => $history->id])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم اعتماد درجة الطالب بنجاح',
                'type' => 'success',
            ]);
    }

    private function assignmentHistoryStatusMeta(?string $status): array
    {
        return match ($status) {
            WebinarAssignmentHistory::$passed => [
                'label' => 'مجتاز',
                'tone' => 'success',
            ],
            WebinarAssignmentHistory::$notPassed => [
                'label' => 'راسب',
                'tone' => 'danger',
            ],
            WebinarAssignmentHistory::$notSubmitted => [
                'label' => 'لم يُسلّم',
                'tone' => 'muted',
            ],
            default => [
                'label' => 'بانتظار التصحيح',
                'tone' => 'warning',
            ],
        };
    }

    private function formatAssignmentDate($timestamp): string
    {
        $ts = (int) $timestamp;
        if ($ts <= 0) {
            return '—';
        }

        return date('Y/m/d', $ts);
    }

    private function formatAssignmentMonthYear(int $timestamp): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
        $month = (int) date('n', $timestamp);

        return ($months[$month] ?? date('F', $timestamp)) . ' ' . date('Y', $timestamp);
    }

    public function consultations(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $meeting = \App\Models\Meeting::where('creator_id', $user->id)->first();
        $reservations = $this->instructorReserveMeetingsQuery($user)
            ->with(['user', 'meetingTime', 'session', 'sale'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $rows = $reservations->map(fn ($reservation) => $this->mapConsultationRow($reservation))->values()->all();

        $now = time();
        $upcoming = $this->instructorReserveMeetingsQuery($user)
            ->with(['meetingTime', 'user', 'session'])
            ->whereIn('status', [
                \App\Models\ReserveMeeting::$open,
                \App\Models\ReserveMeeting::$pending,
            ])
            ->where(function ($q) use ($now) {
                $q->where('date', '>=', $now)->orWhere('start_at', '>=', $now);
            })
            ->orderByRaw('COALESCE(start_at, date) asc')
            ->first();

        $session = [];
        if ($upcoming) {
            $mapped = $this->mapConsultationRow($upcoming);
            $slot = $upcoming->meetingTime;
            $session = [
                'id' => $upcoming->id,
                'title' => $slot->description ?: 'جلسة استشارية',
                'status' => $upcoming->status === \App\Models\ReserveMeeting::$pending ? 'قيد الانتظار' : 'مجدولة',
                'status_key' => $upcoming->status,
                'price' => handlePrice($upcoming->paid_amount ?: ($meeting->amount ?? 0)),
                'instructor' => $user->full_name,
                'instructorInitials' => mb_substr($user->full_name ?? '?', 0, 1),
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'linkLabel' => $upcoming->meeting_type === 'in_person' ? 'لقاء حضوري' : 'لقاء أونلاين',
                'link' => $mapped['link'],
                'detail_url' => $mapped['detail_url'],
                'join_url' => $mapped['join_url'],
                'session_url' => $mapped['session_url'],
                'can_create_session' => $mapped['can_create_session'],
                'link_raw' => $mapped['link_raw'],
                'agora_enabled' => $mapped['agora_enabled'],
                'join_label' => $mapped['join_label'],
            ];
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.consultations',
            'الجلسات الاستشارية',
            [
                'attendees' => $rows,
                'session' => $session,
                'settingsUrl' => url('/panel/meetings/settings'),
                'agoraEnabled' => !empty(getFeaturesSettings('agora_for_meeting')),
            ]
        );
    }

    public function consultationShow(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $reservation = $this->instructorReserveMeetingOrFail($user, $id);
        $row = $this->mapConsultationRow($reservation);
        $slot = $reservation->meetingTime;

        return $this->render(
            $request,
            'panel_v1.instructor.pages.consultation-show',
            'تفاصيل الجلسة',
            [
                'detail' => array_merge($row, [
                    'title' => $slot->description ?: 'جلسة استشارية',
                    'phone' => $reservation->user->mobile ?? null,
                    'password' => $reservation->password,
                    'description' => $reservation->description,
                    'start_label' => !empty($reservation->start_at) ? date('Y/m/d H:i', (int) $reservation->start_at) : $row['date'] . ' ' . $row['time'],
                    'end_label' => !empty($reservation->end_at) ? date('H:i', (int) $reservation->end_at) : null,
                    'calendar_url' => method_exists($reservation, 'addToCalendarLink') ? $reservation->addToCalendarLink() : null,
                ]),
            ]
        );
    }

    public function consultationJoin(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $reservation = $this->instructorReserveMeetingOrFail($user, $id);

        if ($reservation->meeting_type === 'in_person') {
            return back()->with('toast', [
                'title' => 'تنبيه',
                'msg' => 'هذه جلسة حضورية ولا يوجد رابط انضمام',
                'type' => 'error',
            ]);
        }

        if (in_array($reservation->status, [
            \App\Models\ReserveMeeting::$finished,
            \App\Models\ReserveMeeting::$canceled,
        ], true)) {
            return back()->with('toast', [
                'title' => 'تنبيه',
                'msg' => 'لا يمكن الانضمام لجلسة منتهية أو ملغاة',
                'type' => 'error',
            ]);
        }

        // Prefer in-app Agora (Rocket live meeting) whenever enabled.
        if (!empty(getFeaturesSettings('agora_for_meeting'))) {
            if (empty($reservation->session) || ($reservation->session->session_api ?? null) !== 'agora') {
                $this->createConsultationAgoraSession($reservation, $user);
            }

            $reservation->update([
                'status' => \App\Models\ReserveMeeting::$open,
                'link' => null,
            ]);

            $session = $reservation->fresh(['session'])->session;
            if ($session) {
                return redirect(url($session->getJoinLink()));
            }
        }

        $target = $this->resolveConsultationJoinTarget($reservation->fresh(['session']));

        if (empty($target)) {
            return redirect()
                ->route('panel.v1.instructor.consultations.show', ['id' => $reservation->id])
                ->with('toast', [
                    'title' => 'تنبيه',
                    'msg' => 'تعذّر بدء الجلسة المباشرة — تحقق من إعدادات Agora',
                    'type' => 'error',
                ]);
        }

        return \Illuminate\Support\Facades\Redirect::away($target);
    }

    public function consultationCreateSession(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $reservation = $this->instructorReserveMeetingOrFail($user, $id);

        if ($reservation->meeting_type === 'in_person') {
            return back()->with('toast', [
                'title' => 'تنبيه',
                'msg' => 'لا يمكن إنشاء رابط لجلسة حضورية',
                'type' => 'error',
            ]);
        }

        if (in_array($reservation->status, [
            \App\Models\ReserveMeeting::$finished,
            \App\Models\ReserveMeeting::$canceled,
        ], true)) {
            return back()->with('toast', [
                'title' => 'تنبيه',
                'msg' => 'لا يمكن تعديل جلسة منتهية أو ملغاة',
                'type' => 'error',
            ]);
        }

        $agoraEnabled = !empty(getFeaturesSettings('agora_for_meeting'));
        $allowedTypes = $agoraEnabled ? 'agora,external' : 'external';

        $request->validate([
            'session_type' => 'required|in:' . $allowedTypes,
            'url' => 'required_if:session_type,external|nullable|url|max:2000',
            'password' => 'nullable|string|max:100',
        ], [
            'required' => 'حقل :attribute مطلوب',
            'required_if' => 'حقل :attribute مطلوب',
            'url' => 'أدخل رابط لقاء صالح (Zoom / Google Meet / …)',
            'in' => 'نوع الجلسة غير صحيح',
        ], [
            'session_type' => 'نوع الجلسة',
            'url' => 'رابط اللقاء',
            'password' => 'كلمة المرور',
        ]);

        $sessionType = $request->input('session_type');

        if ($sessionType === 'agora') {
            $this->createConsultationAgoraSession($reservation, $user);
            $reservation->update(['status' => \App\Models\ReserveMeeting::$open, 'link' => null]);

            $session = $reservation->fresh()->session;
            $join = $session && method_exists($session, 'getJoinLink')
                ? url($session->getJoinLink())
                : route('panel.v1.instructor.consultations.join', ['id' => $reservation->id]);

            return \Illuminate\Support\Facades\Redirect::away($join);
        }

        $url = trim((string) $request->input('url'));
        if (!$this->isUsableMeetingLink($url)) {
            return back()
                ->withInput()
                ->with('toast', [
                    'title' => 'تنبيه',
                    'msg' => 'استخدم رابط Zoom أو Google Meet حقيقيًا — روابط example.com غير مقبولة',
                    'type' => 'error',
                ]);
        }

        $reservation->update([
            'link' => $url,
            'password' => $request->input('password'),
            'status' => \App\Models\ReserveMeeting::$open,
        ]);

        try {
            sendNotification('new_appointment_link', [
                '[link]' => $url,
                '[instructor.name]' => $user->full_name,
                '[time.date]' => $reservation->day,
            ], $reservation->user_id);
        } catch (\Throwable $e) {
            // notification is optional in local
        }

        return redirect()
            ->route('panel.v1.instructor.consultations')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم حفظ رابط اللقاء — يمكنك الانضمام الآن',
                'type' => 'success',
            ]);
    }

    public function consultationFinish(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $reservation = $this->instructorReserveMeetingOrFail($user, $id);

        if ($reservation->status === \App\Models\ReserveMeeting::$finished) {
            return back()->with('toast', [
                'title' => 'تنبيه',
                'msg' => 'الجلسة منتهية مسبقاً',
                'type' => 'info',
            ]);
        }

        $reservation->update(['status' => \App\Models\ReserveMeeting::$finished]);

        return redirect()
            ->route('panel.v1.instructor.consultations')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إنهاء الجلسة',
                'type' => 'success',
            ]);
    }

    private function instructorReserveMeetingsQuery($user)
    {
        $meetingIds = \App\Models\Meeting::where('creator_id', $user->id)->pluck('id');

        return \App\Models\ReserveMeeting::query()
            ->where(function ($q) use ($meetingIds) {
                $q->whereIn('meeting_id', $meetingIds->all() ?: [0])
                    ->orWhereIn(
                        'meeting_time_id',
                        \App\Models\MeetingTime::whereIn('meeting_id', $meetingIds->all() ?: [0])->pluck('id')->all() ?: [0]
                    );
            });
    }

    private function instructorReserveMeetingOrFail($user, int $id): \App\Models\ReserveMeeting
    {
        return $this->instructorReserveMeetingsQuery($user)
            ->with(['user', 'meetingTime', 'session'])
            ->where('id', $id)
            ->firstOrFail();
    }

    private function isUsableMeetingLink(?string $link): bool
    {
        $link = trim((string) $link);
        if ($link === '') {
            return false;
        }

        if (!filter_var($link, FILTER_VALIDATE_URL)) {
            // Relative Agora panel paths are handled separately.
            return str_starts_with($link, '/panel/sessions/');
        }

        $host = strtolower((string) parse_url($link, PHP_URL_HOST));
        if ($host === '' || str_ends_with($host, 'example.com') || $host === 'localhost' || $host === '127.0.0.1') {
            return false;
        }

        return true;
    }

    private function resolveConsultationJoinTarget(\App\Models\ReserveMeeting $reservation): ?string
    {
        if (!empty($reservation->session)
            && ($reservation->session->session_api ?? null) === 'agora'
            && method_exists($reservation->session, 'getJoinLink')
        ) {
            return url($reservation->session->getJoinLink());
        }

        if (!empty($reservation->session) && method_exists($reservation->session, 'getJoinLink')) {
            $path = $reservation->session->getJoinLink();
            if (!empty($path)) {
                return url($path);
            }
        }

        $link = trim((string) ($reservation->link ?? ''));
        if ($this->isUsableMeetingLink($link)) {
            return str_starts_with($link, '/') ? url($link) : $link;
        }

        return null;
    }

    private function createConsultationAgoraSession(\App\Models\ReserveMeeting $reservation, $user): void
    {
        $duration = 60;
        if (!empty($reservation->start_at) && !empty($reservation->end_at) && $reservation->end_at > $reservation->start_at) {
            $duration = max(15, (int) (($reservation->end_at - $reservation->start_at) / 60));
        }

        $session = \App\Models\Session::query()->updateOrCreate([
            'creator_id' => $user->id,
            'reserve_meeting_id' => $reservation->id,
        ], [
            'date' => time(),
            'duration' => $duration,
            'link' => null,
            'session_api' => 'agora',
            'agora_settings' => json_encode([
                'chat' => true,
                'record' => true,
                'users_join' => true,
            ]),
            'check_previous_parts' => false,
            'status' => \App\Models\Session::$Active,
            'created_at' => time(),
        ]);

        \App\Models\Translation\SessionTranslation::updateOrCreate([
            'session_id' => $session->id,
            'locale' => mb_strtolower(app()->getLocale()),
        ], [
            'title' => 'جلسة استشارية مباشرة',
            'description' => 'جلسة مباشرة داخل المنصة',
        ]);

        try {
            sendNotification('new_appointment_session', [
                '[link]' => $session->getJoinLink(),
                '[instructor.name]' => $user->full_name,
                '[time.date]' => dateTimeFormat($session->date, 'j M Y H:i'),
            ], $reservation->user_id);
        } catch (\Throwable $e) {
            // optional
        }
    }

    private function mapConsultationRow(\App\Models\ReserveMeeting $reservation): array
    {
        $dayLabels = [
            'saturday' => 'السبت',
            'sunday' => 'الأحد',
            'monday' => 'الإثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
        ];

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
        if (!empty($reservation->start_at) && !empty($reservation->end_at)) {
            $timeLabel = date('H:i', (int) $reservation->start_at) . '-' . date('H:i', (int) $reservation->end_at);
        } else {
            $timeLabel = $slot->time ?? '';
        }

        $joinTarget = $this->resolveConsultationJoinTarget($reservation);
        $agoraEnabled = !empty(getFeaturesSettings('agora_for_meeting'));
        $canManageLink = $reservation->meeting_type !== 'in_person'
            && !in_array($reservation->status, [
                \App\Models\ReserveMeeting::$finished,
                \App\Models\ReserveMeeting::$canceled,
            ], true);

        // With Agora on: join always available (creates in-app room on click).
        $canJoin = $canManageLink && ($agoraEnabled || !empty($joinTarget));

        $canFinish = !in_array($reservation->status, [
            \App\Models\ReserveMeeting::$finished,
            \App\Models\ReserveMeeting::$canceled,
        ], true);

        $avatar = null;
        try {
            $avatar = $reservation->user ? $reservation->user->getAvatar(44) : null;
        } catch (\Throwable $e) {
            $avatar = null;
        }

        return [
            'id' => $reservation->id,
            'initials' => mb_substr($reservation->user->full_name ?? '?', 0, 2),
            'avatar' => $avatar,
            'name' => $reservation->user->full_name ?? '',
            'email' => $reservation->user->email ?? '',
            'joinType' => $joinType,
            'day' => $dayLabels[$dayKey] ?? '—',
            'date' => $ts > 0 ? date('Y/m/d', $ts) : ($reservation->day ?? '—'),
            'time' => $timeLabel ?: '—',
            'amount' => handlePrice($reservation->paid_amount),
            'students' => (int) ($reservation->student_count ?? 1),
            'status' => $statusLabel,
            'status_key' => $reservation->status,
            'link' => $joinTarget,
            'link_raw' => $this->isUsableMeetingLink($reservation->link) ? $reservation->link : null,
            'detail_url' => route('panel.v1.instructor.consultations.show', ['id' => $reservation->id]),
            'join_url' => $canJoin
                ? route('panel.v1.instructor.consultations.join', ['id' => $reservation->id])
                : null,
            'session_url' => $canManageLink
                ? route('panel.v1.instructor.consultations.session', ['id' => $reservation->id])
                : null,
            'finish_url' => $canFinish
                ? route('panel.v1.instructor.consultations.finish', ['id' => $reservation->id])
                : null,
            'can_join' => $canJoin,
            'can_create_session' => $canManageLink,
            'can_finish' => $canFinish,
            'agora_enabled' => $agoraEnabled,
            'join_label' => $agoraEnabled ? 'انضمام للجلسة المباشرة' : 'رابط اللقاء',
        ];
    }

    public function calendar(Request $request)
    {
        $user = $this->resolveInstructor($request);

        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $events = $this->buildInstructorCalendarEvents($user);
        $now = time();

        $eventDates = collect($events)
            ->pluck('date')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $selectedTs = (int) $request->get('date', $now);
        $selectedDate = dateTimeFormat($selectedTs, 'Y-m-d', false);
        $dayEvents = collect($events)
            ->where('date', $selectedDate)
            ->sortBy('timestamp')
            ->values()
            ->all();

        $upcoming = collect($events)
            ->filter(fn ($e) => empty($e['is_past']))
            ->sortBy('timestamp')
            ->take(8)
            ->values()
            ->all();

        return $this->render($request, 'panel_v1.instructor.pages.calendar', 'تقويم الأحداث', [
            'calendarEvents' => $events,
            'calendarEventDates' => $eventDates,
            'dayEvents' => $dayEvents,
            'upcomingEvents' => $upcoming,
            'calendarYear' => (int) dateTimeFormat($selectedTs, 'Y', false),
            'calendarMonth' => (int) dateTimeFormat($selectedTs, 'n', false),
            'calendarSelected' => (int) dateTimeFormat($selectedTs, 'j', false),
            'selectedDate' => $selectedDate,
            'selectedDateLabel' => dateTimeFormat($selectedTs, 'Y/m/d', false),
        ]);
    }

    private function buildInstructorCalendarEvents($user): array
    {
        $items = [];
        $now = time();
        // Keep today's finished items visible; drop older finished ones.
        $horizonStart = $now - (12 * 3600);

        $webinarIds = Webinar::query()
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)->orWhere('creator_id', $user->id);
            })
            ->pluck('id')
            ->all();

        $meetingIds = \App\Models\Meeting::where('creator_id', $user->id)->pluck('id')->all();
        $timeIds = !empty($meetingIds)
            ? \App\Models\MeetingTime::whereIn('meeting_id', $meetingIds)->pluck('id')->all()
            : [];

        $reserves = \App\Models\ReserveMeeting::with(['user', 'meetingTime'])
            ->where(function ($q) use ($meetingIds, $timeIds) {
                $q->whereIn('meeting_id', $meetingIds ?: [0])
                    ->orWhereIn('meeting_time_id', $timeIds ?: [0]);
            })
            ->whereIn('status', [
                \App\Models\ReserveMeeting::$open,
                \App\Models\ReserveMeeting::$pending,
            ])
            ->where(function ($q) use ($horizonStart) {
                $q->where('end_at', '>=', $horizonStart)
                    ->orWhere(function ($q2) use ($horizonStart) {
                        $q2->whereNull('end_at')->where(function ($q3) use ($horizonStart) {
                            $q3->where('start_at', '>=', $horizonStart)
                                ->orWhere('date', '>=', $horizonStart);
                        });
                    });
            })
            ->orderBy('start_at')
            ->limit(100)
            ->get();

        foreach ($reserves as $reserve) {
            $startAt = (int) ($reserve->start_at ?: $reserve->date);
            if ($startAt <= 0) {
                continue;
            }
            $endsAt = (int) ($reserve->end_at ?: ($startAt + 3600));
            $isPast = $endsAt < $now;
            $timeLabel = '';
            if (!empty($reserve->start_at) && !empty($reserve->end_at)) {
                $timeLabel = dateTimeFormat((int) $reserve->start_at, 'H:i', false)
                    . ' - '
                    . dateTimeFormat((int) $reserve->end_at, 'H:i', false);
            } elseif (!empty($reserve->meetingTime->time)) {
                $timeLabel = $reserve->meetingTime->time;
            }

            $items[] = $this->mapCalendarEventItem([
                'type' => 'meeting',
                'type_label' => 'جلسة استشارية',
                'icon' => 'icon-[tabler--video]',
                'title' => $reserve->meetingTime->description ?? 'جلسة استشارية',
                'subtitle' => $reserve->user->full_name ?? 'طالب',
                'timestamp' => $startAt,
                'ends_at' => $endsAt,
                'is_past' => $isPast,
                'time_label' => $timeLabel,
                'url' => !$isPast
                    ? route('panel.v1.instructor.consultations.show', ['id' => $reserve->id])
                    : null,
                'calendar_url' => (!$isPast && method_exists($reserve, 'addToCalendarLink'))
                    ? $reserve->addToCalendarLink()
                    : null,
            ]);
        }

        if (!empty($webinarIds)) {
            $sessions = \App\Models\Session::query()
                ->with(['webinar.translations'])
                ->whereIn('webinar_id', $webinarIds)
                ->where('status', \App\Models\Session::$Active)
                ->whereNotNull('date')
                ->where('date', '>=', $horizonStart - (6 * 3600))
                ->orderBy('date')
                ->limit(100)
                ->get();

            foreach ($sessions as $session) {
                $startAt = (int) $session->date;
                $durationMin = max(15, (int) ($session->duration ?: 60));
                $endsAt = $startAt + ($durationMin * 60);
                if ($endsAt < $horizonStart) {
                    continue;
                }
                $isPast = $endsAt < $now;
                $wTr = $session->webinar
                    ? ($session->webinar->translate('ar') ?: $session->webinar->translate(app()->getLocale()) ?: $session->webinar->translations->first())
                    : null;
                $courseTitle = $wTr->title ?? ($session->webinar->title ?? 'دورة');
                $sessionTitle = $session->title ?: 'جلسة مباشرة';

                $items[] = $this->mapCalendarEventItem([
                    'type' => 'live_session',
                    'type_label' => 'جلسة مباشرة',
                    'icon' => 'icon-[tabler--broadcast]',
                    'title' => $sessionTitle,
                    'subtitle' => $courseTitle,
                    'timestamp' => $startAt,
                    'ends_at' => $endsAt,
                    'is_past' => $isPast,
                    'time_label' => dateTimeFormat($startAt, 'H:i', false) . ' · ' . $durationMin . ' د',
                    'url' => (!$isPast && !empty($session->webinar->slug))
                        ? route('panel.v1.instructor.courses.watch', ['slug' => $session->webinar->slug])
                        : null,
                    'calendar_url' => null,
                ]);
            }

            $liveClasses = Webinar::query()
                ->with('translations')
                ->whereIn('id', $webinarIds)
                ->where('type', Webinar::$webinar)
                ->whereNotNull('start_date')
                ->where('start_date', '>=', $horizonStart)
                ->orderBy('start_date')
                ->limit(40)
                ->get();

            foreach ($liveClasses as $webinar) {
                $startAt = (int) $webinar->start_date;
                // Live-class start marker: treat as ended 2 hours after start.
                $endsAt = $startAt + (2 * 3600);
                $isPast = $endsAt < $now;
                $tr = $webinar->translate('ar') ?: $webinar->translate(app()->getLocale()) ?: $webinar->translations->first();

                $items[] = $this->mapCalendarEventItem([
                    'type' => 'live_class',
                    'type_label' => 'بدء بث مباشر',
                    'icon' => 'icon-[tabler--player-play]',
                    'title' => $tr->title ?? ('دورة #' . $webinar->id),
                    'subtitle' => 'موعد بدء الدورة المباشرة',
                    'timestamp' => $startAt,
                    'ends_at' => $endsAt,
                    'is_past' => $isPast,
                    'time_label' => dateTimeFormat($startAt, 'H:i', false),
                    'url' => (!$isPast && !empty($webinar->slug))
                        ? route('panel.v1.instructor.courses.watch', ['slug' => $webinar->slug])
                        : null,
                    'calendar_url' => null,
                ]);
            }
        }

        if (class_exists(\App\Models\Event::class)) {
            try {
                $createdEvents = \App\Models\Event::query()
                    ->where('creator_id', $user->id)
                    ->whereNotNull('start_date')
                    ->where('start_date', '>=', $horizonStart)
                    ->orderBy('start_date')
                    ->limit(40)
                    ->get();

                foreach ($createdEvents as $event) {
                    $startAt = (int) $event->start_date;
                    $endsAt = $startAt + (2 * 3600);
                    $isPast = $endsAt < $now;
                    $items[] = $this->mapCalendarEventItem([
                        'type' => 'event',
                        'type_label' => 'فعالية',
                        'icon' => 'icon-[tabler--ticket]',
                        'title' => $event->title ?? ('فعالية #' . $event->id),
                        'subtitle' => 'فعالية من إنشائك',
                        'timestamp' => $startAt,
                        'ends_at' => $endsAt,
                        'is_past' => $isPast,
                        'time_label' => dateTimeFormat($startAt, 'H:i', false),
                        'url' => (!$isPast && !empty($event->slug)) ? url('/events/' . $event->slug) : null,
                        'calendar_url' => null,
                    ]);
                }
            } catch (\Throwable $e) {
                // optional feature
            }
        }

        usort($items, fn ($a, $b) => ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0));

        return array_values($items);
    }

    private function mapCalendarEventItem(array $item): array
    {
        $ts = (int) ($item['timestamp'] ?? 0);
        $isPast = !empty($item['is_past']);

        return [
            'type' => $item['type'] ?? 'event',
            'type_label' => $item['type_label'] ?? 'حدث',
            'icon' => $item['icon'] ?? 'icon-[tabler--calendar]',
            'title' => $item['title'] ?? '',
            'subtitle' => $item['subtitle'] ?? '',
            'timestamp' => $ts,
            'ends_at' => (int) ($item['ends_at'] ?? $ts),
            'date' => dateTimeFormat($ts, 'Y-m-d', false),
            'time_label' => $item['time_label'] ?? '',
            'url' => $isPast ? null : ($item['url'] ?? null),
            'calendar_url' => $isPast ? null : ($item['calendar_url'] ?? null),
            'is_past' => $isPast,
            'status_label' => $isPast ? 'انتهت' : 'قادمة',
            'can_open' => !$isPast && !empty($item['url']),
        ];
    }

    public function quizzes(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinarIds = $this->teacherWebinars($user)->pluck('id')->all();

        $quizzes = empty($webinarIds)
            ? collect()
            : Quiz::with(['webinar'])
                ->withCount([
                    'quizQuestions',
                    'quizResults',
                    'quizResults as waiting_count' => function ($q) {
                        $q->where('status', \App\Models\QuizzesResult::$waiting);
                    },
                ])
                ->withSum('quizQuestions', 'grade')
                ->whereIn('webinar_id', $webinarIds)
                ->orderByDesc('id')
                ->get();

        $quizIds = $quizzes->pluck('id')->all();

        $studentsByQuiz = empty($quizIds)
            ? collect()
            : \App\Models\QuizzesResult::whereIn('quiz_id', $quizIds)
                ->selectRaw('quiz_id, COUNT(DISTINCT user_id) as c')
                ->groupBy('quiz_id')
                ->pluck('c', 'quiz_id');

        $results = empty($quizIds)
            ? collect()
            : \App\Models\QuizzesResult::with(['user', 'quiz.webinar'])
                ->whereIn('quiz_id', $quizIds)
                ->orderByDesc('id')
                ->limit(50)
                ->get();

        $waitingAll = empty($quizIds)
            ? collect()
            : \App\Models\QuizzesResult::with(['user', 'quiz.webinar'])
                ->whereIn('quiz_id', $quizIds)
                ->where('status', \App\Models\QuizzesResult::$waiting)
                ->orderByDesc('id')
                ->limit(12)
                ->get();

        $graded = $results->where('status', '!=', \App\Models\QuizzesResult::$waiting);
        $passRate = $graded->isNotEmpty()
            ? (int) round($graded->where('status', \App\Models\QuizzesResult::$passed)->count() / $graded->count() * 100)
            : 0;

        $attemptCounts = empty($quizIds)
            ? collect()
            : \App\Models\QuizzesResult::whereIn('quiz_id', $quizIds)
                ->selectRaw('quiz_id, user_id, COUNT(*) as c')
                ->groupBy('quiz_id', 'user_id')
                ->get()
                ->keyBy(fn ($row) => $row->quiz_id . ':' . $row->user_id);

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quizzes',
            'إدارة الاختبارات',
            [
                'quizStats' => [
                    ['value' => $quizzes->count() . ' اختبار', 'label' => 'إجمالي الاختبارات'],
                    ['value' => $passRate . '%', 'label' => 'متوسط نسبة النجاح'],
                    ['value' => $waitingAll->count() . ' إجابة', 'label' => 'إجابات بانتظار التصحيح'],
                ],
                'pendingQuizzes' => $waitingAll->map(function ($result) {
                    return [
                        'result_id' => $result->id,
                        'grade_url' => route('panel.v1.instructor.quiz-results.grade', ['resultId' => $result->id]),
                        'name' => $result->user->full_name ?? 'طالب',
                        'status' => 'بانتظار التصحيح',
                        'title' => $result->quiz->title ?? 'اختبار',
                        'course' => $result->quiz->webinar->title ?? '',
                        'date' => $this->formatAssignmentDate($result->created_at),
                    ];
                })->values()->all(),
                'quizRows' => $quizzes->map(function ($quiz) use ($studentsByQuiz) {
                    $slug = $quiz->webinar->slug ?? null;
                    $statusActive = ($quiz->status ?? '') === Quiz::ACTIVE;

                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title ?: 'اختبار',
                        'course' => $quiz->webinar->title ?? '',
                        'slug' => $slug,
                        'questions' => (int) ($quiz->quiz_questions_count ?? 0),
                        'duration' => !empty($quiz->time) ? ((int) $quiz->time) . ' دقيقة' : 'مفتوح',
                        'fullGrade' => (int) ($quiz->quiz_questions_sum_grade ?: $quiz->total_mark ?: 0),
                        'passGrade' => (int) ($quiz->pass_mark ?? 0),
                        'students' => (int) ($studentsByQuiz[$quiz->id] ?? 0),
                        'waiting' => (int) ($quiz->waiting_count ?? 0),
                        'status' => $statusActive ? 'نشط' : 'معطل',
                        'status_tone' => $statusActive ? 'success' : 'muted',
                        'created_at' => $this->formatAssignmentDate($quiz->created_at),
                        'view_url' => route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id]),
                        'edit_url' => route('panel.v1.instructor.quizzes.edit', ['id' => $quiz->id]),
                        'delete_url' => route('panel.v1.instructor.quizzes.delete', ['id' => $quiz->id]),
                        'course_url' => $slug
                            ? route('panel.v1.instructor.courses.performance', ['slug' => $slug])
                            : route('panel.v1.instructor.courses'),
                    ];
                })->values()->all(),
                'quizStudentRows' => $results->map(function ($result) use ($attemptCounts) {
                    $statusMeta = $this->quizResultStatusMeta($result->status);
                    $attemptsKey = $result->quiz_id . ':' . $result->user_id;
                    $attemptUsed = (int) ($attemptCounts->get($attemptsKey)->c ?? 1);
                    $attemptMax = $result->quiz->attempt ?? null;
                    $totalMark = (int) ($result->quiz->total_mark ?? 0);

                    return [
                        'result_id' => $result->id,
                        'grade_url' => route('panel.v1.instructor.quiz-results.grade', ['resultId' => $result->id]),
                        'name' => $result->user->full_name ?? 'طالب',
                        'title' => $result->quiz->title ?? 'اختبار',
                        'course' => $result->quiz->webinar->title ?? '',
                        'grade' => ((int) ($result->user_grade ?? 0)) . ' / ' . $totalMark,
                        'attempts' => $attemptUsed . ' / ' . ($attemptMax ?: '—'),
                        'attempted_at' => $this->formatAssignmentDate($result->created_at),
                        'status' => $statusMeta['label'],
                        'status_tone' => $statusMeta['tone'],
                    ];
                })->values()->all(),
                'createQuizUrl' => route('panel.v1.instructor.quizzes.create'),
            ]
        );
    }

    private function quizResultStatusMeta(?string $status): array
    {
        return match ($status) {
            \App\Models\QuizzesResult::$passed => ['label' => 'ناجح', 'tone' => 'success'],
            \App\Models\QuizzesResult::$waiting => ['label' => 'بانتظار التصحيح', 'tone' => 'warning'],
            default => ['label' => 'راسب', 'tone' => 'danger'],
        };
    }

    public function quizView(Request $request, int $id)
    {
        $guardUser = $request->user();
        if (!$guardUser) {
            return redirect('/login');
        }

        $quiz = $this->teacherQuizOrFail($guardUser, $id);
        $webinar = $quiz->webinar;
        $shell = $this->instructorCourseShell($webinar, $request);

        $questions = \App\Models\QuizzesQuestion::with(['quizzesQuestionsAnswers'])
            ->where('quiz_id', $quiz->id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $realQuestions = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'grade' => $question->grade,
                'model_answer' => $question->type === \App\Models\QuizzesQuestion::$descriptive
                    ? ($question->correct ?: '—')
                    : null,
                'options' => $question->quizzesQuestionsAnswers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'text' => $answer->title,
                        'correct' => (bool) $answer->correct,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $first = $realQuestions[0] ?? null;
        $quizView = [
            'title' => $quiz->title,
            'subtitle' => ($webinar->title ?? '') . (!empty($webinar->category->title) ? (' • ' . $webinar->category->title) : ''),
            'questions_count' => count($realQuestions),
            'current' => 1,
            'total' => max(1, count($realQuestions)),
            'question' => $first['title'] ?? 'لا توجد أسئلة بعد — أضف سؤالاً أدناه.',
            'options' => collect($first['options'] ?? [])->map(function ($option) {
                return [
                    'text' => $option['text'],
                    'correct' => !empty($option['correct']),
                    'selected' => false,
                ];
            })->all(),
        ];

        $waitingResults = \App\Models\QuizzesResult::with(['user'])
            ->where('quiz_id', $quiz->id)
            ->where('status', \App\Models\QuizzesResult::$waiting)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quiz-view',
            'عرض الاختبار',
            array_merge($shell, [
                'quizId' => $quiz->id,
                'slug' => $webinar->slug ?? '',
                'webinar' => $webinar,
                'quizTitle' => $quiz->title,
                'quizMeta' => [
                    'pass_mark' => $quiz->pass_mark,
                    'time' => $quiz->time,
                    'attempt' => $quiz->attempt,
                    'status' => $quiz->status,
                    'total_mark' => $quiz->total_mark,
                ],
                'quizView' => $quizView,
                'realQuestions' => $realQuestions,
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
        $quiz->load('webinar');

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
        $type = $request->input('type', 'multiple');

        $rules = [
            'title' => 'required|string|max:1000',
            'type' => 'required|in:multiple,descriptive',
            'grade' => 'required|integer|min:1|max:1000',
        ];

        if ($type === 'multiple') {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'nullable|string|max:1000';
            $rules['correct_option'] = 'required|integer|min:0|max:10';
        } else {
            $rules['correct'] = 'nullable|string|max:5000';
        }

        $data = $request->validate($rules, [
            'title.required' => 'اكتب نص السؤال',
            'grade.required' => 'حدد درجة السؤال',
            'options.required' => 'أضف خيارين على الأقل',
            'options.min' => 'أضف خيارين على الأقل',
            'correct_option.required' => 'اختر الإجابة الصحيحة بالنقر على ○ بجانب الخيار',
        ]);

        $options = [];
        if ($type === 'multiple') {
            $options = array_values(array_filter(array_map('trim', (array) $request->input('options', [])), fn ($v) => $v !== ''));
            if (count($options) < 2) {
                return back()->withInput()->withErrors([
                    'options' => 'أضف خيارين مكتوبين على الأقل',
                ]);
            }
            $correctOption = (int) $request->input('correct_option', 0);
            if ($correctOption < 0 || $correctOption >= count($options)) {
                return back()->withInput()->withErrors([
                    'correct_option' => 'اختر الإجابة الصحيحة من الخيارات المكتوبة',
                ]);
            }
        }

        $maxOrder = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->max('order');

        $question = new \App\Models\QuizzesQuestion();
        $question->quiz_id = $quiz->id;
        $question->creator_id = $guardUser->id;
        $question->grade = (int) $data['grade'];
        $question->type = $type;
        $question->order = $maxOrder + 1;
        $question->created_at = time();
        $question->updated_at = time();
        $question->save();

        $translation = $question->translateOrNew('ar');
        $translation->locale = 'ar';
        $translation->title = $data['title'];
        if ($type === 'descriptive') {
            $translation->correct = $request->input('correct');
        }
        $translation->save();

        if ($type === 'multiple') {
            $correctOption = (int) $request->input('correct_option', 0);
            foreach ($options as $index => $optionTitle) {
                $answer = new \App\Models\QuizzesQuestionsAnswer();
                $answer->question_id = $question->id;
                $answer->creator_id = $guardUser->id;
                $answer->correct = $index === $correctOption ? 1 : 0;
                $answer->created_at = time();
                $answer->updated_at = time();
                $answer->save();

                $answerTranslation = $answer->translateOrNew('ar');
                $answerTranslation->locale = 'ar';
                $answerTranslation->title = mb_substr($optionTitle, 0, 1000);
                $answerTranslation->save();
            }
        }

        $quiz->total_mark = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->sum('grade');
        $quiz->updated_at = time();
        $quiz->save();

        return redirect()
            ->route('panel.v1.instructor.quizzes.view', ['id' => $quiz->id])
            ->with('toast', ['title' => 'تم', 'msg' => 'تمت إضافة السؤال بنجاح', 'type' => 'success']);
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

        $quiz->total_mark = (int) \App\Models\QuizzesQuestion::where('quiz_id', $quiz->id)->sum('grade');
        $quiz->updated_at = time();
        $quiz->save();

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

        $decoded = json_decode($result->results ?? '[]', true) ?: [];
        $questionIds = array_keys($decoded);
        $questions = \App\Models\QuizzesQuestion::with(['quizzesQuestionsAnswers'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $reviewItems = [];
        foreach ($decoded as $questionId => $entry) {
            $question = $questions->get($questionId);
            $studentAnswer = '—';
            $modelAnswer = '—';
            $isCorrect = null;

            if ($question && $question->type === \App\Models\QuizzesQuestion::$descriptive) {
                $studentAnswer = $entry['text'] ?? '—';
                $modelAnswer = $question->correct ?: '—';
            } elseif (!empty($entry['answer'])) {
                $answer = \App\Models\QuizzesQuestionsAnswer::find($entry['answer']);
                $studentAnswer = $answer->title ?? '—';
                $correct = $question
                    ? $question->quizzesQuestionsAnswers->firstWhere('correct', 1)
                    : null;
                $modelAnswer = $correct->title ?? '—';
                $isCorrect = !empty($entry['status']);
            }

            $reviewItems[] = [
                'question' => $question->title ?? ('سؤال #' . $questionId),
                'type' => $question->type ?? 'multiple',
                'student_answer' => $studentAnswer,
                'model_answer' => $modelAnswer,
                'is_correct' => $isCorrect,
                'grade' => (int) ($entry['grade'] ?? ($question->grade ?? 0)),
            ];
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.quiz-result-grade',
            'تصحيح نتيجة',
            [
                'quizResult' => $result,
                'reviewItems' => $reviewItems,
                'quizTitle' => $result->quiz->title ?? 'اختبار',
                'courseTitle' => $result->quiz->webinar->title ?? '',
                'studentName' => $result->user->full_name ?? 'طالب',
                'maxGrade' => (int) ($result->quiz->total_mark ?? 0),
            ]
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
            ->route('panel.v1.instructor.quizzes')
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

    public function comments(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $webinarIds = $this->teacherWebinars($user)->pluck('id')->all();
        $courseId = (int) $request->get('course_id', 0);
        $q = trim((string) $request->get('q', ''));

        $baseQuery = \App\Models\Comment::query()
            ->whereIn('webinar_id', $webinarIds ?: [0])
            ->whereNull('reply_id')
            ->whereNotNull('webinar_id');

        $totalComments = (clone $baseQuery)->count();
        $pendingCount = (clone $baseQuery)->where('status', \App\Models\Comment::$pending)->count();
        $repliedCount = (clone $baseQuery)->whereHas('replies')->count();
        $commentIds = (clone $baseQuery)->pluck('id');
        $reportsCount = $commentIds->isEmpty()
            ? 0
            : \App\Models\CommentReport::whereIn('comment_id', $commentIds)->count();

        $listQuery = (clone $baseQuery)
            ->with([
                'user',
                'webinar',
                'replies' => function ($query) {
                    $query->with('user')->orderBy('id');
                },
            ])
            ->orderByDesc('id');

        if ($courseId > 0 && in_array($courseId, $webinarIds, true)) {
            $listQuery->where('webinar_id', $courseId);
        }

        if ($q !== '') {
            $matchedWebinarIds = Webinar::whereIn('id', $webinarIds ?: [0])
                ->get()
                ->filter(fn ($webinar) => mb_stripos((string) $webinar->title, $q) !== false)
                ->pluck('id')
                ->all();

            $listQuery->where(function ($query) use ($q, $matchedWebinarIds) {
                $query->where('comment', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('full_name', 'like', '%' . $q . '%');
                    });

                if (!empty($matchedWebinarIds)) {
                    $query->orWhereIn('webinar_id', $matchedWebinarIds);
                }
            });
        }

        $comments = $listQuery->limit(40)->get();

        foreach ($comments->whereNull('viewed_at') as $comment) {
            $comment->update(['viewed_at' => time()]);
        }

        $courseOptions = $this->teacherWebinars($user)->map(fn ($webinar) => [
            'id' => $webinar->id,
            'title' => $webinar->title,
        ])->values()->all();

        $rows = $comments->map(function ($comment) use ($user) {
            $statusActive = ($comment->status ?? '') === \App\Models\Comment::$active;

            return [
                'id' => $comment->id,
                'name' => $comment->user->full_name ?? 'طالب',
                'avatar' => method_exists($comment->user, 'getAvatar') ? $comment->user->getAvatar() : null,
                'course' => $comment->webinar->title ?? '',
                'slug' => $comment->webinar->slug ?? null,
                'body' => trim(strip_tags((string) ($comment->comment ?? ''))),
                'status' => $statusActive ? 'نشط' : 'قيد المراجعة',
                'status_tone' => $statusActive ? 'success' : 'warning',
                'date' => $this->formatAssignmentDate($comment->created_at),
                'time' => !empty($comment->created_at) ? date('H:i', (int) $comment->created_at) : '',
                'replies' => $comment->replies->map(function ($reply) use ($user) {
                    $isInstructor = (int) ($reply->user_id ?? 0) === (int) $user->id;

                    return [
                        'id' => $reply->id,
                        'name' => $reply->user->full_name ?? 'مستخدم',
                        'body' => trim(strip_tags((string) ($reply->comment ?? ''))),
                        'date' => $this->formatAssignmentDate($reply->created_at),
                        'is_instructor' => $isInstructor,
                    ];
                })->values()->all(),
                'reply_url' => route('panel.v1.instructor.comments.reply', ['id' => $comment->id]),
                'report_url' => route('panel.v1.instructor.comments.report', ['id' => $comment->id]),
                'course_url' => !empty($comment->webinar->slug)
                    ? route('panel.v1.instructor.courses.performance', ['slug' => $comment->webinar->slug])
                    : route('panel.v1.instructor.courses'),
            ];
        })->values()->all();

        return $this->render($request, 'panel_v1.instructor.pages.comments', 'تعليقات الدورات', [
            'commentStats' => [
                ['value' => (string) $totalComments, 'label' => 'إجمالي التعليقات'],
                ['value' => (string) $repliedCount, 'label' => 'تم الرد عليها'],
                ['value' => (string) $pendingCount, 'label' => 'قيد المراجعة'],
                ['value' => (string) $reportsCount, 'label' => 'بلاغات'],
            ],
            'commentRows' => $rows,
            'courseOptions' => $courseOptions,
            'selectedCourseId' => $courseId > 0 ? $courseId : null,
            'searchQuery' => $q,
        ]);
    }

    public function commentReply(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'comment' => 'required|string|max:5000',
        ], [
            'comment.required' => 'اكتب نص الرد',
        ]);

        $comment = $this->teacherCourseCommentOrFail($user, $id);

        \App\Models\Comment::create([
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
            'webinar_id' => $comment->webinar_id,
            'reply_id' => $comment->id,
            'status' => \App\Models\Comment::$active,
            'created_at' => time(),
            'viewed_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.comments', array_filter([
                'course_id' => $request->input('course_id'),
                'q' => $request->input('q'),
            ]))
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إرسال الرد بنجاح',
                'type' => 'success',
            ]);
    }

    public function commentReport(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ], [
            'message.required' => 'اكتب سبب البلاغ',
        ]);

        $comment = $this->teacherCourseCommentOrFail($user, $id);

        \App\Models\CommentReport::create([
            'webinar_id' => $comment->webinar_id,
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'message' => $request->input('message'),
            'created_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.comments')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إرسال البلاغ للإدارة',
                'type' => 'success',
            ]);
    }

    private function teacherCourseCommentOrFail($user, int $id): \App\Models\Comment
    {
        $comment = \App\Models\Comment::with('webinar')->findOrFail($id);
        $webinar = $comment->webinar;

        if (
            empty($webinar)
            || (
                (int) $webinar->teacher_id !== (int) $user->id
                && (int) $webinar->creator_id !== (int) $user->id
            )
        ) {
            abort(404);
        }

        return $comment;
    }

    public function certificates(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $issuedQuery = $this->teacherCertificatesQuery($user);
        $issued = (clone $issuedQuery)
            ->with(['student', 'webinar', 'quiz', 'bundle'])
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $quizSources = \App\Models\Quiz::query()
            ->where('creator_id', $user->id)
            ->where('status', \App\Models\Quiz::ACTIVE)
            ->where('certificate', true)
            ->with(['webinar'])
            ->withCount('certificates')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $courseSources = \App\Models\Webinar::query()
            ->where('status', 'active')
            ->where('certificate', true)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)->orWhere('teacher_id', $user->id);
            })
            ->with('category')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $completionRows = $courseSources->map(function ($webinar) {
            $generated = \App\Models\Certificate::where('webinar_id', $webinar->id)
                ->where('type', 'course')
                ->count();
            $last = \App\Models\Certificate::where('webinar_id', $webinar->id)
                ->where('type', 'course')
                ->orderByDesc('id')
                ->first();

            return [
                'title' => $webinar->title,
                'course' => $webinar->category->title ?? '',
                'generated' => $generated,
                'last_at' => $last ? date('Y/m/d', (int) $last->created_at) : '—',
                'view_url' => route('panel.v1.instructor.certificates.details', [
                    'type' => 'courses',
                    'id' => $webinar->id,
                ]),
            ];
        })->values()->all();

        $examRows = $quizSources->map(function ($quiz) {
            $last = \App\Models\Certificate::where('quiz_id', $quiz->id)
                ->orderByDesc('id')
                ->first();

            return [
                'title' => $quiz->title,
                'course' => $quiz->webinar->title ?? '',
                'generated' => (int) ($quiz->certificates_count ?? 0),
                'last_at' => $last ? date('Y/m/d', (int) $last->created_at) : '—',
                'view_url' => route('panel.v1.instructor.certificates.details', [
                    'type' => 'quiz',
                    'id' => $quiz->id,
                ]),
            ];
        })->values()->all();

        $totalGenerated = (clone $issuedQuery)->count();
        $quizGenerated = (clone $issuedQuery)->where('type', 'quiz')->count();
        $completionGenerated = (clone $issuedQuery)->whereIn('type', ['course', 'bundle'])->count();
        $studentsCount = (clone $issuedQuery)->pluck('student_id')->unique()->filter()->count();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.certificates',
            'إدارة الشهادات',
            [
                'certificateStats' => [
                    ['value' => (string) $totalGenerated, 'label' => 'شهادات مصدرة'],
                    ['value' => (string) $completionGenerated, 'label' => 'شهادات إتمام'],
                    ['value' => (string) $quizGenerated, 'label' => 'شهادات اختبارات'],
                    ['value' => (string) $studentsCount, 'label' => 'طلاب حاصلون على شهادات'],
                ],
                'recentCertificates' => $issued->take(8)->map(function ($certificate) {
                    $title = $certificate->webinar->title
                        ?? ($certificate->quiz->title ?? ($certificate->bundle->title ?? 'شهادة'));

                    return [
                        'title' => $title,
                        'student' => $certificate->student->full_name ?? '',
                        'download_url' => route('panel.v1.instructor.certificates.download', [
                            'id' => $certificate->id,
                        ]),
                    ];
                })->values()->all(),
                'completionRows' => $completionRows,
                'examRows' => $examRows,
                'allCertificatesUrl' => route('panel.v1.instructor.certificates.students'),
            ]
        );
    }

    public function certificatesStudents(Request $request, ?string $type = null, ?int $id = null)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $filterType = $type ?: $request->get('type');
        $filterId = $id ?: (int) $request->get('item_id', 0);
        if ($filterId <= 0) {
            $filterId = null;
        }

        $allowedTypes = ['quiz', 'courses', 'bundles'];
        if (!empty($filterType) && !in_array($filterType, $allowedTypes, true)) {
            abort(404);
        }

        $query = $this->teacherCertificatesQuery($user, $filterType, $filterId);
        $certificates = (clone $query)
            ->with(['student', 'webinar', 'quiz', 'bundle', 'quizzesResult'])
            ->orderByDesc('id')
            ->limit(60)
            ->get();

        $rows = $certificates->map(function ($certificate) {
            $title = $certificate->webinar->title
                ?? ($certificate->quiz->title ?? ($certificate->bundle->title ?? 'شهادة #' . $certificate->id));

            $typeLabel = match ($certificate->type ?? '') {
                'quiz' => 'اختبار',
                'bundle' => 'باقة',
                default => 'إتمام دورة',
            };

            return [
                'id' => $certificate->id,
                'student' => $certificate->student->full_name ?? 'طالب',
                'email' => $certificate->student->email ?? '',
                'title' => $title,
                'type' => $typeLabel,
                'grade' => $certificate->user_grade ?? null,
                'date' => !empty($certificate->created_at) ? date('Y/m/d', (int) $certificate->created_at) : '—',
                'download_url' => route('panel.v1.instructor.certificates.download', ['id' => $certificate->id]),
                'view_url' => route('panel.v1.instructor.certificates.download', [
                    'id' => $certificate->id,
                    'view' => 1,
                ]),
            ];
        })->values()->all();

        $filterLabel = null;
        if ($filterType === 'quiz' && $filterId) {
            $filterLabel = \App\Models\Quiz::find($filterId)?->title;
        } elseif ($filterType === 'courses' && $filterId) {
            $filterLabel = \App\Models\Webinar::find($filterId)?->title;
        } elseif ($filterType === 'bundles' && $filterId) {
            $filterLabel = \App\Models\Bundle::find($filterId)?->title;
        }

        return $this->render(
            $request,
            'panel_v1.instructor.pages.certificates-students',
            'جميع الشهادات الصادرة',
            [
                'certificateRows' => $rows,
                'filterLabel' => $filterLabel,
                'filterType' => $filterType,
                'backUrl' => route('panel.v1.instructor.certificates'),
                'totalCount' => count($rows),
            ]
        );
    }

    public function downloadCertificate(Request $request, int $id)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $certificate = $this->teacherCertificatesQuery($user)->where('id', $id)->first();
        if (empty($certificate)) {
            abort(404);
        }

        $make = new \App\Mixins\Certificate\MakeCertificate();
        $response = $make->showCertificateByType($certificate, $request->boolean('view'));

        if (empty($response)) {
            return redirect()
                ->route('panel.v1.instructor.certificates.students')
                ->with('toast', [
                    'title' => 'تعذر التحميل',
                    'msg' => 'تعذر إنشاء ملف الشهادة حالياً. حاول مرة أخرى.',
                    'type' => 'error',
                ]);
        }

        return $response;
    }

    private function teacherCertificatesQuery($user, ?string $type = null, ?int $typeItemId = null)
    {
        return \App\Models\Certificate::query()
            ->where(function ($query) use ($user, $type, $typeItemId) {
                if (empty($type) || $type === 'quiz') {
                    $query->whereHas('quiz', function ($quizQuery) use ($user, $type, $typeItemId) {
                        $quizQuery->where('creator_id', $user->id)
                            ->where('status', \App\Models\Quiz::ACTIVE);

                        if ($type === 'quiz' && $typeItemId) {
                            $quizQuery->where('id', $typeItemId);
                        }
                    });
                }

                if (empty($type) || $type === 'courses') {
                    $query->orWhereHas('webinar', function ($webinarQuery) use ($user, $type, $typeItemId) {
                        $webinarQuery->where('status', 'active')
                            ->where(function ($owner) use ($user) {
                                $owner->where('creator_id', $user->id)
                                    ->orWhere('teacher_id', $user->id);
                            });

                        if ($type === 'courses' && $typeItemId) {
                            $webinarQuery->where('id', $typeItemId);
                        }
                    });
                }

                if (empty($type) || $type === 'bundles') {
                    $query->orWhereHas('bundle', function ($bundleQuery) use ($user, $type, $typeItemId) {
                        $bundleQuery->where('status', 'active')
                            ->where(function ($owner) use ($user) {
                                $owner->where('creator_id', $user->id)
                                    ->orWhere('teacher_id', $user->id);
                            });

                        if ($type === 'bundles' && $typeItemId) {
                            $bundleQuery->where('id', $typeItemId);
                        }
                    });
                }
            });
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
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $readyPayout = $user->getPayout();
        $financialSettings = getFinancialSettings();

        if (!empty($financialSettings['minimum_payout']) && $readyPayout < $financialSettings['minimum_payout']) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'الرصيد أقل من الحد الأدنى للسحب',
                'type' => 'error',
            ]]);
        }

        if (!$user->financial_approval) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'بياناتك المالية غير معتمدة من الإدارة بعد',
                'type' => 'error',
            ]]);
        }

        if (empty($user->selectedBank)) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'حدد حسابك البنكي من الإعدادات أولاً',
                'type' => 'error',
            ]]);
        }

        $hasWaiting = \App\Models\Payout::where('user_id', $user->id)
            ->where('status', \App\Models\Payout::$waiting)
            ->exists();

        if ($hasWaiting) {
            return back()->with(['toast' => [
                'title' => 'تعذر الطلب',
                'msg' => 'لديك طلب سحب قيد المعالجة بالفعل',
                'type' => 'error',
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

    public function payouts(Request $request)
    {
        $guardUser = $this->resolveInstructor($request);
        if ($guardUser instanceof \Illuminate\Http\RedirectResponse) {
            return $guardUser;
        }

        $financialSettings = getFinancialSettings();
        $minWithdraw = $financialSettings['minimum_payout'] ?? null;

        $availableRaw = 0.0;
        $incomeRaw = 0.0;
        try {
            $availableRaw = (float) $guardUser->getPayout();
            $incomeRaw = (float) $guardUser->getIncome();
        } catch (\Throwable $e) {
        }

        $heldRaw = (float) \App\Models\Payout::where('user_id', $guardUser->id)
            ->where('status', \App\Models\Payout::$waiting)
            ->sum('amount');

        $nextWaiting = \App\Models\Payout::where('user_id', $guardUser->id)
            ->where('status', \App\Models\Payout::$waiting)
            ->orderBy('id')
            ->first();

        $selectedBank = $guardUser->selectedBank;
        $bankLabel = $selectedBank->bank->title ?? null;

        $query = $this->instructorPayoutsQuery($guardUser, $request);
        $payouts = (clone $query)
            ->with(['userSelectedBank.bank'])
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $statusMeta = [
            'waiting' => ['label' => 'قيد المعالجة', 'tone' => 'warning'],
            'done' => ['label' => 'مكتمل', 'tone' => 'success'],
            'reject' => ['label' => 'مرفوض', 'tone' => 'danger'],
        ];

        $payoutRows = $payouts->map(function ($payout) use ($statusMeta) {
            $meta = $statusMeta[$payout->status] ?? ['label' => $payout->status, 'tone' => 'muted'];
            $bankTitle = $payout->userSelectedBank->bank->title ?? null;
            $created = (int) ($payout->created_at ?? 0);

            return [
                'id' => '#' . $payout->id,
                'raw_id' => $payout->id,
                'datetime' => $created ? date('Y/m/d H:i', $created) : '—',
                'date' => $created ? date('Y/m/d', $created) : '—',
                'time' => $created ? date('H:i', $created) : '',
                'type_line1' => 'طلب سحب أرباح',
                'type_line2' => $bankTitle ? ('إلى: ' . $bankTitle) : null,
                'amount' => handlePrice($payout->amount),
                'amount_raw' => (float) $payout->amount,
                'status' => $meta['label'],
                'status_key' => $payout->status,
                'status_tone' => $meta['tone'],
                'bank' => $bankTitle ?? '—',
                'note' => null,
            ];
        })->values()->all();

        return $this->render(
            $request,
            'panel_v1.instructor.pages.payouts',
            'ادارة المستحقات والسحب',
            [
                'payoutSummary' => [
                    'available' => handlePrice($availableRaw),
                    'available_raw' => $availableRaw,
                    'total_income' => handlePrice($incomeRaw),
                    'held' => handlePrice($heldRaw),
                    'next_payout' => $nextWaiting
                        ? date('Y/m/d', (int) $nextWaiting->created_at)
                        : 'لا يوجد طلب معلّق',
                    'min_withdraw' => $minWithdraw !== null ? handlePrice($minWithdraw) : '—',
                    'bank_label' => $bankLabel,
                    'can_request' => $availableRaw > 0
                        && (empty($minWithdraw) || $availableRaw >= (float) $minWithdraw)
                        && (bool) $guardUser->financial_approval
                        && !empty($selectedBank)
                        && $heldRaw <= 0,
                ],
                'payoutRows' => $payoutRows,
                'payoutFilters' => [
                    'q' => trim((string) $request->get('q', '')),
                    'status' => (string) $request->get('status', ''),
                    'from' => (string) $request->get('from', ''),
                    'to' => (string) $request->get('to', ''),
                ],
                'exportUrl' => route('panel.v1.instructor.payouts.export', array_filter([
                    'q' => $request->get('q'),
                    'status' => $request->get('status'),
                    'from' => $request->get('from'),
                    'to' => $request->get('to'),
                ], fn ($v) => $v !== null && $v !== '')),
                'settingsUrl' => route('panel.v1.instructor.settings'),
            ]
        );
    }

    public function exportPayouts(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $payouts = $this->instructorPayoutsQuery($user, $request)
            ->with(['userSelectedBank.bank'])
            ->orderByDesc('id')
            ->limit(1000)
            ->get();

        $fileName = 'payouts_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InstructorPayoutExport($payouts),
            $fileName
        );
    }

    private function instructorPayoutsQuery($user, Request $request)
    {
        $query = \App\Models\Payout::query()->where('user_id', $user->id);

        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $id = (int) ltrim($q, '#');

            if (preg_match('/^#?\d+$/', $q) && $id > 0) {
                $query->where('id', $id);
            } else {
                $matchedBankIds = \App\Models\UserBank::query()
                    ->get()
                    ->filter(fn ($bank) => mb_stripos((string) $bank->title, $q) !== false)
                    ->pluck('id')
                    ->all();

                $query->where(function ($builder) use ($q, $matchedBankIds) {
                    $builder->where('amount', 'like', '%' . $q . '%');

                    if (!empty($matchedBankIds)) {
                        $builder->orWhereHas('userSelectedBank', function ($bankQuery) use ($matchedBankIds) {
                            $bankQuery->whereIn('user_bank_id', $matchedBankIds);
                        });
                    }
                });
            }
        }

        $status = (string) $request->get('status', '');
        if (in_array($status, [
            \App\Models\Payout::$waiting,
            \App\Models\Payout::$done,
            \App\Models\Payout::$reject,
        ], true)) {
            $query->where('status', $status);
        }

        $from = trim((string) $request->get('from', ''));
        $to = trim((string) $request->get('to', ''));
        if ($from !== '' || $to !== '') {
            fromAndToDateFilter($from ?: null, $to ?: null, $query, 'created_at');
        }

        return $query;
    }

    public function marketing(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $now = time();
        $courseOptions = $this->teacherWebinars($user)
            ->filter(fn ($webinar) => ($webinar->status ?? '') === 'active')
            ->map(fn ($webinar) => [
                'id' => $webinar->id,
                'title' => $webinar->title,
            ])
            ->values()
            ->all();

        $webinarIds = collect($courseOptions)->pluck('id')->all();

        $couponRows = \App\Models\Discount::query()
            ->where('creator_id', $user->id)
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->map(function ($discount) use ($now) {
                $active = ($discount->status ?? 'active') === 'active'
                    && (int) ($discount->expired_at ?? 0) > $now;

                return [
                    'name' => $discount->title ?: ('قسيمة #' . $discount->id),
                    'email' => 'كود: ' . ($discount->code ?? '—'),
                    'course' => $discount->source === 'course' ? 'مخصص لدورات' : 'عام',
                    'course_id' => $discount->id,
                    'original_price' => '—',
                    'discount' => ((int) $discount->percent) . '%',
                    'total' => 'استخدام: ' . (int) ($discount->count ?? 0),
                    'net' => $active ? 'مفعّلة' : 'منتهية',
                    'type' => 'قسيمة',
                    'date' => date('Y/m/d', (int) $discount->created_at),
                    'time' => 'حتى ' . date('Y/m/d', (int) $discount->expired_at),
                    'status_tone' => $active ? 'success' : 'muted',
                ];
            })
            ->values()
            ->all();

        $discountRows = empty($webinarIds)
            ? []
            : \App\Models\SpecialOffer::query()
                ->with('webinar')
                ->whereIn('webinar_id', $webinarIds)
                ->orderByDesc('id')
                ->limit(40)
                ->get()
                ->map(function ($offer) use ($now) {
                    $active = ($offer->status ?? '') === \App\Models\SpecialOffer::$active
                        && (int) ($offer->to_date ?? 0) >= $now;

                    return [
                        'name' => $offer->name ?: ('تخفيض #' . $offer->id),
                        'email' => $active ? 'نشط الآن' : 'غير نشط',
                        'course' => $offer->webinar->title ?? 'دورة',
                        'course_id' => $offer->webinar_id ?? $offer->id,
                        'original_price' => '—',
                        'discount' => ((float) $offer->percent) . '%',
                        'total' => date('Y/m/d', (int) $offer->from_date) . ' → ' . date('Y/m/d', (int) $offer->to_date),
                        'net' => $active ? 'ساري' : 'منتهٍ',
                        'type' => 'تخفيض دورة',
                        'date' => date('Y/m/d', (int) $offer->created_at),
                        'time' => '',
                        'status_tone' => $active ? 'success' : 'muted',
                    ];
                })
                ->values()
                ->all();

        $promoSales = \App\Models\Sale::query()
            ->with(['webinar', 'promotion'])
            ->where('buyer_id', $user->id)
            ->where('type', \App\Models\Sale::$promotion)
            ->whereNull('refund_at')
            ->orderByDesc('id')
            ->limit(40)
            ->get();

        $promoRows = $promoSales->map(function ($sale) {
            $promo = $sale->promotion;

            return [
                'name' => $promo->title ?? ('خطة ترويج #' . $sale->id),
                'email' => ($promo->days ?? 0) ? ((int) $promo->days . ' يوم') : '',
                'course' => $sale->webinar->title ?? '—',
                'course_id' => $sale->webinar_id ?? $sale->id,
                'original_price' => handlePrice($sale->amount),
                'discount' => '—',
                'total' => handlePrice($sale->total_amount),
                'net' => 'مفعّلة',
                'type' => 'ترويج',
                'date' => date('Y/m/d', (int) $sale->created_at),
                'time' => date('H:i', (int) $sale->created_at),
                'status_tone' => 'success',
            ];
        })->values()->all();

        // If instructor has no purchased promos yet, show available catalog plans for clarity.
        if (empty($promoRows)) {
            $promoRows = \App\Models\Promotion::query()
                ->orderByDesc('is_popular')
                ->orderBy('price')
                ->limit(20)
                ->get()
                ->map(function ($promo) {
                    return [
                        'name' => $promo->title ?: ('خطة #' . $promo->id),
                        'email' => ((int) ($promo->days ?? 0)) . ' يوم',
                        'course' => 'متاحة للشراء/الطلب',
                        'course_id' => $promo->id,
                        'original_price' => handlePrice($promo->price),
                        'discount' => !empty($promo->is_popular) ? 'الأكثر طلباً' : '—',
                        'total' => handlePrice($promo->price),
                        'net' => 'متاحة',
                        'type' => 'خطة ترويج',
                        'date' => date('Y/m/d', (int) ($promo->created_at ?? time())),
                        'time' => '',
                        'status_tone' => 'warning',
                    ];
                })
                ->values()
                ->all();
        }

        $promotionPlans = \App\Models\Promotion::query()
            ->orderByDesc('is_popular')
            ->orderBy('price')
            ->get()
            ->map(fn ($promo) => [
                'id' => $promo->id,
                'title' => $promo->title,
                'price' => handlePrice($promo->price),
                'days' => (int) ($promo->days ?? 0),
            ])
            ->values()
            ->all();

        $marketingDept = \App\Models\SupportDepartment::query()
            ->get()
            ->first(fn ($dept) => mb_stripos((string) $dept->title, 'market') !== false
                || mb_stripos((string) $dept->title, 'تسويق') !== false);

        return $this->render(
            $request,
            'panel_v1.instructor.pages.marketing',
            'إدارة التسويق والعروض',
            [
                'couponRows' => $couponRows,
                'discountRows' => $discountRows,
                'promoRows' => $promoRows,
                'courseOptions' => $courseOptions,
                'promotionPlans' => $promotionPlans,
                'marketingDepartmentId' => $marketingDept->id ?? null,
                'couponCount' => count($couponRows),
                'offerCount' => count($discountRows),
                'promoCount' => count($promoRows),
            ]
        );
    }

    public function discountStore(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'percent' => 'required|integer|min:1|max:100',
            'webinar_id' => 'nullable|integer',
            'count' => 'nullable|integer|min:1|max:10000',
            'days' => 'nullable|integer|min:1|max:365',
        ], [
            'title.required' => 'أدخل عنوان القسيمة',
            'percent.required' => 'أدخل نسبة الخصم',
        ]);

        $webinarId = (int) $request->input('webinar_id', 0);
        if ($webinarId > 0) {
            $this->teacherOwnedWebinarOrFail($user, $webinarId);
        }

        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (\App\Models\Discount::where('code', $code)->exists());

        $days = max(1, (int) $request->input('days', 30));
        $source = $webinarId > 0 ? \App\Models\Discount::$discountSourceCourse : \App\Models\Discount::$discountSourceAll;

        $discount = \App\Models\Discount::create([
            'creator_id' => $user->id,
            'title' => $request->input('title'),
            'discount_type' => \App\Models\Discount::$discountTypePercentage,
            'source' => $source,
            'code' => $code,
            'percent' => (int) $request->input('percent'),
            'count' => max(1, (int) $request->input('count', 100)),
            'user_type' => 'all_users',
            'status' => 'active',
            'expired_at' => time() + ($days * 86400),
            'created_at' => time(),
        ]);

        if ($webinarId > 0) {
            \App\Models\DiscountCourse::create([
                'discount_id' => $discount->id,
                'course_id' => $webinarId,
            ]);
        }

        return redirect()
            ->route('panel.v1.instructor.marketing', ['tab' => 'coupons'])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إنشاء قسيمة الخصم: ' . $code,
                'type' => 'success',
            ]);
    }

    public function specialOfferStore(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'webinar_id' => 'required|integer',
            'percent' => 'required|numeric|min:1|max:100',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ], [
            'title.required' => 'أدخل عنوان التخفيض',
            'webinar_id.required' => 'اختر الدورة',
            'percent.required' => 'أدخل نسبة التخفيض',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد البداية',
        ]);

        $webinarId = (int) $request->input('webinar_id');
        $this->teacherOwnedWebinarOrFail($user, $webinarId);

        $hasActive = \App\Models\SpecialOffer::query()
            ->where('webinar_id', $webinarId)
            ->where('status', \App\Models\SpecialOffer::$active)
            ->where('to_date', '>=', time())
            ->exists();

        if ($hasActive) {
            return back()->with('toast', [
                'title' => 'تعذر الإنشاء',
                'msg' => 'هذه الدورة لديها تخفيض نشط بالفعل',
                'type' => 'error',
            ]);
        }

        $from = strtotime($request->input('from_date') . ' 00:00:00');
        $to = strtotime($request->input('to_date') . ' 23:59:59');

        \App\Models\SpecialOffer::create([
            'creator_id' => $user->id,
            'name' => $request->input('title'),
            'webinar_id' => $webinarId,
            'percent' => (float) $request->input('percent'),
            'status' => \App\Models\SpecialOffer::$active,
            'created_at' => time(),
            'from_date' => $from,
            'to_date' => $to,
        ]);

        return redirect()
            ->route('panel.v1.instructor.marketing', ['tab' => 'offers'])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم إنشاء تخفيض الدورة بنجاح',
                'type' => 'success',
            ]);
    }

    public function promotionRequestStore(Request $request)
    {
        $user = $this->resolveInstructor($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'promotion_id' => 'required|integer|exists:promotions,id',
            'webinar_id' => 'required|integer',
            'message' => 'nullable|string|max:2000',
        ], [
            'promotion_id.required' => 'اختر الخطة الترويجية',
            'webinar_id.required' => 'اختر الدورة',
        ]);

        $webinarId = (int) $request->input('webinar_id');
        $webinar = $this->teacherOwnedWebinarOrFail($user, $webinarId);
        $promotion = \App\Models\Promotion::findOrFail((int) $request->input('promotion_id'));

        $department = \App\Models\SupportDepartment::query()
            ->get()
            ->first(fn ($dept) => mb_stripos((string) $dept->title, 'market') !== false
                || mb_stripos((string) $dept->title, 'تسويق') !== false);

        if (empty($department)) {
            $department = \App\Models\SupportDepartment::query()->orderBy('id')->first();
        }

        if (empty($department)) {
            return back()->with('toast', [
                'title' => 'تعذر الإرسال',
                'msg' => 'لا يوجد قسم دعم لاستقبال الطلب',
                'type' => 'error',
            ]);
        }

        $title = 'طلب خطة ترويجية: ' . ($promotion->title ?: ('#' . $promotion->id));
        $body = trim(implode("\n", array_filter([
            'خطة: ' . ($promotion->title ?: ('#' . $promotion->id)),
            'المدة: ' . ((int) ($promotion->days ?? 0)) . ' يوم',
            'السعر: ' . handlePrice($promotion->price),
            'الدورة: ' . ($webinar->title ?? ('#' . $webinarId)),
            'معرّف الدورة: ' . $webinarId,
            $request->input('message') ? ('ملاحظة: ' . $request->input('message')) : null,
        ])));

        $support = \App\Models\Support::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'title' => $title,
            'status' => 'open',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        \App\Models\SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $body,
            'attach' => null,
            'created_at' => time(),
        ]);

        return redirect()
            ->route('panel.v1.instructor.support.conversations', ['id' => $support->id])
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم فتح طلب للفريق المختص بنجاح',
                'type' => 'success',
            ]);
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

    private function teacherOwnedWebinarOrFail($user, int $webinarId): Webinar
    {
        return Webinar::query()
            ->where('id', $webinarId)
            ->where(function ($query) use ($user) {
                $query->where('teacher_id', $user->id)
                    ->orWhere('creator_id', $user->id);
            })
            ->firstOrFail();
    }

    private function teacherWebinarOrFail($user, string $slug)
    {
        return Webinar::where('slug', $slug)
            ->where(function ($query) use ($user) {
                $query->where('teacher_id', $user->id)
                    ->orWhere('creator_id', $user->id);
            })
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
            ->where(function ($query) use ($user) {
                $query->where('teacher_id', $user->id)
                    ->orWhere('creator_id', $user->id);
            })
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
