@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $slug = $slug ?? ($demoSlug ?? 'demo');
    $reviewId = $demoAssignmentId ?? 1;
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => $courseTitle ?? 'لوحة اداء الدورة',
        'subtitle' => $courseSubtitle ?? '',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.instructor.courses') }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                عرض تفاصيل الدورة
            </a>
        @endslot
    @endcomponent

    {{-- Operational alert --}}
    <div class="rounded-14px bg-[#F1F5F9] px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="font-medium text-16px text-primary leading-relaxed">{{ $alertText ?? '' }}</p>
        <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
            class="inline-flex items-center justify-center rounded-10px bg-primary px-5 h-11 font-semibold text-14px text-white shrink-0 hover:opacity-95 transition">
            تصحيح الواجب الان
        </a>
    </div>

    {{-- Perf stat cards — RTL order: green, yellow, red from right --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($perfStats ?? [] as $card)
            @php
                $tone = match ($card['tone'] ?? '') {
                    'red' => ['bg' => 'bg-[#FEF2F2]', 'border' => 'border-[#FECACA]', 'icon' => 'text-[#DC2626]'],
                    'yellow' => ['bg' => 'bg-[#FFFBEB]', 'border' => 'border-[#FDE68A]', 'icon' => 'text-[#D97706]'],
                    default => ['bg' => 'bg-[#ECFDF5]', 'border' => 'border-[#A7F3D0]', 'icon' => 'text-[#059669]'],
                };
            @endphp
            <div class="rounded-14px {{ $tone['bg'] }} border {{ $tone['border'] }} px-5 py-6 text-center flex flex-col items-center justify-center min-h-[130px]">
                <span class="icon-[tabler--school] size-7 mb-3 {{ $tone['icon'] }}"></span>
                <p class="font-semibold text-28px sm:text-30px text-primary leading-none mb-2">{{ $card['value'] }}</p>
                <p class="font-semibold text-14px sm:text-16px text-gray leading-snug">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Students list --}}
    <div class="bg-white border border-d9 p-5 sm:p-7 rounded-14px">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <h2 class="font-semibold text-24px text-primary">قائمة طلاب الدورة</h2>
            <button type="button"
                class="inline-flex items-center justify-center rounded-10px bg-primary px-5 h-11 font-semibold text-14px text-white hover:opacity-95 transition">
                تصدير قائمة الطلاب
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                <input type="search"
                    placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك..."
                    class="input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px">
            </div>
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                <span class="icon-[tabler--filter] size-4"></span>
                فلتر
            </button>
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                <span class="icon-[tabler--file-spreadsheet] size-4"></span>
                استخراج في الأكسل
            </button>
        </div>

        <div class="border border-d9 rounded-14px bg-white overflow-x-auto">
            <table class="table w-full text-15px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">المتدرب</th>
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">التقدم</th>
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">نشاط التعلم</th>
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">الاختبارات المجتازة</th>
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">التكليفات المجتازة</th>
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">الشهادات</th>
                        <th class="font-semibold px-4 py-3.5 text-start text-15px">الإجراء والتقييم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students ?? [] as $index => $student)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="size-11 rounded-full bg-primary/10 center overflow-hidden shrink-0">
                                        <span class="font-bold text-16px text-primary">{{ mb_substr($student['name'], 0, 1) }}</span>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-16px text-primary truncate">{{ $student['name'] }}</p>
                                        <p class="font-medium text-13px text-gray truncate">{{ $student['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 min-w-40">
                                <p class="font-semibold text-15px text-primary mb-1.5">{{ $student['progress'] }}%</p>
                                <div class="h-2 rounded-full bg-[#EFEFEF] overflow-hidden max-w-[140px]">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $student['progress'] }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-medium text-15px text-black whitespace-nowrap">{{ $student['activity'] }}</td>
                            <td class="px-4 py-4 font-semibold text-15px text-black">{{ $student['exams'] }}</td>
                            <td class="px-4 py-4 font-semibold text-15px text-black">{{ $student['assignments'] }}</td>
                            <td class="px-4 py-4 font-semibold text-15px text-black">{{ $student['certificates'] }}</td>
                            <td class="px-4 py-4">
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button"
                                        class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                        aria-label="الإجراء والتقييم" id="perf-student-menu-{{ $index }}">
                                        <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                        role="menu" aria-labelledby="perf-student-menu-{{ $index }}">
                                        <li>
                                            <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">تصحيح التكليف</a>
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">ارسال تذكير</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
