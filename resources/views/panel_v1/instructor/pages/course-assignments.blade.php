@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $reviewId = $demoAssignmentId ?? 1; @endphp

<div class="space-y-6 pb-8">
    @include('panel_v1.instructor.components.page-header', [
        'title' => $pageTitleMain ?? 'متطلبات دوراتي / السلام',
        'subtitle' => $pageSubtitle ?? '',
    ])

    {{-- Summary cards with colored end border --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($summaryCards ?? [] as $card)
            <div class="rounded-14px bg-white border border-d9 px-4 sm:px-5 py-5"
                style="border-inline-start: 4px solid {{ $card['edge'] }}">
                <p class="font-medium text-14px sm:text-15px text-gray mb-3">{{ $card['label'] }}</p>
                <p class="font-semibold text-28px sm:text-30px leading-none {{ $card['valueClass'] ?? 'text-primary' }}">
                    {{ $card['value'] }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-d9 p-5 sm:p-7 rounded-14px">
        <h2 class="font-semibold text-24px text-primary mb-5">تسليمات التكليف وإدارة الطلاب</h2>

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
                <span class="icon-[tabler--calendar] size-4"></span>
                April 11 - April 24
            </button>
        </div>

        <div class="border border-d9 rounded-14px overflow-x-auto">
            <table class="table w-full text-15px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="px-4 py-3.5 text-start font-semibold">المتدرب</th>
                        <th class="px-4 py-3.5 text-start font-semibold">تاريخ الانضمام</th>
                        <th class="px-4 py-3.5 text-start font-semibold">أحدث تسليم</th>
                        <th class="px-4 py-3.5 text-start font-semibold">التسليم الأخير</th>
                        <th class="px-4 py-3.5 text-start font-semibold">المحاولات</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الدرجة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الحالة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions ?? [] as $index => $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="size-11 rounded-full bg-primary/10 center shrink-0">
                                        <span class="font-bold text-16px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                                    </span>
                                    <span class="font-semibold text-16px text-primary">{{ $row['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['joined_at'] }}</td>
                            <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['latest_at'] }}</td>
                            <td class="px-4 py-4 font-medium text-gray">{{ $row['last_at'] }}</td>
                            <td class="px-4 py-4 font-semibold">{{ $row['attempts'] }}</td>
                            <td class="px-4 py-4 font-semibold text-[#00B31B]">{{ $row['grade'] }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-[#FEF2F2] px-3 py-1 font-semibold text-13px text-[#DC2626]">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col items-start gap-2">
                                    <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                        <button type="button"
                                            class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                            aria-label="الاجراء" id="course-assign-menu-{{ $index }}">
                                            <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                            role="menu" aria-labelledby="course-assign-menu-{{ $index }}">
                                            <li>
                                                <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                                                    class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">عرض التكليف</a>
                                            </li>
                                            <li>
                                                <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">ارسال تذكير</a>
                                            </li>
                                        </ul>
                                    </div>
                                  
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
