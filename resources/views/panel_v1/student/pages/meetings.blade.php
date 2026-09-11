@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-extrabold text-36px text-primary mb-3">جلساتي الاستشارية</h1>
                <p class="font-semibold text-20px text-gray">حجوزاتك مع المدربين والجلسات القادمة</p>
            </div>
            <div class="flex gap-3">
                <div class="rounded-12px bg-[#E8F5E9] border border-[#A7F3D0] px-5 py-3 text-center">
                    <p class="font-bold text-14px text-[#065F46]">{{ $openCount ?? 0 }} مفتوحة</p>
                </div>
                <div class="rounded-12px bg-fa border border-d9 px-5 py-3 text-center">
                    <p class="font-bold text-14px text-gray">{{ $finishedCount ?? 0 }} منتهية</p>
                </div>
            </div>
        </div>

        @if (($reserveMeetings ?? collect())->isEmpty())
            <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                <div class="size-16 rounded-full bg-primary/10 center mb-4">
                    <span class="icon-[tabler--calendar] size-8 text-primary"></span>
                </div>
                <p class="font-bold text-20px text-primary mb-2">لا توجد حجوزات بعد</p>
                <p class="font-medium text-14px text-gray">احجز جلسة استشارية مع مدرب من صفحة المدربين.</p>
                <a href="{{ route('landing.v1.instructors') }}" class="btn btn-primary rounded-10px h-11 px-6 font-bold text-14px mt-5">استعرض المدربين</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($reserveMeetings as $reserve)
                    <div class="border border-d9 rounded-16px bg-white px-6 py-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-bold text-18px text-primary">{{ $reserve->meeting->creator->full_name ?? 'مدرب' }}</p>
                                <p class="font-medium text-14px text-gray">
                                    {{ $reserve->day ?? '' }} — {{ !empty($reserve->reserved_at) ? date('Y/m/d H:i', (int) $reserve->reserved_at) : '' }}
                                    @if (!empty($reserve->meetingTime->time))
                                        • {{ $reserve->meetingTime->time }}
                                    @endif
                                </p>
                                <p class="font-medium text-13px mt-1">
                                    <span class="rounded-full px-2.5 py-1 font-bold text-12px {{ $reserve->status==='open' ? 'bg-[#E8F5E9] text-[#00B31B]' : ($reserve->status==='finished' ? 'bg-fa text-gray' : ($reserve->status==='pending' ? 'bg-[#FEF6E7] text-[#D97706]' : 'bg-[#FEF2F2] text-[#EF4444]')) }}">
                                        {{ $reserve->status==='open' ? 'مفتوحة' : ($reserve->status==='finished' ? 'منتهية' : ($reserve->status==='pending' ? 'بانتظار التأكيد' : $reserve->status)) }}
                                    </span>
                                    @if (!empty($reserve->paid_amount))
                                        <span class="ms-2 font-bold text-13px text-primary">{{ handlePrice($reserve->paid_amount) }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if ($reserve->status === 'open' && (!empty($reserve->link) || !empty($reserve->session)))
                                    <a href="{{ route('panel.v1.student.meetings.join', ['id' => $reserve->id]) }}" class="btn btn-primary rounded-10px h-10 px-5 font-bold text-13px">انضمام</a>
                                @endif
                                @if ($reserve->status === 'open')
                                    <form method="POST" action="{{ route('panel.v1.student.meetings.finish', ['id' => $reserve->id]) }}" onsubmit="return confirm('إنهاء الجلسة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost rounded-10px h-10 px-4 font-bold text-13px text-gray">إنهاء</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @if (!empty($reserve->meeting->creator->email))
                            <p class="font-medium text-13px text-gray mt-3">المدرب: {{ $reserve->meeting->creator->email }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
