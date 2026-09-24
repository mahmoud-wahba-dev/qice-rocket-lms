@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $session = $session ?? [];
    $settingsUrl = $settingsUrl ?? url('/panel/meetings/settings');
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'الجلسات الاستشارية',
        'subtitle' => 'إدارة وجدولة الورش والجلسات التفاعلية المباشرة مع الطلاب',
    ])
        @slot('actions')
            <a href="{{ $settingsUrl }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                جدولة جلسة جديدة
            </a>
        @endslot
    @endcomponent

    @if (!empty($session))
        <article class="w-full max-w-2xl rounded-14px bg-white border border-d9 border-s-4 border-s-primary p-5 sm:p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <span class="inline-flex rounded-full bg-[#DBEAFE] px-3 py-1 font-semibold text-13px text-[#1D4ED8]">
                    {{ $session['status'] ?? '' }}
                </span>
                <p class="font-semibold text-20px text-primary">{{ $session['price'] ?? '' }}</p>
            </div>

            <h2 class="font-semibold text-20px sm:text-22px text-primary leading-snug mb-3">
                {{ $session['title'] ?? '' }}
            </h2>

            <div class="flex items-center gap-2.5 mb-4">
                <span class="size-9 rounded-full bg-[#E8ECEA] center shrink-0">
                    <span class="font-bold text-12px text-primary">{{ $session['instructorInitials'] ?? 'س' }}</span>
                </span>
                <p class="font-medium text-15px text-gray">{{ $session['instructor'] ?? '' }}</p>
            </div>

            <div class="border-t border-d9 pt-4 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-15px text-primary mb-1">{{ $session['date'] ?? '' }}</p>
                        <p class="font-medium text-14px text-gray">{{ $session['time'] ?? '' }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-15px text-[#0FC787] shrink-0">
                        <span class="icon-[tabler--video] size-4"></span>
                        {{ $session['linkLabel'] ?? 'لقاء أونلاين' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ $session['detail_url'] ?? '#' }}"
                    class="inline-flex items-center justify-center rounded-10px border border-d9 h-11 font-semibold text-14px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                    التفاصيل
                </a>
                @if (!empty($session['join_url']))
                    <a href="{{ $session['join_url'] }}"
                        class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 h-11 font-semibold text-14px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                        <span class="icon-[tabler--video] size-4"></span>
                        {{ $session['join_label'] ?? 'انضمام للجلسة المباشرة' }}
                    </a>
                @elseif (!empty($session['session_url']))
                    <button type="button"
                        data-consultation-session-open
                        data-action="{{ $session['session_url'] }}"
                        data-student="{{ $session['instructor'] ?? '' }}"
                        data-link="{{ $session['link_raw'] ?? '' }}"
                        class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 h-11 font-semibold text-14px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                        <span class="icon-[tabler--video] size-4"></span>
                        إعداد رابط اللقاء
                    </button>
                @else
                    <span class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 h-11 font-semibold text-14px text-gray bg-[#F8FAFC] opacity-60 cursor-not-allowed">
                        <span class="icon-[tabler--link] size-4"></span>
                        لا يوجد رابط
                    </span>
                @endif
            </div>
        </article>
    @endif

    <div class="bg-white border border-d9 rounded-14px overflow-x-auto">
        <table class="table w-full text-15px">
            <thead>
                <tr class="border-b border-d9 text-gray bg-f9">
                    <th class="px-4 py-3.5 text-start font-semibold">طالب</th>
                    <th class="px-4 py-3.5 text-start font-semibold">نوع الاجتماع</th>
                    <th class="px-4 py-3.5 text-start font-semibold">يوم</th>
                    <th class="px-4 py-3.5 text-start font-semibold">تاريخ</th>
                    <th class="px-4 py-3.5 text-start font-semibold">توقيت</th>
                    <th class="px-4 py-3.5 text-start font-semibold">المبلغ المدفوع</th>
                    <th class="px-4 py-3.5 text-start font-semibold">عدد الطلاب</th>
                    <th class="px-4 py-3.5 text-start font-semibold">حالة</th>
                    <th class="px-4 py-3.5 text-start font-semibold">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendees ?? [] as $index => $row)
                    @php
                        $statusKey = $row['status_key'] ?? '';
                        $statusClass = match ($statusKey) {
                            'finished' => 'bg-[#ECFDF5] text-[#059669]',
                            'pending' => 'bg-[#FEF6E7] text-[#D97706]',
                            'canceled' => 'bg-[#FEF2F2] text-[#DC2626]',
                            default => 'bg-[#DBEAFE] text-[#1D4ED8]',
                        };
                    @endphp
                    <tr class="border-b border-d9 last:border-0">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if (!empty($row['avatar']))
                                    <img src="{{ $row['avatar'] }}" alt=""
                                        class="size-11 rounded-full object-cover shrink-0 bg-[#E9D5FF]">
                                @else
                                    <span class="size-11 rounded-full bg-[#E9D5FF] center font-bold text-13px text-[#7C3AED] shrink-0">
                                        {{ $row['initials'] }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-16px text-primary truncate">{{ $row['name'] }}</p>
                                    <p class="font-medium text-13px text-gray truncate">{{ $row['email'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['joinType'] }}</td>
                        <td class="px-4 py-4 font-medium">{{ $row['day'] }}</td>
                        <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['date'] }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-10px bg-[#F1F5F9] px-2.5 py-1 font-semibold text-13px text-primary whitespace-nowrap">
                                {{ $row['time'] }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-semibold">{{ $row['amount'] }}</td>
                        <td class="px-4 py-4 font-semibold">{{ $row['students'] ?? 1 }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 font-semibold text-13px whitespace-nowrap {{ $statusClass }}">
                                {{ $row['status'] ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                <button type="button"
                                    class="dropdown-toggle size-9 rounded-10px border border-d9 bg-white center hover:bg-[#FAFAF4] transition"
                                    aria-label="إجراءات" id="consult-row-menu-{{ $row['id'] ?? $index }}">
                                    <span class="icon-[tabler--dots] size-4 text-gray"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-[13rem] py-1.5 rounded-12px border border-d9 bg-white shadow-xl z-40 text-start"
                                    role="menu" aria-labelledby="consult-row-menu-{{ $row['id'] ?? $index }}">
                                    <li>
                                        <a href="{{ $row['detail_url'] }}"
                                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">
                                            التفاصيل
                                        </a>
                                    </li>
                                    @if (!empty($row['join_url']))
                                        <li>
                                            <a href="{{ $row['join_url'] }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">
                                                {{ $row['join_label'] ?? 'انضمام للجلسة المباشرة' }}
                                            </a>
                                        </li>
                                    @endif
                                    @if (!empty($row['session_url']) && empty($row['agora_enabled']))
                                        <li>
                                            <button type="button"
                                                data-consultation-session-open
                                                data-action="{{ $row['session_url'] }}"
                                                data-student="{{ $row['name'] }}"
                                                data-link="{{ $row['link_raw'] ?? '' }}"
                                                class="dropdown-item w-full text-start px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">
                                                إعداد رابط خارجي
                                            </button>
                                        </li>
                                    @endif
                                    @if (!empty($row['finish_url']))
                                        <li>
                                            <form method="POST" action="{{ $row['finish_url'] }}"
                                                onsubmit="return confirm('إنهاء هذه الجلسة؟');">
                                                @csrf
                                                <button type="submit"
                                                    class="dropdown-item w-full text-start px-4 py-2.5 font-semibold text-14px text-[#16A34A] hover:bg-[#F0FDF4]">
                                                    إنهاء الجلسة
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-16 text-center font-medium text-16px text-gray">
                            لا توجد حجوزات جلسات حالياً
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('panel_v1.instructor.components.consultation-session-modal', [
        'agoraEnabled' => $agoraEnabled ?? false,
    ])
</div>
@endsection
