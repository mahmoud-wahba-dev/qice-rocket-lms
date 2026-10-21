@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $statusToneClass = [
        'success' => 'bg-[#ECFDF5] text-[#059669]',
        'warning' => 'bg-[#FFFBEB] text-[#D97706]',
        'danger' => 'bg-[#FEF2F2] text-[#DC2626]',
        'muted' => 'bg-[#F1F5F9] text-[#64748B]',
    ];
    $rows = $rows ?? [];
@endphp

<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $assignmentTitle ?? 'قائمة طلاب التكليف',
        'subtitle' => ($courseTitle ?? '') ?: 'تسليمات الطلاب ونتائج التصحيح',
    ])
        @slot('actions')
            <a href="{{ $backUrl ?? route('panel.v1.admin.education.section', ['section' => 'assignments']) }}"
                class="inline-flex items-center justify-center gap-2 rounded-12px border border-d9 bg-white px-5 h-12 font-semibold text-15px text-primary hover:bg-fa transition">
                كل التكليفات
            </a>
            @if (!empty($editUrl))
                <a href="{{ $editUrl }}"
                    class="inline-flex items-center justify-center gap-2 rounded-12px bg-color2 text-white px-5 h-12 font-semibold text-15px hover:opacity-95 transition">
                    تعديل التكليف
                </a>
            @endif
        @endslot
    @endcomponent

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($summaryCards ?? [] as $card)
            <div class="rounded-14px bg-white border border-d9 px-4 sm:px-5 py-5"
                style="border-inline-start: 4px solid {{ $card['edge'] ?? '#0f4c45' }}">
                <p class="font-medium text-14px sm:text-15px text-gray mb-3">{{ $card['label'] }}</p>
                <p class="font-semibold text-28px sm:text-30px leading-none {{ $card['valueClass'] ?? 'text-primary' }}">
                    {{ $card['value'] }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="border border-d9 rounded-14px bg-white shadow-sm overflow-visible">
        <div class="p-4 sm:p-5 border-b border-d9 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-18px sm:text-20px text-primary">تسليمات التكليف ونتائج الطلاب</h2>
            <div class="relative flex-1 sm:max-w-sm">
                <span class="icon-[tabler--search] size-4 absolute start-3.5 top-1/2 -translate-y-1/2 text-gray pointer-events-none"></span>
                <input type="search" id="admin-assign-students-search"
                    placeholder="بحث باسم الطالب أو الحالة..."
                    class="input input-bordered w-full h-11 rounded-12px border-d9 bg-[#F8F8F6] font-medium text-14px text-black focus:outline-none focus:border-primary !ps-10">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-13px sm:text-14px" id="admin-assign-students-table">
                <thead>
                    <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                        <th class="px-3 py-3.5 text-start font-semibold min-w-[12rem]">المتدرب</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">تاريخ التسليم</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">المحاولات</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الدرجة</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الحالة</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr class="border-b border-d9 last:border-0 hover:bg-[#FAFAF4]/40" data-assign-student-row>
                            <td class="px-3 py-4 min-w-[12rem]">
                                <div class="flex items-center gap-3">
                                    <span class="size-10 rounded-full bg-primary/10 center shrink-0">
                                        <span class="font-bold text-14px text-primary">{{ mb_substr($row['name'] ?? 'ط', 0, 1) }}</span>
                                    </span>
                                    <div>
                                        <p class="font-bold text-primary leading-snug">{{ $row['name'] ?? 'طالب' }}</p>
                                        @if (!empty($row['email']))
                                            <p class="font-medium text-12px text-gray mt-0.5">{{ $row['email'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $row['submitted_at'] ?? '—' }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $row['attempts'] ?? '—' }}</td>
                            <td class="px-3 py-4 font-semibold text-[#059669] whitespace-nowrap">{{ $row['grade'] ?? '—' }}</td>
                            <td class="px-3 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 font-semibold text-12px {{ $statusToneClass[$row['status_tone'] ?? 'muted'] ?? $statusToneClass['muted'] }}">
                                    {{ $row['status'] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                @if (!empty($row['can_review']) && !empty($row['review_url']))
                                    @include('panel_v1.components.actions-dropdown', [
                                        'id' => 'admin-assign-student-' . ($row['history_id'] ?? $index) . '-' . $index,
                                        'items' => [
                                            [
                                                'label' => 'مراجعة / تصحيح',
                                                'url' => $row['review_url'],
                                                'tone' => 'primary',
                                            ],
                                        ],
                                    ])
                                @else
                                    <span class="font-medium text-13px text-gray">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center font-medium text-15px text-gray">لا توجد تسليمات أو طلاب مسجلون لهذا التكليف.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById('admin-assign-students-search');
    const table = document.getElementById('admin-assign-students-table');
    if (!input || !table) return;
    input.addEventListener('input', () => {
        const q = (input.value || '').trim().toLowerCase();
        table.querySelectorAll('[data-assign-student-row]').forEach((row) => {
            const text = (row.textContent || '').toLowerCase();
            row.classList.toggle('hidden', q !== '' && !text.includes(q));
        });
    });
})();
</script>
@endsection
