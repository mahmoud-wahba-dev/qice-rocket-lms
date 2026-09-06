@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $slug = $slug ?? 'demo'; @endphp

<div class="space-y-8 pb-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="font-extrabold text-28px sm:text-32px text-primary mb-2">{{ $courseTitle ?? '' }}</h1>
            <p class="font-medium text-15px text-primary/70">{{ $courseSubtitle ?? '' }}</p>
        </div>
        <button type="button"
            class="inline-flex items-center justify-center gap-2 rounded-12px bg-[#C99C69] px-5 h-11 font-bold text-14px text-white shrink-0">
            تصدير تفاصيل الدورة
        </button>
    </div>

    <div class="rounded-16px bg-[#F1F5F9] px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="font-medium text-14px text-primary leading-relaxed">{{ $alertText ?? '' }}</p>
        <a href="{{ route('panel.v1.instructor.assignments') }}"
            class="btn btn-primary rounded-10px h-10 px-4 font-bold text-13px shrink-0">تصفح المهام الآن</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($perfStats ?? [] as $card)
            @php
                $bg = match ($card['tone'] ?? '') {
                    'red' => 'bg-[#FEF2F2]',
                    'yellow' => 'bg-[#FFFBEB]',
                    default => 'bg-[#ECFDF5]',
                };
            @endphp
            <div class="rounded-16px {{ $bg }} px-5 py-6 text-center border border-black/5">
                <p class="font-extrabold text-22px text-primary mb-1">{{ $card['value'] }}</p>
                <p class="font-medium text-14px text-gray">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h2 class="font-bold text-22px text-primary">قائمة طلاب الدورة</h2>
            <button type="button" class="btn btn-primary rounded-10px h-10 px-4 font-bold text-13px">تعديل بيانات القائمة</button>
        </div>

        <div class="flex flex-col lg:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                <input type="search" placeholder="ابحث عن طالب..."
                    class="input input-bordered w-full h-11 rounded-10px border-d9 ps-10 font-medium text-14px">
            </div>
            <button type="button" class="btn btn-outline rounded-10px h-11 px-4 border-d9 font-semibold text-14px text-primary">فلتر</button>
            <button type="button" class="btn btn-outline rounded-10px h-11 px-4 border-d9 font-semibold text-14px text-primary">تحميل في ملف</button>
        </div>

        <div class="border border-d9 rounded-16px bg-white overflow-x-auto">
            <table class="table w-full text-14px">
                <thead>
                    <tr class="border-b border-d9 text-gray">
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
                                        <p class="font-bold text-primary">{{ $student['name'] }}</p>
                                        <p class="font-medium text-12px text-gray">{{ $student['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 min-w-36">
                                <p class="font-semibold text-primary mb-1">{{ $student['progress'] }}%</p>
                                <div class="h-1.5 rounded-full bg-d9 overflow-hidden">
                                    <div class="h-full bg-primary" style="width: {{ $student['progress'] }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-medium">{{ $student['activity'] }}</td>
                            <td class="px-4 py-4 font-medium">{{ $student['lectures'] }}</td>
                            <td class="px-4 py-4 font-medium">{{ $student['assignments'] }}</td>
                            <td class="px-4 py-4 font-medium">{{ $student['certificates'] }}</td>
                            <td class="px-4 py-4">
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button" class="dropdown-toggle btn btn-square btn-text">
                                        <span class="icon-[tabler--dots-vertical] size-5"></span>
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
