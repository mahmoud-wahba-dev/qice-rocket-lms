@extends('panel_v1.instructor.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'جميع الشهادات الصادرة',
        'subtitle' => !empty($filterLabel)
            ? ('تصفية: ' . $filterLabel)
            : 'شهادات الطلاب الصادرة من دوراتك واختباراتك',
    ])
        @slot('actions')
            <a href="{{ $backUrl ?? route('panel.v1.instructor.certificates') }}"
                class="inline-flex items-center gap-2 rounded-12px border border-d9 px-5 h-12 font-semibold text-16px text-primary hover:bg-fa transition bg-white">
                <span class="icon-[tabler--arrow-narrow-left] size-5"></span>
                العودة لإدارة الشهادات
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px bg-primary text-white px-5 py-4 inline-flex items-center gap-3">
        <span class="icon-[tabler--certificate] size-6 text-color2"></span>
        <p class="font-semibold text-16px">{{ (int) ($totalCount ?? 0) }} شهادة</p>
    </div>

    @if (empty($certificateRows))
        <div class="border border-d9 rounded-14px bg-white p-10 text-center">
            <span class="icon-[tabler--certificate] size-12 text-gray mx-auto mb-3 block"></span>
            <p class="font-semibold text-18px text-primary mb-1">لا توجد شهادات صادرة بعد</p>
            <p class="font-medium text-14px text-gray">ستظهر هنا شهادات الطلاب عند إتمام الدورات أو اجتياز الاختبارات المفعّل لها الشهادات.</p>
        </div>
    @else
        <div class="bg-white border border-d9 rounded-14px overflow-x-auto">
            <table class="table w-full text-15px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="px-4 py-3.5 text-start font-semibold">الطالب</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الشهادة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">النوع</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الدرجة</th>
                        <th class="px-4 py-3.5 text-start font-semibold">التاريخ</th>
                        <th class="px-4 py-3.5 text-start font-semibold">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($certificateRows as $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4 min-w-44">
                                <p class="font-semibold text-16px text-primary">{{ $row['student'] }}</p>
                                <p class="font-medium text-13px text-gray">{{ $row['email'] }}</p>
                            </td>
                            <td class="px-4 py-4 font-medium text-primary min-w-52">{{ $row['title'] }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-[#ECFDF5] text-[#059669] px-3 py-1 font-semibold text-12px">
                                    {{ $row['type'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold">
                                {{ $row['grade'] !== null && $row['grade'] !== '' ? $row['grade'] : '—' }}
                            </td>
                            <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['date'] }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ $row['download_url'] }}"
                                        class="inline-flex items-center gap-1.5 rounded-10px bg-primary px-3 h-10 font-semibold text-13px text-white hover:opacity-95 transition">
                                        <span class="icon-[tabler--download] size-4"></span>
                                        تحميل
                                    </a>
                                    <a href="{{ $row['view_url'] }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1.5 rounded-10px border border-d9 px-3 h-10 font-semibold text-13px text-primary hover:bg-fa transition">
                                        عرض
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
