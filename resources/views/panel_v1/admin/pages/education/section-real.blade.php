@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.page-header', ['title' => $stubTitle ?? $pageTitle ?? 'التعليم', 'subtitle' => ''])

    @include('panel_v1.admin.components.filter-bar')

    @if (!empty($paginator))
        @include('panel_v1.admin.components.stats-cards', ['stats' => [['label'=>'إجمالي النتائج','value'=> (string)$paginator->total(), 'icon'=>'icon-[tabler--database]']]])
    @endif

    @if (!empty($courses))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold text-16px text-primary">الدورات</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('panel.v1.admin.education.courses.export', request()->query()) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"><span class="icon-[tabler--file-spreadsheet] size-4"></span> تصدير Excel</a>
                    <a href="{{ route('panel.v1.admin.education.courses.create') }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px hover:opacity-95 transition">+ إضافة دورة</a>
                </div>
            </div>
            @if (!empty($filterCategories))
                <div class="px-4 sm:px-6 py-3 bg-white border-b border-d9 flex flex-wrap gap-3">
                    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap gap-2 w-full">
                        {{-- الحفاظ على البحث وأي باراميتر آخر عند تغيير قائمة منسدلة --}}
                        @foreach (request()->except(['category_id', 'status', 'page']) as $fkey => $fval)
                            @if(!is_array($fval))
                                <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                            @endif
                        @endforeach
                        <select name="category_id" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px min-w-[12rem] bg-white">
                            <option value="">كل التصنيفات</option>
                            @foreach ($filterCategories as $fc)
                                <option value="{{ $fc['id'] }}" @selected(request('category_id')==$fc['id'])>{{ $fc['title'] }}</option>
                            @endforeach
                        </select>
                        <select name="status" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px min-w-[10rem] bg-white">
                            <option value="">كل الحالات</option>
                            <option value="active" @selected(request('status')=='active')>نشط</option>
                            <option value="pending" @selected(request('status')=='pending')>بانتظار</option>
                            <option value="is_draft" @selected(request('status')=='is_draft')>مسودة</option>
                            <option value="inactive" @selected(request('status')=='inactive')>مرفوض</option>
                        </select>
                        @if(request('category_id') || request('status'))
                            <a href="{{ url()->current() }}" class="inline-flex items-center h-10 px-4 rounded-10px border border-d9 bg-white text-13px font-medium text-primary">مسح الفلتر</a>
                        @endif
                    </form>
                </div>
            @endif
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3">التصنيف</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3">السعر</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach ($courses as $c)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $c->title }}</td>
                            <td class="px-4 py-3 text-center">{{ $c->category->title ?? '' }}</td>
                            @php
                                $courseStatusLabel = match ($c->status) {
                                    'active' => 'نشط',
                                    'pending' => 'بانتظار المراجعة',
                                    'is_draft' => 'مسودة',
                                    'inactive' => 'مرفوض',
                                    default => $c->status ?: '—',
                                };
                            @endphp
                            <td class="px-4 py-3 text-center"><span @class(['inline-flex rounded-full px-2.5 py-1 font-semibold text-11px','bg-[#D1FAE5] text-[#059669]'=>$c->status=='active','bg-[#FEF3C7] text-[#D97706]'=>$c->status=='pending','bg-[#FEE2E2] text-[#DC2626]'=>$c->status=='inactive','bg-[#EFF6FF] text-[#2563EB]'=>$c->status=='is_draft' || !in_array($c->status,['active','pending','inactive','is_draft'], true)])>{{ $courseStatusLabel }}</span></td>
                            <td class="px-4 py-3 text-center">{{ $c->price ? handlePrice($c->price) : 'مجانية' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $courseActionItems = [
                                        ['label' => 'تعديل', 'url' => route('panel.v1.admin.education.courses.edit',['id'=>$c->id]), 'tone' => 'gray'],
                                        ['label' => 'المنهج', 'url' => route('panel.v1.admin.education.courses.curriculum',['id'=>$c->id]), 'tone' => 'gray'],
                                        ['label' => 'إشعار الطلاب', 'url' => route('panel.v1.admin.education.courses.notify',['id'=>$c->id]), 'tone' => 'gray'],
                                    ];
                                    if ($c->status !== 'active') {
                                        $courseActionItems[] = ['label' => 'موافقة', 'action' => route('panel.v1.admin.education.courses.approve',['id'=>$c->id]), 'tone' => 'success'];
                                    }
                                    if ($c->status !== 'inactive') {
                                        $courseActionItems[] = ['label' => 'رفض', 'action' => route('panel.v1.admin.education.courses.reject',['id'=>$c->id]), 'tone' => 'danger'];
                                    }
                                    $courseActionItems[] = ['label' => 'حذف', 'action' => route('panel.v1.admin.education.courses.delete',['id'=>$c->id]), 'confirm' => 'حذف الدورة؟', 'tone' => 'danger'];
                                @endphp
                                @include('panel_v1.components.actions-dropdown', [
                                    'id' => 'edu-course-'.$c->id,
                                    'items' => $courseActionItems,
                                ])
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($bundles))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">الحِزم</h2>
                <a href="{{ route('panel.v1.admin.education.bundles.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة حزمة</a>
            </div>
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الحزمة</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                <tbody>
                @foreach ($bundles as $b)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $b->title ?? 'حزمة #'.$b->id }}</td><td class="px-4 py-3 text-center">{{ $b->status ?? '—' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-bundle-'.$b->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.education.bundles.edit',['id'=>$b->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.bundles.delete',['id'=>$b->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($assignments))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">التكليفات</h2>
                <a href="{{ route('panel.v1.admin.education.assignments.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة تكليف</a>
            </div>
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">التكليف</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                <tbody>
                @foreach ($assignments as $a)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $a->title ?? 'تكليف #'.$a->id }}</td><td class="px-4 py-3 text-center">{{ $a->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-assign-'.$a->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.assignments.delete',['id'=>$a->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($quizzes))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">الاختبارات</h2>
                <a href="{{ route('panel.v1.admin.education.quizzes.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة اختبار</a>
            </div>
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الاختبار</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                <tbody>
                @foreach ($quizzes as $q)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $q->title }}</td><td class="px-4 py-3 text-center">{{ $q->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ $q->status }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-quiz-'.$q->id, 'items' => [['label' => 'الأسئلة', 'url' => route('panel.v1.admin.education.quizzes.questions',['id'=>$q->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.quizzes.delete',['id'=>$q->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($certificates))
        @if (!empty($certificateStats))
            @include('panel_v1.admin.components.stats-cards', ['stats' => $certificateStats])
        @endif
        <div class="px-2 sm:px-0 mb-3 flex flex-wrap gap-2">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap gap-2 items-center">
                @foreach (request()->except(['type','page']) as $k=>$v) @if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
                <select name="type" onchange="this.form.submit()" class="select select-bordered h-9 rounded-10px border-d9 text-12px bg-white">
                    <option value="">كل الأنواع</option>
                    <option value="course" @selected(request('type')=='course')>إتمام دورة</option>
                    <option value="quiz" @selected(request('type')=='quiz')>اختبار</option>
                    <option value="bundle" @selected(request('type')=='bundle')>حزمة</option>
                </select>
                <a href="{{ url('/certificate_validation') }}" target="_blank" class="h-9 px-3 rounded-10px border border-d9 bg-white text-12px font-semibold text-primary center gap-1"><span class="icon-[tabler--shield-check] size-4"></span> صفحة التحقق</a>
            </form>
        </div>
        <div class="border border-d9 rounded-14px bg-white overflow-hidden mb-4">
            <div class="px-4 py-3 bg-[#FAFAF4] border-b border-d9 flex items-center justify-between">
                <h3 class="font-bold text-14px text-primary">قوالب الشهادات</h3>
                <a href="{{ route('panel.v1.admin.education.certificates.templates.create') }}" class="h-9 px-4 rounded-10px bg-primary text-white font-bold text-12px center">+ قالب جديد</a>
            </div>
            @if (!empty($certificateTemplates) && $certificateTemplates->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4">
                    @foreach($certificateTemplates as $tmpl)
                        <div class="rounded-12px border border-d9 p-3 flex items-center gap-3">
                            <div class="size-10 rounded-8px bg-primary/10 center shrink-0"><span class="icon-[tabler--certificate] size-5 text-primary"></span></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-13px text-primary truncate">{{ $tmpl->title ?? 'قالب #' . $tmpl->id }}</p>
                                <p class="font-medium text-11px text-gray">{{ $tmpl->type ?? '' }} — {{ $tmpl->status ?? '' }}</p>
                            </div>
                            <div class="shrink-0">
                                @include('panel_v1.components.actions-dropdown', ['id' => 'edu-cert-tmpl-'.$tmpl->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.education.certificates.templates.edit',['id'=>$tmpl->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.certificates.templates.delete',['id'=>$tmpl->id]), 'confirm' => 'حذف القالب؟', 'tone' => 'danger']]])
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="font-medium text-13px text-gray text-center py-8">لا توجد قوالب — أنشئ أول قالب شهادة</p>
            @endif
        </div>
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-13px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-3 py-3 text-start">الشهادة</th><th class="px-3 py-3">النوع</th><th class="px-3 py-3">الطالب</th><th class="px-3 py-3">الدورة/الاختبار</th><th class="px-3 py-3">التاريخ</th><th class="px-3 py-3">تحقق</th><th class="px-3 py-3 text-center">إجراءات</th></tr></thead>
                <tbody>
                @foreach ($certificates as $c)
                    @php
                        $cTitle = $c->type==='quiz' ? ($c->quiz->title ?? 'اختبار #'.$c->quiz_id) : ($c->type==='bundle' ? ($c->bundle->title ?? 'حزمة #'.$c->bundle_id) : ($c->webinar->title ?? '—'));
                        $typeLabel = $c->type==='quiz' ? 'اختبار' : ($c->type==='bundle' ? 'حزمة' : 'إتمام');
                        $validUrl = url('/certificate_validation?certificate_id='.$c->id);
                    @endphp
                    <tr class="border-b border-d9 last:border-0">
                        <td class="px-3 py-3 font-bold text-primary font-mono">#{{ $c->id }}</td>
                        <td class="px-3 py-3 text-center"><span class="inline-flex rounded-full px-2 py-1 font-bold text-11px {{ $c->type==='quiz' ? 'bg-[#EDE9FE] text-[#6D28D9]' : ($c->type==='bundle' ? 'bg-[#FEF3C7] text-[#92400E]' : 'bg-[#D1FAE5] text-[#065F46]') }}">{{ $typeLabel }}</span></td>
                        <td class="px-3 py-3 text-center">{{ $c->student->full_name ?? '' }}<br><span class="text-11px text-gray">{{ $c->student->email ?? '' }}</span></td>
                        <td class="px-3 py-3 text-center truncate max-w-[14rem]">{{ $cTitle }}</td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">{{ date('Y/m/d', (int)$c->created_at) }}</td>
                        <td class="px-3 py-3 text-center"><a href="{{ $validUrl }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-11px text-primary hover:underline"><span class="icon-[tabler--qrcode] size-3.5"></span> تحقق</a></td>
                        <td class="px-3 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-cert-'.$c->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.certificates.delete',['id'=>$c->id]), 'confirm' => 'حذف الشهادة #'.$c->id.'؟', 'tone' => 'danger']]])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($lives))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الجلسة</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                <tbody>
                @foreach ($lives as $l)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $l->title ?? 'جلسة #'.$l->id }}</td><td class="px-4 py-3 text-center">{{ $l->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ date('Y/m/d H:i',(int)$l->date) }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-live-'.$l->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.live.delete',['id'=>$l->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($events))
        @if (!empty($eventStats))
            @include('panel_v1.admin.components.stats-cards', ['stats' => $eventStats])
        @endif
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold text-16px text-primary">الفعاليات</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('panel.v1.admin.education.events.export', request()->query()) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"><span class="icon-[tabler--file-spreadsheet] size-4"></span> تصدير Excel</a>
                    <a href="{{ route('panel.v1.admin.education.events.create') }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px hover:opacity-95 transition">+ إضافة فعالية</a>
                </div>
            </div>
            @if (!empty($filterCategories))
                <div class="px-4 sm:px-6 py-3 bg-white border-b border-d9">
                    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap gap-2 w-full">
                        {{-- الحفاظ على البحث وأي باراميتر آخر عند تغيير قائمة منسدلة --}}
                        @foreach (request()->except(['category_id', 'type', 'status', 'page']) as $fkey => $fval)
                            @if(!is_array($fval))
                                <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                            @endif
                        @endforeach
                        <select name="category_id" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px min-w-[12rem] bg-white">
                            <option value="">كل التصنيفات</option>
                            @foreach ($filterCategories as $fc)
                                <option value="{{ $fc['id'] }}" @selected(request('category_id')==$fc['id'])>{{ $fc['title'] }}</option>
                            @endforeach
                        </select>
                        <select name="type" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px min-w-[10rem] bg-white">
                            <option value="">كل الأنواع</option>
                            <option value="online" @selected(request('type')=='online')>عن بُعد</option>
                            <option value="in_person" @selected(request('type')=='in_person')>حضوري</option>
                        </select>
                        <select name="status" onchange="this.form.submit()" class="select select-bordered h-10 rounded-10px border-d9 text-13px min-w-[10rem] bg-white">
                            <option value="">كل الحالات</option>
                            <option value="draft" @selected(request('status')=='draft')>مسودة</option>
                            <option value="publish" @selected(request('status')=='publish')>منشور</option>
                            <option value="unpublish" @selected(request('status')=='unpublish')>غير منشور</option>
                        </select>
                        @if(request('category_id') || request('type') || request('status'))
                            <a href="{{ url()->current() }}" class="inline-flex items-center h-10 px-4 rounded-10px border border-d9 bg-white text-13px font-medium text-primary">مسح الفلتر</a>
                        @endif
                    </form>
                </div>
            @endif
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الفعالية</th><th class="px-4 py-3">التصنيف</th><th class="px-4 py-3">المنشئ</th><th class="px-4 py-3">النوع</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3">البدء</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach ($events as $e)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $e->title ?? 'فعالية #'.$e->id }}</td>
                            <td class="px-4 py-3 text-center">{{ $e->category->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $e->creator->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ ($e->type ?? '') === 'in_person' ? 'حضوري' : 'عن بُعد' }}</td>
                            <td class="px-4 py-3 text-center"><span @class(['inline-flex rounded-full px-2.5 py-1 font-semibold text-11px','bg-[#D1FAE5] text-[#059669]'=>in_array($e->status,['publish','active']),'bg-[#FEF3C7] text-[#D97706]'=>in_array($e->status,['draft','pending']),'bg-[#FEE2E2] text-[#DC2626]'=>in_array($e->status,['rejected','canceled','inactive'])])>{{ $e->status ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-center">{{ !empty($e->start_date) ? date('Y/m/d',(int)$e->start_date) : '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $eventActionItems = [
                                        ['label' => 'تعديل', 'url' => route('panel.v1.admin.education.events.edit',['id'=>$e->id]), 'tone' => 'gray'],
                                    ];
                                    if (!in_array($e->status, ['publish'])) {
                                        $eventActionItems[] = ['label' => 'نشر', 'action' => route('panel.v1.admin.education.events.status',['id'=>$e->id,'status'=>'publish']), 'tone' => 'success'];
                                    }
                                    $eventActionItems[] = ['label' => 'حذف', 'action' => route('panel.v1.admin.education.events.delete',['id'=>$e->id]), 'confirm' => 'حذف الفعالية؟', 'tone' => 'danger'];
                                @endphp
                                @include('panel_v1.components.actions-dropdown', [
                                    'id' => 'edu-event-'.$e->id,
                                    'items' => $eventActionItems,
                                ])
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($reviews))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">التعليق</th><th class="px-4 py-3">المستخدم</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                <tbody>
                @foreach ($reviews as $r)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ \Illuminate\Support\Str::limit(strip_tags($r->comment ?? ''),50) }}</td><td class="px-4 py-3 text-center">{{ $r->user->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $r->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-review-'.$r->id, 'items' => [['label' => 'اعتماد', 'action' => route('panel.v1.admin.education.reviews.approve',['id'=>$r->id]), 'tone' => 'success'], ['label' => 'إرجاع للانتظار', 'action' => route('panel.v1.admin.education.reviews.reject',['id'=>$r->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.reviews.delete',['id'=>$r->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($quizzesResults))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold text-16px text-primary">نتائج الاختبار — {{ $quizzesResults->total() }}</h2>
                <a href="{{ route('panel.v1.admin.education.quiz-results.export', ['quizId' => $quiz_id ?? 0]) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 bg-white font-semibold text-13px text-primary hover:bg-[#FAFAF4] transition"><span class="icon-[tabler--file-spreadsheet] size-4"></span> تصدير Excel</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الطالب</th><th class="px-4 py-3 text-center">الدرجة</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach ($quizzesResults as $qr)
                        @php
                            $qrStatusLabel = ($qr->status ?? '') === \App\Models\QuizzesResult::$passed ? 'ناجح' : ((($qr->status ?? '') === \App\Models\QuizzesResult::$failed) ? 'راسب' : 'بانتظار المراجعة');
                        @endphp
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $qr->user->full_name ?? '' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-primary">{{ $qr->user_grade ?? 0 }}</td>
                            <td class="px-4 py-3 text-center"><span @class(['inline-flex rounded-full px-2.5 py-1 font-semibold text-11px','bg-[#D1FAE5] text-[#059669]'=>($qr->status ?? '')===\App\Models\QuizzesResult::$passed,'bg-[#FEE2E2] text-[#DC2626]'=>($qr->status ?? '')===\App\Models\QuizzesResult::$failed,'bg-[#FEF3C7] text-[#D97706]'=>!in_array(($qr->status ?? ''),[\App\Models\QuizzesResult::$passed,\App\Models\QuizzesResult::$failed], true)])>{{ $qrStatusLabel }}</span></td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ !empty($qr->created_at) ? date('Y/m/d', (int)$qr->created_at) : '—' }}</td>
                            <td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-qr-'.$qr->id, 'items' => [['label' => 'مراجعة', 'url' => route('panel.v1.admin.education.quiz-results.review',['quizId'=>$quiz_id ?? $qr->quiz_id,'resultId'=>$qr->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.quiz-results.delete',['quizId'=>$quiz_id ?? $qr->quiz_id,'resultId'=>$qr->id]), 'confirm' => 'حذف النتيجة؟', 'tone' => 'danger']]])</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($relatedCourses))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden p-4">
            <form method="POST" action="{{ route('panel.v1.admin.education.related-courses.store') }}" class="flex flex-wrap gap-2 mb-4">
                @csrf
                <input type="hidden" name="item_id" value="{{ $itemId ?? request('item_id') }}">
                <input type="hidden" name="item_type" value="{{ $itemType ?? request('item_type','webinar') }}">
                <input type="number" name="course_id" placeholder="معرف الدورة المرتبطة" class="input input-bordered flex-1 h-10 rounded-10px text-14px min-w-[12rem]" required>
                <button type="submit" class="btn btn-primary rounded-10px h-10 px-5 font-bold text-13px">إضافة ارتباط</button>
            </form>
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدورة المرتبطة</th><th class="px-4 py-3 text-center">المدرب</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>@foreach($relatedCourses as $rc)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $rc->course->title ?? 'دورة #'.$rc->course_id }}</td><td class="px-4 py-3 text-center">{{ $rc->course->teacher->full_name ?? '—' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-rc-'.$rc->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.related-courses.delete',['id'=>$rc->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@endforeach</tbody></table>
        </div>
    @endif

    @if (!empty($departments))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden p-4">
            <form method="POST" action="{{ route('panel.v1.admin.education.departments.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="title" placeholder="اسم القسم الجديد" class="input input-bordered flex-1 h-10 rounded-10px text-14px" required>
                <button type="submit" class="btn btn-primary rounded-10px h-10 px-5 font-bold text-13px">إضافة</button>
            </form>
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">القسم</th><th class="px-4 py-3">الترتيب</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead><tbody>@foreach($departments as $d)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $d->title }}</td><td class="px-4 py-3 text-center">{{ $d->order ?? '—' }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-dept-'.$d->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.education.departments.edit',['id'=>$d->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.departments.delete',['id'=>$d->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@endforeach</tbody></table>
        </div>
    @endif

    @if (!empty($attendances))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الجلسة</th><th class="px-4 py-3">الطالب</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>@foreach($attendances as $a)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $a->session->title ?? 'جلسة #'.$a->session_id }}</td><td class="px-4 py-3 text-center">{{ $a->student->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $a->status }}</td><td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-att-'.$a->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.attendances.delete',['id'=>$a->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td></tr>@endforeach</tbody></table>
        </div>
    @endif

    {{-- إدارة متقدمة — كانت تظهر قريباً لأنها بلا بلوك عرض — الآن حقيقية 100% --}}
    @if (!empty($filters))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">الفلاتر — {{ $filters->total() }}</h2>
                <a href="{{ route('panel.v1.admin.education.filters.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة فلتر</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">الفلتر</th><th class="px-4 py-3 text-center">التصنيف</th><th class="px-4 py-3 text-center">الخيارات</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach($filters as $f)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $f->id }}</td>
                            <td class="px-4 py-3 font-bold text-primary">{{ $f->title ?? 'فلتر #'.$f->id }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $f->category->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-primary">{{ $f->options->count() ?? $f->filterOptions->count() ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @include('panel_v1.components.actions-dropdown', ['id' => 'edu-filter-'.$f->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.education.filters.edit',['id'=>$f->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.filters.delete',['id'=>$f->id]), 'confirm' => 'حذف الفلتر؟', 'tone' => 'danger']]])
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($trends))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">التصنيفات الرائجة — {{ $trends->total() }}</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('panel.v1.admin.education.trends.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة رائج</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">التصنيف</th><th class="px-4 py-3 text-center">الأيقونة</th><th class="px-4 py-3 text-center">اللون</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach($trends as $t)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $t->id }}</td>
                            <td class="px-4 py-3 font-bold text-primary">{{ $t->category->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="{{ $t->icon }} size-5 text-primary inline-block"></span> <span class="font-mono text-11px text-gray">{{ $t->icon }}</span></td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex items-center gap-2"><span class="size-5 rounded-full border border-d9" style="background: {{ $t->color }}"></span><span class="font-mono text-11px text-gray">{{ $t->color }}</span></span></td>
                            <td class="px-4 py-3 text-center">
                                @include('panel_v1.components.actions-dropdown', ['id' => 'edu-trend-'.$t->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.education.trends.edit',['id'=>$t->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.trends.delete',['id'=>$t->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($sales))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">التسجيل — {{ $sales->total() }} عملية</h2>
                <span class="font-medium text-12px text-gray">سجل التسجيلات المدفوعة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">#</th><th class="px-4 py-3 text-start">الطالب</th><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3 text-center">المبلغ</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">الحالة</th></tr></thead>
                    <tbody>
                    @foreach($sales as $s)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">#{{ $s->id }}</td>
                            <td class="px-4 py-3 font-medium text-primary">{{ $s->buyer->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-primary truncate max-w-[16rem]">{{ $s->webinar->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-primary">{{ handlePrice($s->total_amount ?? $s->amount ?? 0) }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$s->created_at) }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-2.5 py-1 font-semibold text-11px {{ empty($s->refund_at) ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEE2E2] text-[#DC2626]' }}">{{ empty($s->refund_at) ? 'مكتمل' : 'مسترد' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($upcomingCourses))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">الدورات القادمة — {{ $upcomingCourses->total() }}</h2>
                <a href="{{ route('panel.v1.admin.education.upcoming.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إضافة</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3 text-center">المتابعون</th><th class="px-4 py-3 text-center">المدرب</th><th class="px-4 py-3 text-center">الحالة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                    <tbody>
                    @foreach($upcomingCourses as $u)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary truncate max-w-[18rem]">{{ $u->title ?? 'دورة #'.$u->id }}</td>
                            <td class="px-4 py-3 text-center font-bold text-primary">{{ $u->followers_count ?? $u->followers->count() ?? 0 }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ $u->teacher->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span class="inline-flex rounded-full px-2.5 py-1 font-semibold text-11px {{ ($u->status ?? '')==='active' ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-[#FEF3C7] text-[#D97706]' }}">{{ $u->status ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-center">
                                @include('panel_v1.components.actions-dropdown', ['id' => 'edu-upcoming-'.$u->id, 'items' => [['label' => 'تعديل', 'url' => route('panel.v1.admin.education.upcoming.edit',['id'=>$u->id]), 'tone' => 'gray'], ['label' => 'حذف', 'action' => route('panel.v1.admin.education.upcoming.delete',['id'=>$u->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($waitlists))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4]">
                <h2 class="font-bold text-16px text-primary">قوائم الانتظار — {{ $waitlists->total() }} دورة مفعّلة</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3 text-center">الأعضاء</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach($waitlists as $w)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary truncate max-w-[20rem]">{{ $w->title ?? 'دورة #'.$w->id }}</td>
                            <td class="px-4 py-3 text-center font-bold text-primary">{{ $w->members ?? 0 }}</td>
                            <td class="px-4 py-3 text-center"><a href="{{ route('panel.v1.admin.education.waitlists.view',['webinarId'=>$w->id]) }}" class="inline-flex h-8 px-3 rounded-8px bg-primary/10 text-primary font-semibold text-12px center hover:bg-primary/15">عرض الأعضاء</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($noticeboards))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex items-center justify-between">
                <h2 class="font-bold text-16px text-primary">لوح الإعلانات — {{ $noticeboards->total() }}</h2>
                <a href="{{ route('panel.v1.admin.education.noticeboard.create') }}" class="h-10 px-4 rounded-12px bg-primary text-white font-bold text-13px center">+ إعلان جديد</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العنوان</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach($noticeboards as $n)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary truncate max-w-[20rem]">{{ $n->title ?? 'إعلان #'.$n->id }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$n->created_at) }}</td>
                            <td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-notice-'.$n->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.noticeboard.delete',['id'=>$n->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($waitlistItems))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-d9 bg-[#FAFAF4] flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-bold text-16px text-primary">قائمة الانتظار — {{ $webinar->title ?? '' }} ({{ $waitlistItems->total() }})</h2>
                <form method="POST" action="{{ route('panel.v1.admin.education.waitlists.delete-all', ['webinarId' => $webinar->id ?? 0]) }}" onsubmit="return confirm('حذف كل الأعضاء؟')" class="inline">@csrf<button type="submit" class="h-10 px-4 rounded-12px border border-red-200 bg-white font-semibold text-13px text-red-500 hover:bg-red-50 transition">حذف الكل</button></form>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full text-14px">
                    <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">العضو</th><th class="px-4 py-3 text-center">الهاتف</th><th class="px-4 py-3 text-center">التاريخ</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead>
                    <tbody>
                    @foreach($waitlistItems as $w)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-3 font-bold text-primary">{{ $w->full_name ?? 'عضو #'.$w->id }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray" dir="ltr">{{ $w->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray">{{ date('Y/m/d', (int)$w->created_at) }}</td>
                            <td class="px-4 py-3 text-center">@include('panel_v1.components.actions-dropdown', ['id' => 'edu-wl-item-'.$w->id, 'items' => [['label' => 'حذف', 'action' => route('panel.v1.admin.education.waitlists.delete',['id'=>$w->id]), 'confirm' => 'حذف؟', 'tone' => 'danger']]])</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!empty($stats) && empty($courses) && empty($bundles) && empty($assignments) && empty($quizzes) && empty($certificates) && empty($lives) && empty($events) && empty($reviews) && empty($departments) && empty($attendances) && empty($filters) && empty($trends) && empty($sales) && empty($upcomingCourses) && empty($waitlists) && empty($waitlistItems) && empty($noticeboards) && empty($quizzesResults) && empty($relatedCourses))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stats as $st)
                <div class="border border-d9 rounded-14px bg-white p-6 text-center">
                    <p class="font-bold text-28px text-primary leading-none mb-2">{{ is_array($st) ? ($st['value'] ?? $st['count'] ?? '—') : $st }}</p>
                    <p class="font-semibold text-14px text-gray">{{ is_array($st) ? ($st['label'] ?? 'إحصائية') : 'إحصائية' }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if (empty($courses) && empty($bundles) && empty($assignments) && empty($quizzes) && empty($certificates) && empty($lives) && empty($events) && empty($reviews) && empty($departments) && empty($attendances) && empty($filters) && empty($trends) && empty($sales) && empty($upcomingCourses) && empty($waitlists) && empty($waitlistItems) && empty($noticeboards) && empty($quizzesResults) && empty($relatedCourses) && empty($stats))
        @include('panel_v1.admin.components.empty-stub')
    @endif

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
