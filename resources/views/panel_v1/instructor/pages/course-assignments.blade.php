@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $statusToneClass = [
        'success' => 'bg-[#ECFDF5] text-[#059669]',
        'warning' => 'bg-[#FFFBEB] text-[#D97706]',
        'danger' => 'bg-[#FEF2F2] text-[#DC2626]',
        'muted' => 'bg-[#F1F5F9] text-[#64748B]',
    ];
@endphp

<div class="space-y-6 pb-8">
    @include('panel_v1.instructor.components.page-header', [
        'title' => $pageTitleMain ?? 'متطلبات الدورات',
        'subtitle' => $pageSubtitle ?? '',
    ])

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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <h2 class="font-semibold text-24px text-primary">تسليمات التكليف وإدارة الطلاب</h2>
            <a href="{{ route('panel.v1.instructor.assignments') }}"
                class="inline-flex items-center gap-2 font-semibold text-15px text-primary hover:opacity-80 transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                كل التكليفات
            </a>
        </div>

        <div class="flex flex-col lg:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                <input type="search" id="course-assign-search"
                    placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك..."
                    class="input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px">
            </div>
        </div>

        <div class="border border-d9 rounded-14px overflow-x-auto">
            <table class="table w-full text-15px" id="course-assign-table">
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
                    @forelse ($submissions ?? [] as $index => $row)
                        <tr class="border-b border-d9 last:border-0" data-course-assign-row>
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
                                <span class="inline-flex rounded-full px-3 py-1 font-semibold text-13px {{ $statusToneClass[$row['status_tone'] ?? 'warning'] ?? $statusToneClass['warning'] }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @include('panel_v1.components.actions-dropdown', [
                                    'id' => 'course-assign-menu-' . $index,
                                    'items' => [
                                        ['label' => 'عرض التكليف', 'url' => $row['review_url']],
                                    ],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center font-medium text-15px text-gray">لا توجد تسليمات لهذه الدورة بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById('course-assign-search');
    const table = document.getElementById('course-assign-table');
    if (!input || !table) return;
    input.addEventListener('input', () => {
        const q = (input.value || '').trim().toLowerCase();
        table.querySelectorAll('[data-course-assign-row]').forEach((row) => {
            const text = (row.textContent || '').toLowerCase();
            row.classList.toggle('hidden', q !== '' && !text.includes(q));
        });
    });
})();
</script>
@endsection
