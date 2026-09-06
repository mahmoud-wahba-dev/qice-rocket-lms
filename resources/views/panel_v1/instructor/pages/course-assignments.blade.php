@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $reviewId = $demoAssignmentId ?? 1; @endphp

<div class="space-y-6 pb-8">
    @include('panel_v1.instructor.components.page-header', [
        'title' => $pageTitleMain ?? '',
        'subtitle' => $pageSubtitle ?? '',
    ])

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($summaryCards ?? [] as $card)
            <div class="rounded-14px bg-white border border-d9 px-4 py-5"
                style="border-inline-end: 4px solid {{ $card['edge'] }}">
                <p class="font-medium text-14px text-gray mb-2">{{ $card['label'] }}</p>
                <p class="font-semibold text-30px text-primary leading-none">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-d9 p-5 sm:p-7 rounded-10px">
        <h2 class="font-semibold text-20px text-black mb-4">تسليمات التكليف وإدارة الطلاب</h2>

        <div class="flex flex-col lg:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                <input type="search" placeholder="البحث عن طريق الطلاب أو الدرجة أو غير ذلك..."
                    class="input input-bordered w-full h-11 rounded-10px border-d9 ps-10 font-medium text-14px">
            </div>
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-11 font-semibold text-14px text-primary bg-white hover:bg-fa transition">
                <span class="icon-[tabler--calendar] size-4"></span>
                April 11 - April 24
            </button>
            <button type="button"
                class="inline-flex items-center justify-center rounded-10px border border-d9 px-4 h-11 font-semibold text-14px text-primary bg-white hover:bg-fa transition">تصدير</button>
        </div>

        <div class="border border-d9 rounded-14px bg-white overflow-x-auto">
            <table class="table w-full text-14px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="px-4 py-3 text-start font-semibold">الطالب</th>
                        <th class="px-4 py-3 text-start font-semibold">تاريخ التسليم</th>
                        <th class="px-4 py-3 text-start font-semibold">آخر تحديث</th>
                        <th class="px-4 py-3 text-start font-semibold">الدرجة</th>
                        <th class="px-4 py-3 text-start font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-start font-semibold">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions ?? [] as $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="size-10 rounded-full bg-primary center text-white font-bold">
                                        {{ mb_substr($row['name'], 0, 1) }}
                                    </span>
                                    <span class="font-semibold text-black">{{ $row['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">{{ $row['submitted_at'] }}</td>
                            <td class="px-4 py-4">{{ $row['updated_at'] }}</td>
                            <td class="px-4 py-4 font-bold text-[#00B31B]">{{ $row['grade'] }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-[#FEF2F2] px-3 py-1 font-semibold text-12px text-[#DC2626]">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                                    class="font-bold text-14px text-[#3B82F6] hover:opacity-80">عرض التكليف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
