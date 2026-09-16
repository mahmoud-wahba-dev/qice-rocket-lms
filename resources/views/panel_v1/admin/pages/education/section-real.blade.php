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
                            <td class="px-4 py-3 text-center"><span @class(['inline-flex rounded-full px-2.5 py-1 font-semibold text-11px','bg-[#D1FAE5] text-[#059669]'=>$c->status=='active','bg-[#FEF3C7] text-[#D97706]'=>$c->status=='pending','bg-[#FEE2E2] text-[#DC2626]'=>$c->status=='inactive','bg-[#EFF6FF] text-[#2563EB]'=>!in_array($c->status,['active','pending','inactive'],'')])>{{ $c->status }}</span></td>
                            <td class="px-4 py-3 text-center">{{ $c->price ? handlePrice($c->price) : 'مجانية' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('panel.v1.admin.education.courses.edit',['id'=>$c->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تعديل"><span class="icon-[tabler--edit] size-4 text-primary"></span></a>
                                    <a href="{{ route('panel.v1.admin.education.courses.curriculum',['id'=>$c->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="المنهج"><span class="icon-[tabler--list-check] size-4 text-primary"></span></a>
                                    <a href="{{ route('panel.v1.admin.education.courses.notify',['id'=>$c->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="إشعار الطلاب"><span class="icon-[tabler--bell] size-4 text-primary"></span></a>
                                    @if($c->status!=='active')
                                        <form method="POST" action="{{ route('panel.v1.admin.education.courses.approve',['id'=>$c->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#D1FAE5] center hover:opacity-90" title="موافقة"><span class="icon-[tabler--check] size-4 text-[#059669]"></span></button></form>
                                    @endif
                                    @if($c->status!=='inactive')
                                        <form method="POST" action="{{ route('panel.v1.admin.education.courses.reject',['id'=>$c->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#FEE2E2] center hover:opacity-90" title="رفض"><span class="icon-[tabler--x] size-4 text-[#DC2626]"></span></button></form>
                                    @endif
                                    <form method="POST" action="{{ route('panel.v1.admin.education.courses.delete',['id'=>$c->id]) }}" onsubmit="return confirm('حذف الدورة؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center hover:bg-red-50" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form>
                                </div>
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
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $b->title ?? 'حزمة #'.$b->id }}</td><td class="px-4 py-3 text-center">{{ $b->status ?? '—' }}</td><td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><a href="{{ route('panel.v1.admin.education.bundles.edit',['id'=>$b->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تعديل"><span class="icon-[tabler--edit] size-4 text-primary"></span></a><form method="POST" action="{{ route('panel.v1.admin.education.bundles.delete',['id'=>$b->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center hover:bg-red-50" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></div></td></tr>
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
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $a->title ?? 'تكليف #'.$a->id }}</td><td class="px-4 py-3 text-center">{{ $a->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.assignments.delete',['id'=>$a->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td></tr>
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
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $q->title }}</td><td class="px-4 py-3 text-center">{{ $q->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ $q->status }}</td><td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><a href="{{ route('panel.v1.admin.education.quizzes.questions',['id'=>$q->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="الأسئلة"><span class="icon-[tabler--list-check] size-4 text-primary"></span></a><form method="POST" action="{{ route('panel.v1.admin.education.quizzes.delete',['id'=>$q->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></div></td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($certificates))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الشهادة</th><th class="px-4 py-3">الطالب</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3 text-center">إجراءات</th></tr></thead>
                <tbody>
                @foreach ($certificates as $c)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $c->id }}</td><td class="px-4 py-3 text-center">{{ $c->student->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $c->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.certificates.delete',['id'=>$c->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td></tr>
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
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $l->title ?? 'جلسة #'.$l->id }}</td><td class="px-4 py-3 text-center">{{ $l->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ date('Y/m/d H:i',(int)$l->date) }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.live.delete',['id'=>$l->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td></tr>
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
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('panel.v1.admin.education.events.edit',['id'=>$e->id]) }}" class="size-8 rounded-8px border border-d9 center hover:bg-[#FAFAF4]" title="تعديل"><span class="icon-[tabler--edit] size-4 text-primary"></span></a>
                                    @if(!in_array($e->status,['publish']))
                                        <form method="POST" action="{{ route('panel.v1.admin.education.events.status',['id'=>$e->id,'status'=>'publish']) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#D1FAE5] center hover:opacity-90" title="نشر"><span class="icon-[tabler--check] size-4 text-[#059669]"></span></button></form>
                                    @endif
                                    <form method="POST" action="{{ route('panel.v1.admin.education.events.delete',['id'=>$e->id]) }}" onsubmit="return confirm('حذف الفعالية؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center hover:bg-red-50" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form>
                                </div>
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
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ \Illuminate\Support\Str::limit(strip_tags($r->comment ?? ''),50) }}</td><td class="px-4 py-3 text-center">{{ $r->user->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $r->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center"><div class="flex items-center justify-center gap-1.5"><form method="POST" action="{{ route('panel.v1.admin.education.reviews.approve',['id'=>$r->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#D1FAE5] center hover:opacity-90" title="اعتماد"><span class="icon-[tabler--check] size-4 text-[#059669]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.education.reviews.reject',['id'=>$r->id]) }}" class="inline">@csrf<button type="submit" class="size-8 rounded-8px bg-[#FEF3C7] center hover:opacity-90" title="إرجاع للانتظار"><span class="icon-[tabler--x] size-4 text-[#D97706]"></span></button></form><form method="POST" action="{{ route('panel.v1.admin.education.reviews.delete',['id'=>$r->id]) }}" onsubmit="return confirm('حذف؟')" class="inline">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center" title="حذف"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></div></td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($departments))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden p-4">
            <form method="POST" action="{{ route('panel.v1.admin.education.departments.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="title" placeholder="اسم القسم الجديد" class="input input-bordered flex-1 h-10 rounded-10px text-14px" required>
                <button type="submit" class="btn btn-primary rounded-10px h-10 px-5 font-bold text-13px">إضافة</button>
            </form>
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">القسم</th><th class="px-4 py-3">الترتيب</th><th class="px-4 py-3">إجراء</th></tr></thead><tbody>@foreach($departments as $d)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $d->title }}</td><td class="px-4 py-3 text-center">{{ $d->order ?? '—' }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.departments.delete',['id'=>$d->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="text-red-500 font-bold text-12px">حذف</button></form></td></tr>@endforeach</tbody></table>
        </div>
    @endif

    @if (!empty($attendances))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الجلسة</th><th class="px-4 py-3">الطالب</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3 text-center">إجراء</th></tr></thead><tbody>@foreach($attendances as $a)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $a->session->title ?? 'جلسة #'.$a->session_id }}</td><td class="px-4 py-3 text-center">{{ $a->student->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $a->status }}</td><td class="px-4 py-3 text-center"><form method="POST" action="{{ route('panel.v1.admin.education.attendances.delete',['id'=>$a->id]) }}" onsubmit="return confirm('حذف؟')">@csrf<button type="submit" class="size-8 rounded-8px border border-red-200 center"><span class="icon-[tabler--trash] size-4 text-red-500"></span></button></form></td></tr>@endforeach</tbody></table>
        </div>
    @endif

    @if (empty($courses) && empty($bundles) && empty($assignments) && empty($quizzes) && empty($certificates) && empty($lives) && empty($events) && empty($reviews) && empty($departments) && empty($attendances))
        @include('panel_v1.admin.components.empty-stub')
    @endif

    @if (!empty($paginator))
        @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
    @endif
</div>
@endsection
