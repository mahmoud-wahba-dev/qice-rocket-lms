@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $quizzes = $quizzes ?? collect();
    $paginator = $paginator ?? null;
    $stats = $quizListStats ?? [];
    $showFilters = request()->filled('status') || request()->boolean('filters');
    $from = request('from');
    $to = request('to');
    $dateLabel = 'اختيار التاريخ';
    if ($from || $to) {
        $fromLabel = $from ? date('M j', strtotime($from)) : '…';
        $toLabel = $to ? date('M j', strtotime($to)) : '…';
        $dateLabel = $fromLabel . ' - ' . $toLabel;
    }
@endphp

<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? 'جميع الاختبارات',
        'subtitle' => $stubSubtitle ?? 'إعداد وإدارة التقييمات الأكاديمية والامتحانات الإلكترونية',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.admin.education.quizzes.create') }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition shrink-0">
                <span class="icon-[tabler--plus] size-5"></span>
                إنشاء اختبار جديد
            </a>
        @endslot
    @endcomponent

    @include('panel_v1.admin.components.stats-cards', ['stats' => $stats])

    <div class="border border-d9 rounded-14px bg-white shadow-sm overflow-visible">
        <div class="p-4 sm:p-5 border-b border-d9">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col lg:flex-row flex-wrap items-stretch lg:items-center gap-3">
                @foreach (request()->except(['search', 'page', 'filters']) as $fkey => $fval)
                    @if (!is_array($fval))
                        <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                    @endif
                @endforeach

                <div class="relative flex-1 min-w-[16rem]">
                    <span class="icon-[tabler--search] size-4 absolute start-3.5 top-1/2 -translate-y-1/2 text-gray pointer-events-none"></span>
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 bg-[#F8F8F6] font-medium text-14px text-black focus:outline-none focus:border-primary !ps-10"
                        placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك...">
                </div>

                <button type="submit" name="filters" value="1"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-[#F8F8F6] font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                    <span class="icon-[tabler--filter] size-4"></span>
                    فلتر
                </button>

                <button type="button"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-[#F8F8F6] font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0"
                    onclick="document.getElementById('quiz-date-filters').classList.toggle('hidden')">
                    <span class="icon-[tabler--calendar] size-4"></span>
                    {{ $dateLabel }}
                </button>
            </form>

            <form method="GET" action="{{ url()->current() }}" id="quiz-date-filters"
                class="mt-4 pt-4 border-t border-d9 flex flex-wrap gap-3 items-end {{ ($from || $to || $showFilters) ? '' : 'hidden' }}">
                @foreach (request()->except(['from', 'to', 'status', 'page', 'filters']) as $fkey => $fval)
                    @if (!is_array($fval))
                        <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                    @endif
                @endforeach
                <input type="hidden" name="filters" value="1">
                <div>
                    <label class="font-medium text-12px text-gray mb-1.5 block">من تاريخ</label>
                    <input type="date" name="from" value="{{ $from }}" class="input input-bordered h-11 rounded-10px border-d9 text-13px bg-white">
                </div>
                <div>
                    <label class="font-medium text-12px text-gray mb-1.5 block">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $to }}" class="input input-bordered h-11 rounded-10px border-d9 text-13px bg-white">
                </div>
                <div>
                    <label class="font-medium text-12px text-gray mb-1.5 block">الحالة</label>
                    <select name="status" class="select select-bordered h-11 rounded-10px border-d9 text-13px bg-white min-w-[10rem]">
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(request('status')=='active')>نشط</option>
                        <option value="inactive" @selected(request('status')=='inactive')>غير نشط</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center h-11 px-4 rounded-10px bg-primary text-white text-13px font-semibold">تطبيق</button>
                <a href="{{ url()->current() }}" class="inline-flex items-center h-11 px-4 rounded-10px border border-d9 bg-white text-13px font-medium text-primary">مسح</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-13px sm:text-14px">
                <thead>
                    <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                        <th class="px-3 py-3.5 text-start font-semibold min-w-[14rem]">اسم الاختبار والدورة</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">المدرب</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الأسئلة</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الطلاب</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">متوسط الدرجات</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">الحالة</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">الحالة</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quizzes as $index => $quiz)
                        @php
                            $instructor = $quiz->teacher->full_name
                                ?? $quiz->webinar->teacher->full_name
                                ?? '—';
                            $questionsCount = (int) ($quiz->questions_count ?? 0);
                            $studentsCount = (int) ($quiz->students_count ?? 0);
                            $passedCount = (int) ($quiz->passed_count ?? 0);
                            $avgGrade = $quiz->avg_grade !== null ? (int) round((float) $quiz->avg_grade) : '—';
                            $hasCertificate = !empty($quiz->certificate);
                            $isActive = ($quiz->status ?? '') === 'active';
                        @endphp
                        <tr class="border-b border-d9 last:border-0 hover:bg-[#FAFAF4]/40">
                            <td class="px-3 py-4 min-w-[14rem]">
                                <p class="font-bold text-primary leading-snug">{{ $quiz->title }}</p>
                                <p class="font-medium text-12px text-gray mt-0.5">{{ $quiz->webinar->title ?? '—' }}</p>
                            </td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $instructor }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $questionsCount }}</td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <p class="font-medium text-primary">{{ $studentsCount }} طلاب</p>
                                <p class="font-medium text-12px text-gray mt-0.5">الناجحون: {{ $passedCount }}</p>
                            </td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $avgGrade }}</td>
                            <td class="px-3 py-4 text-center">
                                @if ($hasCertificate)
                                    <span class="icon-[tabler--check] size-5 text-[#059669] inline-block" title="شهادة مفعّلة"></span>
                                @else
                                    <span class="icon-[tabler--x] size-5 text-[#DC2626] inline-block" title="بدون شهادة"></span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-center">
                                <span @class([
                                    'inline-flex rounded-full px-3 py-1 font-semibold text-12px',
                                    'bg-[#D1FAE5] text-[#059669]' => $isActive,
                                    'bg-[#FEE2E2] text-[#B91C1C]' => !$isActive,
                                ])>{{ $isActive ? 'نشط' : 'غير نشط' }}</span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                @include('panel_v1.components.actions-dropdown', [
                                    'id' => 'admin-quiz-actions-' . $quiz->id . '-' . $index,
                                    'items' => [
                                        [
                                            'label' => 'نتائج الطلاب',
                                            'url' => route('panel.v1.admin.education.quiz-results', ['quizId' => $quiz->id]),
                                            'tone' => 'gray',
                                        ],
                                        [
                                            'label' => 'تعديل الاختبار',
                                            'url' => route('panel.v1.admin.education.quizzes.questions', ['id' => $quiz->id]),
                                            'tone' => 'gray',
                                        ],
                                        [
                                            'label' => 'حذف',
                                            'action' => route('panel.v1.admin.education.quizzes.delete', ['id' => $quiz->id]),
                                            'confirm' => 'حذف هذا الاختبار؟',
                                            'tone' => 'danger',
                                        ],
                                    ],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center font-medium text-15px text-gray">لا توجد اختبارات مطابقة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-5 pb-4">
            @include('panel_v1.admin.components.pagination', ['paginator' => $paginator])
        </div>
    </div>
</div>
@endsection
