@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $session = $session ?? []; @endphp

<div class="space-y-8 pb-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-extrabold text-28px sm:text-32px text-primary mb-2">الجلسات الاستشارية</h1>
            <p class="font-medium text-15px text-gray">إدارة وجدولة الورش والجلسات التفاعلية المباشرة مع الطلاب</p>
        </div>
        <a href="#"
            class="inline-flex items-center justify-center gap-2 rounded-12px bg-[#C99C69] px-5 h-12 font-bold text-15px text-white shrink-0">
            <span class="icon-[tabler--plus] size-5"></span>
            جدولة جلسة جديدة
        </a>
    </div>

    <article class="border border-d9 rounded-16px bg-white p-5 sm:p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <span class="inline-flex rounded-full bg-[#DBEAFE] px-3 py-1 font-semibold text-12px text-[#1D4ED8]">
                {{ $session['status'] ?? '' }}
            </span>
            <p class="font-bold text-18px text-primary">{{ $session['price'] ?? '' }}</p>
        </div>
        <h2 class="font-bold text-20px text-primary mb-3">{{ $session['title'] ?? '' }}</h2>
        <div class="flex items-center gap-2 mb-5">
            <span class="size-8 rounded-full bg-primary/10 center">
                <span class="icon-[tabler--user] size-4 text-primary"></span>
            </span>
            <p class="font-medium text-14px text-gray">{{ $session['instructor'] ?? '' }}</p>
        </div>
        <div class="flex flex-wrap gap-4 font-medium text-14px text-gray mb-5">
            <span class="inline-flex items-center gap-1"><span class="icon-[tabler--calendar] size-4"></span>{{ $session['date'] ?? '' }}</span>
            <span class="inline-flex items-center gap-1"><span class="icon-[tabler--clock] size-4"></span>{{ $session['time'] ?? '' }}</span>
            <a href="#" class="inline-flex items-center gap-1 text-[#0FC787] font-semibold">
                <span class="icon-[tabler--link] size-4"></span>
                {{ $session['linkLabel'] ?? '' }}
            </a>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline rounded-10px h-10 px-4 border-d9 font-semibold text-13px text-primary">التفاصيل</button>
            <button type="button" class="btn btn-outline rounded-10px h-10 px-4 border-d9 font-semibold text-13px text-primary">إلغاء اللقاء</button>
        </div>
    </article>

    <div class="border border-d9 rounded-16px bg-white overflow-x-auto">
        <table class="table w-full text-13px sm:text-14px">
            <thead>
                <tr class="bg-fa border-b border-d9 text-gray">
                    <th class="px-4 py-3 text-start font-semibold">طالب</th>
                    <th class="px-4 py-3 text-start font-semibold">نوع الانضمام</th>
                    <th class="px-4 py-3 text-start font-semibold">يوم</th>
                    <th class="px-4 py-3 text-start font-semibold">تاريخ</th>
                    <th class="px-4 py-3 text-start font-semibold">توقيت</th>
                    <th class="px-4 py-3 text-start font-semibold">المبلغ المدفوع</th>
                    <th class="px-4 py-3 text-start font-semibold">عدد الملفات</th>
                    <th class="px-4 py-3 text-start font-semibold">حالة</th>
                    <th class="px-4 py-3 text-start font-semibold">اجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendees ?? [] as $row)
                    <tr class="border-b border-d9 last:border-0">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <span class="size-10 rounded-full bg-[#E9D5FF] center font-bold text-12px text-[#7C3AED]">
                                    {{ $row['initials'] }}
                                </span>
                                <div>
                                    <p class="font-bold text-primary">{{ $row['name'] }}</p>
                                    <p class="font-medium text-12px text-gray">{{ $row['email'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">{{ $row['joinType'] }}</td>
                        <td class="px-4 py-4">{{ $row['day'] }}</td>
                        <td class="px-4 py-4">{{ $row['date'] }}</td>
                        <td class="px-4 py-4">{{ $row['time'] }}</td>
                        <td class="px-4 py-4">{{ $row['amount'] }}</td>
                        <td class="px-4 py-4">{{ $row['files'] }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-12px text-[#059669]">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <button type="button" class="btn btn-square btn-text" aria-label="خيارات">
                                <span class="icon-[tabler--dots-vertical] size-5"></span>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
