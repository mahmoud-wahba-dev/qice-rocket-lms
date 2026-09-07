@extends('panel_v1.instructor.layouts.app')

@section('content')
@php $session = $session ?? []; @endphp

<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'الجلسات الاستشارية',
        'subtitle' => 'إدارة وجدولة الورش والجلسات التفاعلية المباشرة مع الطلاب',
    ])
        @slot('actions')
            <a href="#"
                class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white shrink-0 hover:opacity-95 transition">
                <span class="icon-[tabler--plus] size-5"></span>
                جدولة جلسة جديدة
            </a>
        @endslot
    @endcomponent

    {{-- Featured upcoming session --}}
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
            <button type="button"
                class="inline-flex items-center justify-center rounded-10px border border-d9 h-11 font-semibold text-14px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                التفاصيل
            </button>
            <a href="#"
                class="inline-flex items-center justify-center gap-2 rounded-10px border border-d9 h-11 font-semibold text-14px text-primary bg-[#F8FAFC] hover:bg-fa transition">
                <span class="icon-[tabler--link] size-4"></span>
                رابط اللقاء
            </a>
        </div>
    </article>

    {{-- Consultations table --}}
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
                @foreach ($attendees ?? [] as $index => $row)
                    <tr class="border-b border-d9 last:border-0">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <span class="size-11 rounded-full bg-[#E9D5FF] center font-bold text-13px text-[#7C3AED] shrink-0">
                                    {{ $row['initials'] }}
                                </span>
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
                        <td class="px-4 py-4 font-semibold">{{ $row['students'] }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full bg-[#ECFDF5] px-3 py-1 font-semibold text-13px text-[#059669] whitespace-nowrap">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                <button type="button"
                                    class="dropdown-toggle size-9 rounded-full bg-[#F1F5F9] !inline-flex !items-center !justify-center border-0 hover:bg-[#E8ECEA] transition"
                                    aria-label="إجراءات" id="consult-row-menu-{{ $index }}">
                                    <span class="icon-[tabler--dots-vertical] size-5 text-gray"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                    role="menu" aria-labelledby="consult-row-menu-{{ $index }}">
                                    <li>
                                        <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">التفاصيل</a>
                                    </li>
                                    <li>
                                        <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-primary">رابط اللقاء</a>
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
@endsection
