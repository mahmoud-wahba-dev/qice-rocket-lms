@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $slug = $slug ?? 'demo'; @endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => $courseTitle ?? '',
        'subtitle' => $courseSubtitle ?? '',
    ])
        @slot('actions')
            <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-14px text-white shrink-0 hover:opacity-95 transition">
                تصدير تفاصيل الدورة
            </button>
        @endslot
    @endcomponent

    <div class="rounded-14px bg-f9 border border-d9 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="font-medium text-14px text-primary leading-relaxed">{{ $alertText ?? '' }}</p>
        <a href="{{ route('panel.v1.instructor.assignments') }}"
            class="inline-flex items-center justify-center rounded-10px bg-primary px-4 h-10 font-semibold text-13px text-white shrink-0 hover:opacity-95 transition">تصفح المهام الآن</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($perfStats ?? [] as $card)
            @php
                $bg = match ($card['tone'] ?? '') {
                    'red' => 'bg-[#FEF2F2]',
                    'yellow' => 'bg-[#FFFBEB]',
                    default => 'bg-[#ECFDF5]',
                };
            @endphp
            <div class="rounded-14px {{ $bg }} px-5 py-6 text-center border border-d9">
                <p class="font-semibold text-30px text-primary leading-none mb-2">{{ $card['value'] }}</p>
                <p class="font-semibold text-14px text-gray">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white border border-d9 p-5 sm:p-7 rounded-10px">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h2 class="font-semibold text-20px text-black">قائمة طلاب الدورة</h2>
            <button type="button"
                class="inline-flex items-center justify-center rounded-10px bg-primary px-4 h-10 font-semibold text-13px text-white hover:opacity-95 transition">تعديل بيانات القائمة</button>
        </div>

        <div class="flex flex-col lg:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                <input type="search" placeholder="ابحث عن طالب..."
                    class="input input-bordered w-full h-11 rounded-10px border-d9 ps-10 font-medium text-14px">
            </div>
            <button type="button"
                class="inline-flex items-center justify-center rounded-10px border border-d9 px-4 h-11 font-semibold text-14px text-primary bg-white hover:bg-fa transition">فلتر</button>
            <button type="button"
                class="inline-flex items-center justify-center rounded-10px border border-d9 px-4 h-11 font-semibold text-14px text-primary bg-white hover:bg-fa transition">تحميل في ملف</button>
        </div>

        <div class="border border-d9 rounded-14px bg-white overflow-x-auto">
            <table class="table w-full text-14px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="font-semibold px-4 py-3 text-start">الطالب</th>
                        <th class="font-semibold px-4 py-3 text-start">التقدم</th>
                        <th class="font-semibold px-4 py-3 text-start">نشاط التعلم</th>
                        <th class="font-semibold px-4 py-3 text-start">المحاضرات</th>
                        <th class="font-semibold px-4 py-3 text-start">التكليفات</th>
                        <th class="font-semibold px-4 py-3 text-start">الشهادات</th>
                        <th class="font-semibold px-4 py-3 text-start">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students ?? [] as $student)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="size-10 rounded-full bg-primary center text-white font-bold">
                                        {{ mb_substr($student['name'], 0, 1) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-black">{{ $student['name'] }}</p>
                                        <p class="font-medium text-12px text-gray">{{ $student['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 min-w-36">
                                <p class="font-semibold text-primary mb-1">{{ $student['progress'] }}%</p>
                                <div class="h-1.5 rounded-full bg-[#EFEFEF] overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $student['progress'] }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-medium">{{ $student['activity'] }}</td>
                            <td class="px-4 py-4 font-medium">{{ $student['lectures'] }}</td>
                            <td class="px-4 py-4 font-medium">{{ $student['assignments'] }}</td>
                            <td class="px-4 py-4 font-medium">{{ $student['certificates'] }}</td>
                            <td class="px-4 py-4">
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button" class="dropdown-toggle btn btn-square btn-text">
                                        <span class="icon-[tabler--dots] size-5 text-primary"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl">
                                        <li><a href="#" class="dropdown-item px-4 py-2 font-medium text-14px text-primary">تعديل البيانات</a></li>
                                        <li><a href="#" class="dropdown-item px-4 py-2 font-medium text-14px text-primary">تغيير الحالة</a></li>
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
