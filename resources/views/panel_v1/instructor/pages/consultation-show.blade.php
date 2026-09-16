@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $d = $detail ?? []; @endphp

<div class="space-y-6 pb-8 max-w-3xl">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('panel.v1.instructor.consultations') }}"
            class="inline-flex items-center gap-2 font-semibold text-15px text-primary hover:opacity-80 transition">
            <span class="icon-[tabler--chevron-right] size-5"></span>
            العودة للجلسات
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if (!empty($d['join_url']))
                <a href="{{ $d['join_url'] }}"
                    class="inline-flex items-center gap-2 h-10 px-4 rounded-12px bg-primary text-white font-semibold text-14px hover:opacity-90 transition">
                    <span class="icon-[tabler--video] size-4"></span>
                    {{ $d['join_label'] ?? 'انضمام للجلسة المباشرة' }}
                </a>
            @endif
            @if (!empty($d['session_url']) && empty($d['agora_enabled']))
                <button type="button"
                    data-consultation-session-open
                    data-action="{{ $d['session_url'] }}"
                    data-student="{{ $d['name'] ?? '' }}"
                    data-link="{{ $d['link_raw'] ?? '' }}"
                    class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-d9 font-semibold text-14px text-primary hover:bg-fa transition">
                    <span class="icon-[tabler--link] size-4"></span>
                    إعداد رابط خارجي
                </button>
            @endif
            @if (!empty($d['finish_url']))
                <form method="POST" action="{{ $d['finish_url'] }}" onsubmit="return confirm('إنهاء هذه الجلسة؟');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-10 px-4 rounded-12px border border-[#FECACA] text-[#E11D48] font-semibold text-14px hover:bg-[#FEF2F2] transition">
                        إنهاء الجلسة
                    </button>
                </form>
            @endif
        </div>
    </div>

    <section class="border border-d9 rounded-14px bg-white p-5 sm:p-7 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="font-bold text-22px text-primary mb-2">{{ $d['title'] ?? 'تفاصيل الجلسة' }}</h1>
                <p class="font-medium text-14px text-gray">{{ $d['start_label'] ?? '' }}</p>
            </div>
            @php
                $statusClass = match ($d['status_key'] ?? '') {
                    'finished' => 'bg-[#ECFDF5] text-[#059669]',
                    'pending' => 'bg-[#FEF6E7] text-[#D97706]',
                    'canceled' => 'bg-[#FEF2F2] text-[#DC2626]',
                    default => 'bg-[#DBEAFE] text-[#1D4ED8]',
                };
            @endphp
            <span class="inline-flex rounded-full px-3 py-1 font-semibold text-13px {{ $statusClass }}">
                {{ $d['status'] ?? '' }}
            </span>
        </div>

        <div class="flex items-center gap-3 border-t border-d9 pt-5">
            @if (!empty($d['avatar']))
                <img src="{{ $d['avatar'] }}" alt="" class="size-14 rounded-full object-cover">
            @else
                <span class="size-14 rounded-full bg-[#E9D5FF] center font-bold text-16px text-[#7C3AED]">
                    {{ $d['initials'] ?? '?' }}
                </span>
            @endif
            <div>
                <p class="font-bold text-17px text-primary">{{ $d['name'] ?? '' }}</p>
                <p class="font-medium text-14px text-gray">{{ $d['email'] ?? '' }}</p>
                @if (!empty($d['phone']))
                    <p class="font-medium text-13px text-gray mt-0.5">{{ $d['phone'] }}</p>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-d9 pt-5">
            <div>
                <dt class="font-medium text-13px text-gray mb-1">نوع الاجتماع</dt>
                <dd class="font-semibold text-15px text-primary">{{ $d['joinType'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-13px text-gray mb-1">اليوم</dt>
                <dd class="font-semibold text-15px text-primary">{{ $d['day'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-13px text-gray mb-1">التاريخ</dt>
                <dd class="font-semibold text-15px text-primary">{{ $d['date'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-13px text-gray mb-1">التوقيت</dt>
                <dd class="font-semibold text-15px text-primary">{{ $d['time'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-13px text-gray mb-1">المبلغ المدفوع</dt>
                <dd class="font-semibold text-15px text-primary">{{ $d['amount'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-13px text-gray mb-1">عدد الطلاب</dt>
                <dd class="font-semibold text-15px text-primary">{{ $d['students'] ?? 1 }}</dd>
            </div>
            @if (!empty($d['password']))
                <div>
                    <dt class="font-medium text-13px text-gray mb-1">كلمة مرور اللقاء</dt>
                    <dd class="font-semibold text-15px text-primary">{{ $d['password'] }}</dd>
                </div>
            @endif
        </dl>

        @if (!empty($d['description']))
            <div class="border-t border-d9 pt-5">
                <h2 class="font-bold text-16px text-primary mb-2">ملاحظات</h2>
                <p class="font-medium text-14px text-primary/80 leading-relaxed whitespace-pre-line">{{ $d['description'] }}</p>
            </div>
        @endif

        @if (!empty($d['calendar_url']))
            <div class="border-t border-d9 pt-5">
                <a href="{{ $d['calendar_url'] }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 font-semibold text-14px text-primary hover:opacity-80 transition">
                    <span class="icon-[tabler--calendar] size-4"></span>
                    إضافة للتقويم
                </a>
            </div>
        @endif
    </section>

    @include('panel_v1.instructor.components.consultation-session-modal', [
        'agoraEnabled' => $d['agora_enabled'] ?? !empty(getFeaturesSettings('agora_for_meeting')),
    ])
</div>
@endsection
