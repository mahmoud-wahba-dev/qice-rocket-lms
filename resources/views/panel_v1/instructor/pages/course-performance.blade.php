@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $slug = $slug ?? ($courseSlug ?? 'demo');
    $filters = $filters ?? ['q' => '', 'progress' => ''];
@endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => $courseTitle ?? 'لوحة اداء الدورة',
        'subtitle' => $courseSubtitle ?? '',
    ])
        @slot('actions')
            <a href="{{ $courseDetailsUrl ?? route('panel.v1.instructor.courses.watch', ['slug' => $slug]) }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                عرض تفاصيل الدورة
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px bg-[#F1F5F9] px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="font-medium text-16px text-primary leading-relaxed">{{ $alertText ?? '' }}</p>
        @if (!empty($canGradeNow) && !empty($reviewId))
            <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                class="inline-flex items-center justify-center rounded-10px bg-primary px-5 h-11 font-semibold text-14px text-white shrink-0 hover:opacity-95 transition">
                تصحيح الواجب الان
            </a>
        @else
            <a href="{{ $courseAssignmentsUrl ?? route('panel.v1.instructor.courses.assignments', ['slug' => $slug]) }}"
                class="inline-flex items-center justify-center rounded-10px bg-primary px-5 h-11 font-semibold text-14px text-white shrink-0 hover:opacity-95 transition">
                عرض التكليفات
            </a>
        @endif
    </div>

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

    @if (!empty($extraStats))
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ($extraStats as $stat)
                <div class="rounded-12px border border-d9 bg-white px-4 py-3 flex items-center justify-between">
                    <span class="font-medium text-14px text-gray">{{ $stat['label'] }}</span>
                    <span class="font-bold text-16px text-primary">{{ $stat['value'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="bg-white border border-d9 p-5 sm:p-7 rounded-14px">
        <div class="mb-5">
            <h2 class="font-semibold text-24px text-primary">قائمة طلاب الدورة</h2>
        </div>

        <form method="GET" action="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
            class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="progress"
                class="select select-bordered h-12 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary min-w-[12rem] flex-1">
                <option value="">كل مستويات التقدم</option>
                <option value="low" @selected(($filters['progress'] ?? '') === 'low')>أقل من 40%</option>
                <option value="mid" @selected(($filters['progress'] ?? '') === 'mid')>من 40% إلى 79%</option>
                <option value="high" @selected(($filters['progress'] ?? '') === 'high')>80% فأعلى</option>
            </select>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                <span class="icon-[tabler--filter] size-4"></span>
                فلتر
            </button>
            <a href="{{ $exportUrl ?? '#' }}"
                class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 font-semibold text-15px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                <span class="icon-[tabler--file-spreadsheet] size-4"></span>
                استخراج Excel
            </a>
        </form>

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
                    @forelse ($students ?? [] as $index => $student)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="size-11 rounded-full bg-primary/10 center overflow-hidden shrink-0">
                                        @if (!empty($student['avatar']))
                                            <img src="{{ $student['avatar'] }}" alt="" class="size-full object-cover">
                                        @else
                                            <span class="font-bold text-16px text-primary">{{ mb_substr($student['name'], 0, 1) }}</span>
                                        @endif
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
                                    <div class="h-full bg-primary rounded-full" style="width: {{ (int) $student['progress'] }}%"></div>
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
                                            <a href="{{ $student['review_url'] }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">
                                                {{ !empty($student['has_pending']) ? 'تصحيح التكليف' : 'عرض التكليفات' }}
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ $student['remind_url'] }}">
                                                @csrf
                                                <button type="submit"
                                                    class="dropdown-item w-full text-start px-4 py-2.5 font-medium text-15px text-primary">
                                                    ارسال تذكير
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center font-medium text-15px text-gray">
                                لا يوجد طلاب مطابقون للفلتر الحالي
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
