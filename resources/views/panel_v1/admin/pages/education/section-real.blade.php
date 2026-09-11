@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    <h1 class="font-bold text-26px text-primary">{{ $stubTitle ?? $pageTitle }}</h1>

    @if (!empty($courses))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الدورة</th><th class="px-4 py-3">التصنيف</th><th class="px-4 py-3">الحالة</th><th class="px-4 py-3">السعر</th></tr></thead>
                <tbody>
                @foreach ($courses as $c)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $c->title }}</td><td class="px-4 py-3 text-center">{{ $c->category->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ $c->status }}</td><td class="px-4 py-3 text-center">{{ $c->price ? handlePrice($c->price) : 'مجانية' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($bundles))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الحزمة</th><th class="px-4 py-3">الحالة</th></tr></thead>
                <tbody>
                @foreach ($bundles as $b)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $b->title ?? 'حزمة #'.$b->id }}</td><td class="px-4 py-3 text-center">{{ $b->status ?? '—' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($assignments))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">التكليف</th><th class="px-4 py-3">الدورة</th></tr></thead>
                <tbody>
                @foreach ($assignments as $a)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $a->title ?? 'تكليف #'.$a->id }}</td><td class="px-4 py-3 text-center">{{ $a->webinar->title ?? '' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($quizzes))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الاختبار</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3">الحالة</th></tr></thead>
                <tbody>
                @foreach ($quizzes as $q)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $q->title }}</td><td class="px-4 py-3 text-center">{{ $q->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ $q->status }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($certificates))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الشهادة</th><th class="px-4 py-3">الطالب</th><th class="px-4 py-3">الدورة</th></tr></thead>
                <tbody>
                @foreach ($certificates as $c)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">#{{ $c->id }}</td><td class="px-4 py-3 text-center">{{ $c->student->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $c->webinar->title ?? '' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($lives))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الجلسة</th><th class="px-4 py-3">الدورة</th><th class="px-4 py-3">التاريخ</th></tr></thead>
                <tbody>
                @foreach ($lives as $l)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $l->title ?? 'جلسة #'.$l->id }}</td><td class="px-4 py-3 text-center">{{ $l->webinar->title ?? '' }}</td><td class="px-4 py-3 text-center">{{ date('Y/m/d H:i',(int)$l->date) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($events))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الفعالية</th><th class="px-4 py-3">التاريخ</th></tr></thead>
                <tbody>
                @foreach ($events as $e)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $e->title ?? 'فعالية #'.$e->id }}</td><td class="px-4 py-3 text-center">{{ date('Y/m/d',(int)$e->created_at) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($forums))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الموضوع</th><th class="px-4 py-3">الكاتب</th></tr></thead>
                <tbody>
                @foreach ($forums as $f)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $f->title }}</td><td class="px-4 py-3 text-center">{{ $f->creator->full_name ?? '' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($notifications))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الإشعار</th><th class="px-4 py-3">النوع</th></tr></thead>
                <tbody>
                @foreach ($notifications as $n)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $n->title ?? 'إشعار #'.$n->id }}</td><td class="px-4 py-3 text-center">{{ $n->type }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($reviews))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px">
                <thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">التعليق</th><th class="px-4 py-3">المستخدم</th><th class="px-4 py-3">الدورة</th></tr></thead>
                <tbody>
                @foreach ($reviews as $r)
                    <tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ \Illuminate\Support\Str::limit(strip_tags($r->comment ?? ''),50) }}</td><td class="px-4 py-3 text-center">{{ $r->user->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $r->webinar->title ?? '' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (!empty($registrations))
        <div class="border border-d9 rounded-14px bg-white overflow-hidden">
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">المستخدم</th><th class="px-4 py-3">البريد</th><th class="px-4 py-3">الحالة</th></tr></thead><tbody>@foreach($registrations as $u)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $u->full_name }}</td><td class="px-4 py-3 text-center">{{ $u->email }}</td><td class="px-4 py-3 text-center">{{ $u->status }}</td></tr>@endforeach</tbody></table>
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
            <table class="table w-full text-14px"><thead><tr class="bg-fa border-b border-d9 text-gray"><th class="px-4 py-3 text-start">الجلسة</th><th class="px-4 py-3">الطالب</th><th class="px-4 py-3">الحالة</th></tr></thead><tbody>@foreach($attendances as $a)<tr class="border-b border-d9 last:border-0"><td class="px-4 py-3 font-bold text-primary">{{ $a->session->title ?? 'جلسة #'.$a->session_id }}</td><td class="px-4 py-3 text-center">{{ $a->student->full_name ?? '' }}</td><td class="px-4 py-3 text-center">{{ $a->status }}</td></tr>@endforeach</tbody></table>
        </div>
    @endif

    @if (empty($courses) && empty($bundles) && empty($assignments) && empty($quizzes) && empty($certificates) && empty($lives) && empty($events) && empty($forums) && empty($notifications) && empty($reviews) && empty($registrations) && empty($departments) && empty($attendances))
        @include('panel_v1.admin.components.empty-stub')
    @endif
</div>
@endsection
