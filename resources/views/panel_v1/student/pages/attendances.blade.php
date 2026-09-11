@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-extrabold text-36px text-primary mb-3">سجل الحضور</h1>
                <p class="font-semibold text-20px text-gray">متابعة حضورك في الجلسات المباشرة</p>
            </div>
            <div class="flex gap-3">
                <div class="rounded-12px bg-[#E8F5E9] border border-[#A7F3D0] px-5 py-3 text-center">
                    <p class="font-bold text-14px text-[#065F46]">{{ $presentCount ?? 0 }} حاضر</p>
                </div>
                <div class="rounded-12px bg-fa border border-d9 px-5 py-3 text-center">
                    <p class="font-bold text-14px text-gray">{{ $myAttendances ?? 0 }} إجمالي</p>
                </div>
            </div>
        </div>

        @if (($attendances ?? collect())->isEmpty())
            <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                <p class="font-bold text-20px text-gray">لا يوجد سجل حضور بعد</p>
                <p class="font-medium text-14px text-gray mt-2">سيظهر هنا حضورك في الجلسات المباشرة.</p>
            </div>
        @else
            <div class="border border-d9 rounded-16px bg-white overflow-hidden">
                <div class="hidden md:grid md:grid-cols-[2fr_1fr_1fr_1fr] gap-4 px-6 py-4 bg-fa border-b border-d9 font-bold text-13px text-gray">
                    <span>الجلسة</span>
                    <span class="text-center">الدورة</span>
                    <span class="text-center">الحالة</span>
                    <span class="text-center">التاريخ</span>
                </div>
                @foreach ($attendances as $att)
                    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr] gap-3 px-6 py-5 border-b border-d9 last:border-0 items-center">
                        <p class="font-bold text-15px text-primary">{{ $att->session->title ?? 'جلسة #' . $att->session_id }}</p>
                        <p class="hidden md:block font-medium text-13px text-gray text-center">{{ $att->session->webinar->title ?? '' }}</p>
                        <div class="text-center">
                            <span class="inline-flex rounded-full px-3 py-1 font-bold text-12px {{ ($att->status ?? '')==='present' ? 'bg-[#E8F5E9] text-[#00B31B]' : (($att->status ?? '')==='late' ? 'bg-[#FEF6E7] text-[#D97706]' : 'bg-[#FEF2F2] text-[#EF4444]') }}">
                                {{ ($att->status ?? '')==='present' ? 'حاضر' : (($att->status ?? '')==='late' ? 'متأخر' : (($att->status ?? '')==='absent' ? 'غائب' : $att->status)) }}
                            </span>
                        </div>
                        <p class="font-medium text-13px text-gray text-center">{{ date('Y/m/d H:i', (int) ($att->created_at ?? $att->session->date ?? time())) }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
