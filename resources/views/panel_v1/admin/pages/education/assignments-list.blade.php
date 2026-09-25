@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $assignments = $assignments ?? collect();
    $paginator = $paginator ?? null;
    $stats = $assignmentListStats ?? [];
    $enrolledMap = $assignmentEnrolledByWebinar ?? [];
    $showFilters = request()->filled('status') || request()->boolean('filters');
@endphp

<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? 'جميع التكليفات والواجبات',
        'subtitle' => $stubSubtitle ?? 'متابعة وتقييم المهام الدراسية للطلاب',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.admin.education.assignments.create') }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition shrink-0">
                <span class="icon-[tabler--plus] size-5"></span>
                إنشاء تكليف جديد
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

                <a href="{{ url()->current() . '?' . http_build_query(array_merge(request()->query(), ['export' => '1'])) }}"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-[#F8F8F6] font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                    <span class="icon-[tabler--external-link] size-4"></span>
                    استخراج في الإكسل
                </a>
            </form>

            @if ($showFilters)
                <form method="GET" action="{{ url()->current() }}" class="mt-4 pt-4 border-t border-d9 flex flex-wrap gap-3 items-end">
                    @foreach (request()->except(['status', 'page', 'filters']) as $fkey => $fval)
                        @if (!is_array($fval))
                            <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="filters" value="1">
                    <div>
                        <label class="font-medium text-12px text-gray mb-1.5 block">الحالة</label>
                        <select name="status" onchange="this.form.submit()" class="select select-bordered h-11 rounded-10px border-d9 text-13px bg-white min-w-[10rem]">
                            <option value="">كل الحالات</option>
                            <option value="active" @selected(request('status')=='active')>نشط</option>
                            <option value="inactive" @selected(request('status')=='inactive')>غير نشط</option>
                        </select>
                    </div>
                    <a href="{{ url()->current() }}" class="inline-flex items-center h-11 px-4 rounded-10px border border-d9 bg-white text-13px font-medium text-primary">مسح الفلتر</a>
                </form>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-13px sm:text-14px">
                <thead>
                    <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                        <th class="px-3 py-3.5 text-start font-semibold min-w-[14rem]">اسم الواجب والدورة</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">طلاب</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الدرجة الكلية</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">درجة النجاح</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">الحالة</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $index => $a)
                        @php
                            $submitted = (int) ($a->submissions_count ?? 0);
                            $enrolled = (int) ($enrolledMap[$a->webinar_id] ?? 0);
                            $isActive = ($a->status ?? '') === 'active';
                            $studentsUrl = route('panel.v1.admin.education.enrollment.history', ['search' => $a->webinar_id]);
                            $editUrl = route('panel.v1.admin.education.assignments.edit', ['id' => $a->id]);
                        @endphp
                        <tr class="border-b border-d9 last:border-0 hover:bg-[#FAFAF4]/40">
                            <td class="px-3 py-4 min-w-[14rem]">
                                <p class="font-bold text-primary leading-snug">{{ $a->title ?? ('تكليف #'.$a->id) }}</p>
                                <p class="font-medium text-12px text-gray mt-0.5">{{ $a->webinar->title ?? '—' }}</p>
                            </td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $submitted }} / {{ $enrolled }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $a->grade ?? '—' }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $a->pass_grade ?? '—' }}</td>
                            <td class="px-3 py-4 text-center">
                                <span @class([
                                    'inline-flex rounded-full px-3 py-1 font-semibold text-12px',
                                    'bg-[#D1FAE5] text-[#059669]' => $isActive,
                                    'bg-[#FEE2E2] text-[#B91C1C]' => !$isActive,
                                ])>{{ $isActive ? 'نشط' : 'غير نشط' }}</span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                @include('panel_v1.components.actions-dropdown', [
                                    'id' => 'admin-assignment-actions-' . $a->id . '-' . $index,
                                    'items' => [
                                        [
                                            'label' => 'قائمة الطلاب',
                                            'url' => $studentsUrl,
                                            'tone' => 'gray',
                                        ],
                                        [
                                            'label' => 'تعديل',
                                            'url' => $editUrl,
                                            'tone' => 'gray',
                                        ],
                                        [
                                            'label' => 'حذف',
                                            'action' => route('panel.v1.admin.education.assignments.delete', ['id' => $a->id]),
                                            'confirm' => 'حذف هذا التكليف؟',
                                            'tone' => 'danger',
                                        ],
                                    ],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center font-medium text-15px text-gray">لا توجد تكليفات مطابقة.</td>
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
