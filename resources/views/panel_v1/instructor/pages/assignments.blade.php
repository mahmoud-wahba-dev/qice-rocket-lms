@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $reviewId = $demoAssignmentId ?? 1; @endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الواجبات والتكليفات',
        'subtitle' => 'متابعة التسليمات والتصحيح وإدارة تكليفات الدورات.',
    ])
        @slot('actions')
            <a href="#"
                class="inline-flex items-center gap-2 rounded-12px border border-color2 px-5 h-12 font-semibold text-14px text-color2 hover:opacity-90 transition">
                عرض جميع التكاليف
            </a>
            <a href="#"
                class="inline-flex items-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-14px text-white hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                إضافة تكليف جديد
            </a>
        @endslot
    @endcomponent

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($assignmentStats ?? [] as $stat)
            <div class="rounded-14px bg-primary text-white px-5 py-6 text-center flex flex-col items-center justify-center min-h-[120px]">
                <span class="icon-[tabler--school] size-7 mx-auto mb-3 text-color2"></span>
                <p class="font-semibold text-30px leading-none mb-2">{{ $stat['value'] }}</p>
                <p class="font-semibold text-14px text-white leading-snug">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div>
        <h2 class="font-semibold text-20px text-black mb-4">التكليفات الحالية</h2>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
            @foreach ($currentAssignments ?? [] as $item)
                <article class="border border-d9 rounded-14px bg-f9 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-semibold text-16px text-black leading-snug mb-1">{{ $item['title'] }}</h3>
                            <p class="font-medium text-14px text-gray">{{ $item['date'] }}</p>
                        </div>
                        <button type="button" class="btn btn-square btn-text" aria-label="خيارات">
                            <span class="icon-[tabler--dots] size-5 text-primary"></span>
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="rounded-10px bg-white border border-d9 px-2 py-3 text-center">
                            <p class="font-bold text-14px text-primary">{{ $item['submissions'] }}</p>
                        </div>
                        <div class="rounded-10px bg-white border border-d9 px-2 py-3 text-center">
                            <p class="font-bold text-13px text-primary">{{ $item['corrected'] }}</p>
                        </div>
                        <div class="rounded-10px bg-white border border-d9 px-2 py-3 text-center">
                            <p class="font-bold text-13px text-primary">{{ $item['pending'] }}</p>
                        </div>
                    </div>
                    <p class="font-semibold text-12px text-black mb-1.5">نسبة تصحيح الواجب {{ $item['progress'] }}%</p>
                    <div class="h-1.5 rounded-full bg-[#EFEFEF] overflow-hidden mb-4">
                        <div class="h-full bg-primary rounded-full" style="width: {{ $item['progress'] }}%"></div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                            class="inline-flex items-center justify-center rounded-10px bg-primary px-4 h-10 font-semibold text-13px text-white hover:opacity-95 transition">
                            تصحيح التكليفات
                        </a>
                        <button type="button"
                            class="inline-flex items-center justify-center rounded-10px border border-d9 px-4 h-10 font-semibold text-13px text-primary bg-white hover:bg-fa transition">
                            {{ $item['studentsCount'] }} طلاب
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-d9 p-5 sm:p-7 rounded-10px">
        <nav class="tabs tabs-bordered flex w-full max-w-xl mb-5 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-14px sm:text-16px text-gray pb-4 active-tab:text-primary active-tab:border-b-color2"
                data-tab="#a-panel-1" role="tab" aria-selected="true">نتائج تكليفات الطلاب</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-14px sm:text-16px text-gray pb-4 active-tab:text-primary active-tab:border-b-color2"
                data-tab="#a-panel-2" role="tab" aria-selected="false">جميع التكليفات</button>
        </nav>

        <div class="flex flex-col lg:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                <input type="search" placeholder="البحث عن طريق الطلاب أو الدرجة أو غير ذلك..."
                    class="input input-bordered w-full h-11 rounded-10px border-d9 ps-10 font-medium text-14px">
            </div>
            <button type="button" class="btn btn-outline rounded-10px h-11 px-4 border-d9 font-semibold text-14px text-primary">فلتر</button>
            <button type="button" class="btn btn-outline rounded-10px h-11 px-4 border-d9 font-semibold text-14px text-primary inline-flex items-center gap-2">
                <span class="icon-[tabler--calendar] size-4"></span>
                April 11 - April 24
            </button>
        </div>

        <div id="a-panel-1" role="tabpanel" class="border border-d9 rounded-14px bg-white overflow-x-auto">
            <table class="table w-full text-13px sm:text-14px">
                <thead>
                    <tr class="border-b border-d9 text-gray">
                        <th class="px-4 py-3 text-start font-semibold">المدرب</th>
                        <th class="px-4 py-3 text-start font-semibold">العنوان والدورة</th>
                        <th class="px-4 py-3 text-start font-semibold">أول تسليم</th>
                        <th class="px-4 py-3 text-start font-semibold">آخر تسليم</th>
                        <th class="px-4 py-3 text-start font-semibold">المحاولات</th>
                        <th class="px-4 py-3 text-start font-semibold">الدرجة</th>
                        <th class="px-4 py-3 text-start font-semibold">تاريخ الانتهاء</th>
                        <th class="px-4 py-3 text-start font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-start font-semibold">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resultsRows ?? [] as $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="size-9 rounded-full bg-primary center text-white font-bold text-12px">ع</span>
                                    <span class="font-semibold text-primary">{{ $row['instructor'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-bold text-primary">{{ $row['title'] }}</p>
                                <p class="font-medium text-12px text-gray">{{ $row['course'] }}</p>
                            </td>
                            <td class="px-4 py-4">{{ $row['first'] }}</td>
                            <td class="px-4 py-4">{{ $row['last'] }}</td>
                            <td class="px-4 py-4">{{ $row['attempts'] }}</td>
                            <td class="px-4 py-4 font-bold text-[#00B31B]">{{ $row['grade'] }}</td>
                            <td class="px-4 py-4">{{ $row['end'] }}</td>
                            <td class="px-4 py-4">
                                @if (($row['statusTone'] ?? '') === 'pending')
                                    <span class="inline-flex rounded-full bg-[#FEF2F2] px-3 py-1 font-semibold text-12px text-[#DC2626]">{{ $row['status'] }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-12px text-primary">{{ $row['status'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                                    class="font-bold text-14px text-primary hover:opacity-80">عرض التكليف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="a-panel-2" class="hidden" role="tabpanel">
            @include('panel_v1.instructor.components.empty-state', ['title' => 'ستظهر جميع التكليفات هنا'])
        </div>
    </div>
</div>
@endsection
