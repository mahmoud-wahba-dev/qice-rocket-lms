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
    @component('panel_v1.instructor.components.page-header', [
        'title' => 'إدارة الواجبات والتكليفات',
        'subtitle' => 'متابعة تسليمات الطلاب وتصحيح التكليفات عبر جميع دوراتك',
    ])
        @slot('actions')
            <a href="#a-panel-1"
                class="inline-flex items-center gap-2 rounded-12px border border-color2 px-5 h-12 font-semibold text-16px text-color2 hover:opacity-90 transition bg-white">
                عرض جميع التكاليف
            </a>
            <button type="button"
                class="inline-flex items-center gap-2 rounded-12px bg-color2 px-5 h-12 font-semibold text-16px text-white hover:opacity-95 transition"
                data-overlay="#instructor-assignment-create-modal"
                aria-haspopup="dialog" aria-controls="instructor-assignment-create-modal">
                <span class="icon-[tabler--plus] size-5"></span>
                إضافة تكليف جديد
            </button>
        @endslot
    @endcomponent

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        @foreach ($assignmentStats ?? [] as $stat)
            <div class="rounded-14px bg-primary text-white px-5 py-5 flex items-center gap-4 min-h-[110px]">
                <span class="icon-[tabler--school] size-8 text-color2 shrink-0"></span>
                <div>
                    <p class="font-semibold text-28px sm:text-30px leading-none mb-1.5">{{ $stat['value'] }}</p>
                    <p class="font-semibold text-15px sm:text-16px text-white/90 leading-snug">{{ $stat['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        <h2 class="font-semibold text-24px text-primary mb-4">التكليفات الحالية</h2>
        @if (empty($currentAssignments))
            <div class="border border-d9 rounded-14px bg-white p-8 text-center">
                <p class="font-semibold text-18px text-primary mb-2">لا توجد تكليفات حالياً</p>
                <p class="font-medium text-14px text-gray mb-5">أنشئ تكليفاً جديداً من هنا ثم تابع تسليمات الطلاب.</p>
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-12px bg-primary px-5 h-11 font-semibold text-15px text-white hover:opacity-95 transition"
                    data-overlay="#instructor-assignment-create-modal">
                    إضافة تكليف جديد
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ($currentAssignments as $index => $item)
                    <article class="border border-d9 rounded-14px bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
                                    <span class="icon-[tabler--file-pencil] size-5 text-primary"></span>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-18px sm:text-20px text-primary leading-snug mb-1">{{ $item['title'] }}</h3>
                                    <p class="font-medium text-14px text-gray">
                                        {{ $item['course'] }}
                                        <span class="mx-1.5 text-d9">|</span>
                                        آخر موعد: {{ $item['deadline'] }}
                                    </p>
                                </div>
                            </div>
                            @include('panel_v1.components.actions-dropdown', [
                                'id' => 'assign-card-menu-' . $index,
                                'items' => [
                                    ['label' => 'عرض التسليمات', 'url' => $item['review_url']],
                                    ['label' => 'جميع التسليمات', 'url' => $item['course_assignments_url']],
                                    ['label' => 'عرض الدورة', 'url' => $item['edit_url']],
                                ],
                                'class' => 'shrink-0',
                            ])
                        </div>

                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="rounded-10px bg-[#F8FAFC] border border-d9 px-2 py-3 text-center">
                                <p class="font-medium text-12px text-gray mb-1">التسليمات</p>
                                <p class="font-semibold text-15px text-primary">{{ $item['submissions'] }}</p>
                            </div>
                            <div class="rounded-10px bg-[#F8FAFC] border border-d9 px-2 py-3 text-center">
                                <p class="font-medium text-12px text-gray mb-1">بانتظار التصحيح</p>
                                <p class="font-semibold text-15px text-primary">{{ $item['pending'] }}</p>
                            </div>
                            <div class="rounded-10px bg-[#F8FAFC] border border-d9 px-2 py-3 text-center">
                                <p class="font-medium text-12px text-gray mb-1">تم التقييم</p>
                                <p class="font-semibold text-15px text-primary">{{ $item['graded'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 mb-1.5">
                            <p class="font-semibold text-14px text-primary">نسبة تسليم الواجب</p>
                            <p class="font-semibold text-14px text-primary">{{ $item['progress'] }}%</p>
                        </div>
                        <div class="h-2 rounded-full bg-[#EFEFEF] overflow-hidden mb-5">
                            <div class="h-full bg-primary rounded-full" style="width: {{ (int) $item['progress'] }}%"></div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-16px text-primary">{{ $item['points'] }} درجة</span>
                                <span class="font-medium text-13px text-gray">النقاط</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ $item['preview_url'] }}" class="font-semibold text-15px text-primary hover:opacity-80 transition">معاينة</a>
                                <a href="{{ $item['review_url'] }}"
                                    class="relative inline-flex items-center justify-center rounded-10px bg-primary px-4 h-11 font-semibold text-14px text-white hover:opacity-95 transition">
                                    {{ $item['cta'] }}
                                    @if (!empty($item['badge']))
                                        <span class="absolute -top-2 -start-2 size-6 rounded-full bg-[#F59E0B] center font-bold text-11px text-white">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-5 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#a-panel-1" role="tab" aria-selected="true">جميع التكاليف</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-16px sm:text-18px text-gray pb-4 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#a-panel-2" role="tab" aria-selected="false">نتائج تكليفات الطلاب</button>
        </nav>

        <div id="a-panel-1" role="tabpanel" class="bg-white border border-d9 p-5 sm:p-7 rounded-14px">
            <div class="flex flex-col lg:flex-row gap-3 mb-5">
                <div class="relative flex-1">
                    <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                    <input type="search" data-assign-search="results"
                        placeholder="البحث عن طريق المعرف أو اسم الدورة أو غير ذلك..."
                        class="input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px">
                </div>
            </div>

            <div class="border border-d9 rounded-14px overflow-x-auto">
                <table class="table w-full text-15px" id="assign-results-table">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-4 py-3.5 text-start font-semibold">العنوان والدورة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الدرجة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">درجة النجاح</th>
                            <th class="px-4 py-3.5 text-start font-semibold">التسليمات</th>
                            <th class="px-4 py-3.5 text-start font-semibold">قيد الانتظار</th>
                            <th class="px-4 py-3.5 text-start font-semibold">ناجح</th>
                            <th class="px-4 py-3.5 text-start font-semibold">راسب</th>
                            <th class="px-4 py-3.5 text-start font-semibold">موعد التسليم</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الحالة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resultsRows ?? [] as $index => $row)
                            <tr class="border-b border-d9 last:border-0" data-assign-row>
                                <td class="px-4 py-4 min-w-48">
                                    <p class="font-semibold text-16px text-primary">{{ $row['title'] }}</p>
                                    <p class="font-medium text-13px text-gray">{{ $row['course'] }}</p>
                                </td>
                                <td class="px-4 py-4 font-semibold">{{ $row['grade'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['passGrade'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['submissions'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['pending'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['passed'] }}</td>
                                <td class="px-4 py-4 font-semibold">{{ $row['failed'] }}</td>
                                <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $row['deadline'] }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 font-semibold text-13px {{ $statusToneClass[$row['status_tone'] ?? 'success'] ?? $statusToneClass['success'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @include('panel_v1.components.actions-dropdown', [
                                        'id' => 'assign-row-menu-' . $index,
                                        'items' => [
                                            ['label' => 'التسليمات بانتظار المراجعة', 'url' => $row['pending_url']],
                                            ['label' => 'جميع التسليمات', 'url' => $row['course_assignments_url']],
                                            ['label' => 'عرض الدورة', 'url' => $row['edit_url']],
                                            ['label' => 'أداء الدورة', 'url' => $row['course_url']],
                                        ],
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center font-medium text-15px text-gray">لا توجد تكليفات للعرض</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="a-panel-2" class="hidden bg-white border border-d9 p-5 sm:p-7 rounded-14px" role="tabpanel">
            <div class="flex flex-col lg:flex-row gap-3 mb-5">
                <div class="relative flex-1">
                    <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                    <input type="search" data-assign-search="students"
                        placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك..."
                        class="input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px">
                </div>
            </div>

            <div class="border border-d9 rounded-14px overflow-x-auto">
                <table class="table w-full text-15px" id="assign-students-table">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-4 py-3.5 text-start font-semibold">المتدرب</th>
                            <th class="px-4 py-3.5 text-start font-semibold">العنوان والدورة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">التسليم الأول</th>
                            <th class="px-4 py-3.5 text-start font-semibold">التسليم الأخير</th>
                            <th class="px-4 py-3.5 text-start font-semibold">المحاولات</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الدرجة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">تاريخ الانشاء</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الحالة</th>
                            <th class="px-4 py-3.5 text-start font-semibold">الاجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($studentResultsRows ?? [] as $index => $row)
                            <tr class="border-b border-d9 last:border-0" data-assign-row>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="size-11 rounded-full bg-primary/10 center shrink-0">
                                            <span class="font-bold text-16px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                                        </span>
                                        <span class="font-semibold text-16px text-primary whitespace-nowrap">{{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 min-w-48">
                                    <p class="font-semibold text-16px text-primary">{{ $row['title'] }}</p>
                                    <p class="font-medium text-13px text-gray">{{ $row['course'] }}</p>
                                </td>
                                <td class="px-4 py-4 font-medium text-gray">{{ $row['first_at'] }}</td>
                                <td class="px-4 py-4 font-medium text-gray">{{ $row['last_at'] }}</td>
                                <td class="px-4 py-4 font-medium text-gray">{{ $row['attempts'] }}</td>
                                <td class="px-4 py-4 font-medium text-gray">{{ $row['grade'] }}</td>
                                <td class="px-4 py-4">
                                    <div class="leading-tight">
                                        <p class="font-semibold text-20px text-primary">{{ $row['created_day'] }}</p>
                                        <p class="font-medium text-13px text-gray whitespace-nowrap">{{ $row['created_month'] }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 font-semibold text-13px {{ $statusToneClass[$row['status_tone'] ?? 'warning'] ?? $statusToneClass['warning'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @include('panel_v1.components.actions-dropdown', [
                                        'id' => 'student-result-menu-' . $index,
                                        'items' => [
                                            ['label' => 'عرض التكليف', 'url' => $row['review_url']],
                                        ],
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center font-medium text-15px text-gray">لا توجد تسليمات طلاب بعد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const bindSearch = (inputSel, tableSel) => {
        const input = document.querySelector(inputSel);
        const table = document.querySelector(tableSel);
        if (!input || !table) return;
        input.addEventListener('input', () => {
            const q = (input.value || '').trim().toLowerCase();
            table.querySelectorAll('[data-assign-row]').forEach((row) => {
                const text = (row.textContent || '').toLowerCase();
                row.classList.toggle('hidden', q !== '' && !text.includes(q));
            });
        });
    };
    bindSearch('[data-assign-search="results"]', '#assign-results-table');
    bindSearch('[data-assign-search="students"]', '#assign-students-table');
})();
</script>

@include('panel_v1.instructor.components.assignment-create-modal')
@endsection
