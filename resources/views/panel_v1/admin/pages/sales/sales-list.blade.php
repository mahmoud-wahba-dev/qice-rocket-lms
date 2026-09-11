@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'قائمة المبيعات',
        'subtitle' => $pageSubtitle ?? '',
    ])
        @slot('actions')
            <button type="button"
                class="inline-flex items-center gap-2 h-12 px-5 rounded-12px bg-color2 text-white font-semibold text-15px hover:opacity-95 transition">
                <span class="icon-[tabler--file-spreadsheet] size-5"></span>
                استخرج التقرير إكسل
            </button>
        @endslot
    @endcomponent

    @include('panel_v1.admin.components.stats-cards', ['stats' => $stats ?? []])

    <div class="border border-d9 rounded-14px bg-white p-4 sm:p-6 shadow-sm">
        @include('panel_v1.admin.components.filter-bar')

        <div class="overflow-x-auto">
            <table class="table w-full text-14px sm:text-15px">
                <thead>
                    <tr class="border-b border-d9 bg-f9 text-gray">
                        <th class="px-3 py-3.5 text-start font-semibold">#</th>
                        <th class="px-3 py-3.5 text-start font-semibold">متدرب</th>
                        <th class="px-3 py-3.5 text-start font-semibold">المدرب</th>
                        <th class="px-3 py-3.5 text-start font-semibold">الخدمة</th>
                        <th class="px-3 py-3.5 text-start font-semibold">السعر</th>
                        <th class="px-3 py-3.5 text-start font-semibold">الخصم</th>
                        <th class="px-3 py-3.5 text-start font-semibold">الضريبة</th>
                        <th class="px-3 py-3.5 text-start font-semibold">نوع الخدمة</th>
                        <th class="px-3 py-3.5 text-start font-semibold">التاريخ</th>
                        <th class="px-3 py-3.5 text-start font-semibold">الحالة</th>
                        <th class="px-3 py-3.5 text-start font-semibold">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesRows ?? [] as $i => $row)
                        <tr class="border-b border-d9 last:border-0">
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $row['id'] }}</td>
                            <td class="px-3 py-4 min-w-40">
                                <p class="font-semibold text-primary">{{ $row['student'] }}</p>
                                <p class="font-medium text-12px text-gray">{{ $row['student_email'] }}</p>
                            </td>
                            <td class="px-3 py-4 font-medium text-primary whitespace-nowrap">{{ $row['instructor'] }}</td>
                            <td class="px-3 py-4 font-medium text-primary min-w-36">{{ $row['service'] }}</td>
                            <td class="px-3 py-4 font-semibold text-primary whitespace-nowrap">{{ $row['price'] }}</td>
                            <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ $row['discount'] }}</td>
                            <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ $row['vat'] }}</td>
                            <td class="px-3 py-4 font-medium text-primary">{{ $row['type'] }}</td>
                            <td class="px-3 py-4 font-medium text-gray whitespace-nowrap">{{ $row['date'] }}</td>
                            <td class="px-3 py-4">
                                <span class="inline-flex rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 font-semibold text-12px">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td class="px-3 py-4">
                                <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                    <button type="button" class="dropdown-toggle size-9 rounded-10px border border-d9 center hover:bg-[#FAFAF4]"
                                        aria-label="إجراءات">
                                        <span class="icon-[tabler--dots] size-4 text-primary"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20">
                                        <li><a href="{{ route('panel.v1.admin.sales.section', ['section' => 'sales']) }}" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">عرض الفاتورة</a></li>
                                        <li><button type="button" onclick="alert('الاسترداد من لوحة الإدارة القديمة /admin/financial/sales')" class="dropdown-item px-4 py-2.5 font-medium text-14px text-red-500 w-full text-start">استرداد</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('panel_v1.admin.components.pagination', ['pagination' => $pagination ?? []])
    </div>
</div>
@endsection
