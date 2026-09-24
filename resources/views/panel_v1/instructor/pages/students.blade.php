@extends('panel_v1.instructor.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'قائمة الطلاب',
        'subtitle' => 'الطلاب المسجلون في دوراتك من المبيعات الفعلية في قاعدة البيانات.',
    ])
    @endcomponent

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($studentStats ?? [] as $stat)
            <div class="rounded-14px bg-primary h-[150px] px-4 flex flex-col items-center justify-center text-center gap-2.5 shadow-sm">
                <span class="icon-[tabler--school] size-6 text-color2 shrink-0"></span>
                <p class="font-bold text-22px sm:text-26px leading-none text-[#F5E6C8]">{{ $stat['value'] }}</p>
                <p class="font-medium text-13px sm:text-14px text-white leading-snug">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-14px border border-d9 bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="table w-full text-15px">
                <thead>
                    <tr class="border-b border-d9 bg-fa text-gray">
                        <th class="px-5 py-4 text-start font-semibold">الطالب</th>
                        <th class="px-5 py-4 text-start font-semibold">البريد</th>
                        <th class="px-5 py-4 text-start font-semibold">الدورات</th>
                        <th class="px-5 py-4 text-start font-semibold">عدد الدورات</th>
                        <th class="px-5 py-4 text-start font-semibold">آخر شراء</th>
                        <th class="px-5 py-4 text-start font-semibold">إجمالي المدفوع</th>
                        <th class="px-5 py-4 text-start font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students ?? [] as $student)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-5 py-4 font-semibold text-primary">{{ $student['name'] }}</td>
                            <td class="px-5 py-4 font-medium text-gray" dir="ltr">{{ $student['email'] }}</td>
                            <td class="px-5 py-4 font-medium text-primary max-w-xs">
                                <span class="line-clamp-2">{{ $student['courses_label'] ?: '—' }}</span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-primary">{{ $student['courses_count'] }}</td>
                            <td class="px-5 py-4 font-medium text-gray whitespace-nowrap">{{ $student['last_purchase'] ?? '—' }}</td>
                            <td class="px-5 py-4 font-semibold text-primary whitespace-nowrap">{{ $student['spent_label'] }}</td>
                            <td class="px-5 py-4">
                                @if (!empty($student['course_slug']))
                                    <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $student['course_slug']]) }}"
                                        class="font-semibold text-14px text-primary hover:opacity-80">عرض الدورة</a>
                                @else
                                    <span class="text-gray">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center font-medium text-16px text-gray">
                                لا يوجد طلاب مسجلون بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
