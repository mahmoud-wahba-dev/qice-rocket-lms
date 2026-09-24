@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $courses = $courses ?? collect();
    $paginator = $paginator ?? null;
    $filterCategories = $filterCategories ?? [];
    $stats = $courseListStats ?? [];
    $showFilters = request()->filled('category_id') || request()->filled('status') || request()->boolean('filters');
@endphp

<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $stubTitle ?? 'جميع الدورات المسجلة',
        'subtitle' => $stubSubtitle ?? 'دورات فيديو مُعدّة مسبقاً يمكن للطلاب مشاهدتها في أي وقت — تعلّم مرن حسب جدولك.',
    ])
        @slot('actions')
            <a href="{{ route('panel.v1.admin.education.courses.create') }}"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition shrink-0">
                <span class="icon-[tabler--plus] size-5"></span>
                إنشاء دورة جديدة
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
                        class="input input-bordered w-full h-12 rounded-12px border-d9 font-medium text-14px text-black focus:outline-none focus:border-primary !ps-10"
                        placeholder="البحث عن طريق المعرّف أو اسم الدورة أو غير ذلك...">
                </div>

                <button type="submit" name="filters" value="1"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                    <span class="icon-[tabler--filter] size-4"></span>
                    فلتر
                </button>

                <a href="{{ route('panel.v1.admin.education.courses.export', request()->query()) }}"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition shrink-0">
                    <span class="icon-[tabler--external-link] size-4"></span>
                    استخراج في الإكسل
                </a>
            </form>

            @if ($showFilters)
                <form method="GET" action="{{ url()->current() }}" class="mt-4 pt-4 border-t border-d9 flex flex-wrap gap-3 items-end">
                    @foreach (request()->except(['category_id', 'status', 'page', 'filters']) as $fkey => $fval)
                        @if (!is_array($fval))
                            <input type="hidden" name="{{ $fkey }}" value="{{ $fval }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="filters" value="1">
                    <div class="min-w-[12rem]">
                        <label class="font-medium text-12px text-gray mb-1.5 block">التصنيف</label>
                        <select name="category_id" onchange="this.form.submit()" class="select select-bordered w-full h-11 rounded-10px border-d9 text-13px bg-white">
                            <option value="">كل التصنيفات</option>
                            @foreach ($filterCategories as $fc)
                                <option value="{{ $fc['id'] }}" @selected(request('category_id') == $fc['id'])>{{ $fc['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[10rem]">
                        <label class="font-medium text-12px text-gray mb-1.5 block">الحالة</label>
                        <select name="status" onchange="this.form.submit()" class="select select-bordered w-full h-11 rounded-10px border-d9 text-13px bg-white">
                            <option value="">كل الحالات</option>
                            <option value="active" @selected(request('status')=='active')>نشط</option>
                            <option value="pending" @selected(request('status')=='pending')>معلقة</option>
                            <option value="is_draft" @selected(request('status')=='is_draft')>مسودة</option>
                            <option value="inactive" @selected(request('status')=='inactive')>مرفوض</option>
                        </select>
                    </div>
                    <a href="{{ url()->current() }}" class="inline-flex items-center h-11 px-4 rounded-10px border border-d9 bg-white text-13px font-medium text-primary hover:bg-[#FAFAF4]">مسح الفلتر</a>
                </form>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-13px sm:text-14px">
                <thead>
                    <tr class="bg-[#FAFAF4] border-b border-d9 text-gray">
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">ID</th>
                        <th class="px-3 py-3.5 text-start font-semibold min-w-[12rem]">العنوان</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">المدرب</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">السعر</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">مبيعات</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الدخل</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الطلاب</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">تاريخ الإنشاء</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">تاريخ آخر تعديل</th>
                        <th class="px-3 py-3.5 text-start font-semibold whitespace-nowrap">الحالة</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">اجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $index => $c)
                        @php
                            $salesCount = (int) ($c->sales_count ?? 0);
                            $salesAmount = (float) ($c->sales_amount ?? 0);
                            $statusMeta = match ($c->status) {
                                'active' => ['label' => 'نشط', 'class' => 'bg-[#D1FAE5] text-[#059669]'],
                                'pending' => ['label' => 'معلقة', 'class' => 'bg-[#FEE2E2] text-[#B91C1C]'],
                                'is_draft' => ['label' => 'مسودة', 'class' => 'bg-[#EFF6FF] text-[#2563EB]'],
                                'inactive' => ['label' => 'مرفوض', 'class' => 'bg-[#FEE2E2] text-[#DC2626]'],
                                default => ['label' => $c->status ?: '—', 'class' => 'bg-[#F1F5F9] text-gray'],
                            };
                            $menuId = 'course-actions-' . $c->id . '-' . $index;
                        @endphp
                        <tr class="border-b border-d9 last:border-0 hover:bg-[#FAFAF4]/40">
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $c->id }}</td>
                            <td class="px-3 py-4 min-w-[12rem]">
                                <p class="font-semibold text-primary leading-snug">{{ $c->title }}</p>
                                @if (!empty($c->category?->title))
                                    <p class="font-medium text-12px text-gray mt-0.5">{{ $c->category->title }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $c->teacher->full_name ?? '—' }}</td>
                            <td class="px-3 py-4 font-semibold text-primary whitespace-nowrap">{{ $c->price ? handlePrice($c->price) : 'مجانية' }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $salesCount }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $salesAmount > 0 ? handlePrice($salesAmount) : '0' }}</td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $salesCount }}</td>
                            <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ !empty($c->created_at) ? dateTimeFormat($c->created_at, 'j F Y') : '—' }}</td>
                            <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ !empty($c->updated_at) ? dateTimeFormat($c->updated_at, 'j F Y') : '—' }}</td>
                            <td class="px-3 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 font-semibold text-12px {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button"
                                        class="dropdown-toggle size-9 rounded-10px border border-d9 bg-white center hover:bg-[#FAFAF4] transition"
                                        aria-label="اجراءات" id="{{ $menuId }}">
                                        <span class="icon-[tabler--dots] size-4 text-gray"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-[15rem] py-1.5 rounded-12px border border-d9 bg-white shadow-xl z-40 text-start"
                                        role="menu" aria-labelledby="{{ $menuId }}">
                                        <li>
                                            <form method="POST" action="{{ route('panel.v1.admin.education.courses.approve', ['id' => $c->id]) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="dropdown-item w-full text-start px-4 py-2.5 font-semibold text-14px text-[#16A34A] hover:bg-[#F0FDF4] {{ $c->status === 'active' ? 'opacity-40 pointer-events-none' : '' }}"
                                                    {{ $c->status === 'active' ? 'disabled' : '' }}>موافقة</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('panel.v1.admin.education.courses.reject', ['id' => $c->id]) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="dropdown-item w-full text-start px-4 py-2.5 font-semibold text-14px text-[#DC2626] hover:bg-[#FEF2F2] {{ $c->status === 'inactive' ? 'opacity-40 pointer-events-none' : '' }}"
                                                    {{ $c->status === 'inactive' ? 'disabled' : '' }}>رفض</button>
                                            </form>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.admin.education.courses.notify', ['id' => $c->id]) }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">إرسال إخطار للمتدربين</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.admin.education.enrollment.history', ['search' => $c->id]) }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">قائمة المتدربين</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.admin.education.statistics') }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">لوحة أداء الدورة</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.admin.education.noticeboard.create') }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">إرسال ملاحظة للمدرب</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('panel.v1.admin.education.courses.edit', ['id' => $c->id]) }}"
                                                class="dropdown-item px-4 py-2.5 font-medium text-14px text-gray hover:bg-[#FAFAF4]">تعديل</a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('panel.v1.admin.education.courses.delete', ['id' => $c->id]) }}"
                                                onsubmit="return confirm('حذف الدورة؟');">
                                                @csrf
                                                <button type="submit" class="dropdown-item w-full text-start px-4 py-2.5 font-semibold text-14px text-[#DC2626] hover:bg-[#FEF2F2]">حذف</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-12 text-center font-medium text-15px text-gray">لا توجد دورات مطابقة.</td>
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
