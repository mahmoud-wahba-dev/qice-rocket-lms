@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $webinar = $webinar ?? null;
    $students = $students ?? collect();
    $paginator = $paginator ?? null;
    $filterGroups = $filterGroups ?? [];
    $showFilters = request()->filled('status') || request()->filled('group_id') || request()->boolean('filters');
    $courseId = $courseId ?? ($webinar->id ?? 0);
@endphp

<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => ($webinar->title ?? 'الدورة') . ' - الطلاب',
        'subtitle' => 'قائمة الطلاب',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.admin.education.courses.performance', ['id' => $courseId]) }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                لوحة أداء الدورة
            </a>
            <a href="{{ route('panel.v1.admin.education.courses.notify', ['id' => $courseId]) }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-semibold text-14px hover:opacity-95 transition shrink-0">
                <span class="icon-[tabler--bell] size-4"></span>
                إرسال إخطار
            </a>
        @endslot
    @endcomponent

    <div class="border border-d9 rounded-14px bg-white shadow-sm overflow-visible">
        <div class="p-4 sm:p-5 border-b border-d9">
            <form method="GET" action="{{ route('panel.v1.admin.education.courses.students', ['id' => $courseId]) }}"
                class="flex flex-col lg:flex-row flex-wrap items-stretch lg:items-center gap-3">
                @foreach (request()->except(['search', 'page', 'filters']) as $fkey => $fval)
                    @if (!is_array($fval))
                        <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                    @endif
                @endforeach

                <div class="relative flex-1 min-w-[16rem]">
                    <span class="icon-[tabler--search] size-4 absolute start-3.5 top-1/2 -translate-y-1/2 text-gray pointer-events-none"></span>
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="input input-bordered w-full h-12 rounded-12px border-d9 font-medium text-14px text-black focus:outline-none focus:border-primary !ps-10"
                        placeholder="البحث عن طريق المعرف أو الاسم أو البريد...">
                </div>

                <button type="submit" name="filters" value="1"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                    <span class="icon-[tabler--filter] size-4"></span>
                    فلتر
                </button>

                <a href="{{ route('panel.v1.admin.education.courses.students.export', array_merge(['id' => $courseId], request()->except('page'))) }}"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                    <span class="icon-[tabler--external-link] size-4"></span>
                    استخراج في الإكسل
                </a>
            </form>

            @if ($showFilters)
                <form method="GET" action="{{ route('panel.v1.admin.education.courses.students', ['id' => $courseId]) }}"
                    class="mt-4 pt-4 border-t border-d9 flex flex-wrap gap-3 items-end">
                    @foreach (request()->except(['status', 'group_id', 'page', 'filters']) as $fkey => $fval)
                        @if (!is_array($fval))
                            <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="filters" value="1">

                    <div class="min-w-[12rem]">
                        <label class="font-medium text-12px text-gray mb-1.5 block">مجموعة المستخدم</label>
                        <select name="group_id" onchange="this.form.submit()"
                            class="select select-bordered w-full h-11 rounded-10px border-d9 text-13px bg-white">
                            <option value="">كل المجموعات</option>
                            @foreach ($filterGroups as $g)
                                <option value="{{ $g['id'] }}" @selected(request('group_id') == $g['id'])>{{ $g['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-[10rem]">
                        <label class="font-medium text-12px text-gray mb-1.5 block">الحالة</label>
                        <select name="status" onchange="this.form.submit()"
                            class="select select-bordered w-full h-11 rounded-10px border-d9 text-13px bg-white">
                            <option value="">كل الحالات</option>
                            <option value="active" @selected(request('status')==='active')>نشط</option>
                            <option value="blocked" @selected(request('status')==='blocked')>محظور</option>
                            <option value="expire" @selected(request('status')==='expire')>منتهي</option>
                        </select>
                    </div>
                </form>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-14px">
                <thead>
                    <tr class="border-b border-d9 text-gray bg-f9">
                        <th class="font-semibold px-4 py-3.5 text-start">ID</th>
                        <th class="font-semibold px-4 py-3.5 text-start">الاسم</th>
                        <th class="font-semibold px-4 py-3.5 text-start">التقييمات(5)</th>
                        <th class="font-semibold px-4 py-3.5 text-start">التعلم</th>
                        <th class="font-semibold px-4 py-3.5 text-start">مجموعة المستخدم</th>
                        <th class="font-semibold px-4 py-3.5 text-start">الدخل</th>
                        <th class="font-semibold px-4 py-3.5 text-start">تاريخ الشراء</th>
                        <th class="font-semibold px-4 py-3.5 text-start">الحالة</th>
                        <th class="font-semibold px-4 py-3.5 text-start">اجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $index => $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-4 py-4 font-medium text-primary">{{ $row['id'] }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3 min-w-[14rem]">
                                    <span class="size-10 rounded-full bg-primary/10 center overflow-hidden shrink-0">
                                        @if (!empty($row['avatar']))
                                            <img src="{{ $row['avatar'] }}" alt="" class="size-full object-cover">
                                        @else
                                            <span class="font-bold text-14px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-14px text-primary truncate">{{ $row['name'] }}</p>
                                        @if (!empty($row['email']))
                                            <p class="font-medium text-12px text-gray truncate">{{ $row['email'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-semibold text-primary">{{ $row['rate'] }}</td>
                            <td class="px-4 py-4 font-semibold text-primary">{{ $row['learning'] }}%</td>
                            <td class="px-4 py-4 font-medium text-black">{{ $row['group'] }}</td>
                            <td class="px-4 py-4 font-semibold text-primary whitespace-nowrap">{{ $row['income'] }}</td>
                            <td class="px-4 py-4 font-medium text-black whitespace-nowrap">{{ $row['purchase_date'] }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 font-semibold text-12px {{ $row['status_class'] }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @include('panel_v1.components.actions-dropdown', [
                                    'id' => 'course-student-menu-' . $index,
                                    'items' => $row['actions'] ?? [],
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center font-medium text-15px text-gray">
                                لا يوجد متدربون مطابقون للبحث أو الفلتر الحالي
                            </td>
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
