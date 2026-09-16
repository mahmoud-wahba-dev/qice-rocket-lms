@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $summary = $payoutSummary ?? [];
    $filters = $payoutFilters ?? ['q' => '', 'status' => '', 'from' => '', 'to' => ''];
    $statusToneClass = [
        'success' => 'bg-[#ECFDF5] text-[#059669]',
        'warning' => 'bg-[#FFFBEB] text-[#D97706]',
        'danger' => 'bg-[#FEF2F2] text-[#DC2626]',
        'muted' => 'bg-[#F1F5F9] text-[#64748B]',
    ];
@endphp

<div class="space-y-8 sm:space-y-10 pb-10">
    <div class="min-w-0">
        <h1 class="font-semibold text-28px sm:text-32px text-primary mb-2">ادارة المستحقات والسحب</h1>
        <p class="font-medium text-16px sm:text-18px text-gray leading-relaxed">
            متابعة الرصيد المتاح وطلبات السحب وسجل العمليات المالية.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">
        <article class="rounded-14px lg:col-span-6 bg-primary text-white p-5 sm:p-6 flex flex-col min-h-[220px] shadow-sm">
            <p class="font-semibold text-15px sm:text-16px text-white/90 mb-3">الرصيد المتاح للسحب</p>
            <p class="font-bold text-36px sm:text-40px leading-none mb-5">
                {{ $summary['available'] ?? '0.00' }}
            </p>
            <div class="border-t border-white/20 pt-4 mb-5 space-y-1.5">
                <p class="font-medium text-13px sm:text-14px text-white/85">
                    الموعد القادم للصرف: {{ $summary['next_payout'] ?? '—' }}
                </p>
                <p class="font-medium text-13px sm:text-14px text-white/85">
                    الحد الأدنى للسحب: {{ $summary['min_withdraw'] ?? '—' }}
                </p>
                <p class="font-medium text-13px sm:text-14px text-white/85">
                    الحساب البنكي: {{ $summary['bank_label'] ?? 'غير محدد' }}
                </p>
            </div>
            <div class="mt-auto grid grid-cols-2 gap-2.5">
                <form method="POST" action="{{ route('panel.v1.instructor.payouts.request') }}"
                    onsubmit="return confirm('تأكيد طلب سحب الرصيد المتاح؟');">
                    @csrf
                    <button type="submit"
                        @disabled(empty($summary['can_request']))
                        class="w-full inline-flex items-center justify-center rounded-12px bg-white h-11 px-3 font-bold text-14px text-primary hover:opacity-95 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        طلب سحب
                    </button>
                </form>
                <a href="{{ $settingsUrl ?? route('panel.v1.instructor.settings') }}"
                    class="inline-flex items-center justify-center rounded-12px bg-white/15 border border-white/30 h-11 px-3 font-semibold text-14px text-white hover:bg-white/25 transition">
                    معلومات السحب
                </a>
            </div>
        </article>

        <article class="rounded-14px lg:col-span-3 border border-d9 bg-white p-5 sm:p-6 flex flex-col min-h-[220px] shadow-sm">
            <div class="size-10 rounded-10px bg-[#FAF8F4] center mb-4">
                <span class="icon-[tabler--wallet] size-5 text-color2"></span>
            </div>
            <p class="font-medium text-15px sm:text-16px text-gray mb-2">إجمالي الدخل (المبيعات)</p>
            <p class="font-bold text-32px sm:text-36px text-primary leading-none mb-3">
                {{ $summary['total_income'] ?? '0.00' }}
            </p>
            <p class="mt-auto font-semibold text-14px text-[#0FC787]">من المبيعات والمحاسبة</p>
        </article>

        <article class="rounded-14px lg:col-span-3 border border-d9 bg-white p-5 sm:p-6 flex flex-col min-h-[220px] shadow-sm">
            <div class="size-10 rounded-10px bg-[#FAF8F4] center mb-4">
                <span class="icon-[tabler--lock] size-5 text-color2"></span>
            </div>
            <p class="font-medium text-15px sm:text-16px text-gray mb-2">المبلغ المحتجز (المعلق)</p>
            <p class="font-bold text-32px sm:text-36px text-primary leading-none mb-3">
                {{ $summary['held'] ?? '0.00' }}
            </p>
            <p class="mt-auto font-medium text-14px text-gray">طلبات سحب قيد المعالجة</p>
        </article>
    </div>

    <div>
        <h2 class="font-semibold text-24px sm:text-26px text-primary mb-5">سجل عمليات السحب</h2>

        <div class="bg-white border border-d9 rounded-14px p-4 sm:p-6 shadow-sm">
            <form method="GET" action="{{ route('panel.v1.instructor.payouts') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3 mb-5">
                <div class="relative lg:col-span-4">
                    <span class="icon-[tabler--search] size-5 absolute top-1/2 start-3 -translate-y-1/2 text-gray"></span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="ابحث برقم العملية أو المبلغ أو البنك..."
                        class="input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 bg-[#F8FAFC] ps-10 font-medium text-15px sm:text-16px">
                </div>
                <select name="status"
                    class="select select-bordered lg:col-span-2 h-12 sm:h-14 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary">
                    <option value="">كل الحالات</option>
                    <option value="waiting" @selected(($filters['status'] ?? '') === 'waiting')>قيد المعالجة</option>
                    <option value="done" @selected(($filters['status'] ?? '') === 'done')>مكتمل</option>
                    <option value="reject" @selected(($filters['status'] ?? '') === 'reject')>مرفوض</option>
                </select>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                    class="input input-bordered lg:col-span-2 h-12 sm:h-14 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-14px">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                    class="input input-bordered lg:col-span-2 h-12 sm:h-14 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-14px">
                <div class="lg:col-span-2 flex flex-col sm:flex-row gap-2">
                    <button type="submit"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-10px bg-primary px-4 h-12 sm:h-14 font-semibold text-15px text-white hover:opacity-95 transition">
                        <span class="icon-[tabler--filter] size-4"></span>
                        فلتر
                    </button>
                    <a href="{{ $exportUrl ?? route('panel.v1.instructor.payouts.export') }}"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-10px border border-d9 px-4 h-12 sm:h-14 font-semibold text-14px sm:text-15px text-primary bg-white hover:bg-fa transition whitespace-nowrap">
                        <span class="icon-[tabler--file-spreadsheet] size-4"></span>
                        استخراج Excel
                    </a>
                </div>
            </form>

            <div class="border border-d9 rounded-14px overflow-x-auto">
                <table class="table w-full text-15px sm:text-16px">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold">رقم العملية / التاريخ</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold">نوع المعاملة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold">المبلغ</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold">الحالة</th>
                            <th class="px-4 sm:px-5 py-4 text-start font-semibold">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payoutRows ?? [] as $row)
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-4 sm:px-5 py-5 whitespace-nowrap">
                                    <p class="font-bold text-16px sm:text-17px text-primary mb-1">{{ $row['id'] }}</p>
                                    <p class="font-medium text-14px text-gray">{{ $row['datetime'] }}</p>
                                </td>
                                <td class="px-4 sm:px-5 py-5 min-w-48">
                                    <p class="font-semibold text-16px text-primary leading-snug">{{ $row['type_line1'] }}</p>
                                    @if (!empty($row['type_line2']))
                                        <p class="font-medium text-14px text-gray mt-0.5">{{ $row['type_line2'] }}</p>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-5 font-bold text-16px sm:text-17px text-[#0FC787] whitespace-nowrap">
                                    {{ $row['amount'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1.5 font-semibold text-13px sm:text-14px {{ $statusToneClass[$row['status_tone'] ?? 'muted'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-5 py-5">
                                    <details class="relative">
                                        <summary class="list-none cursor-pointer inline-flex items-center justify-center rounded-10px bg-[#F1F5F9] px-4 h-10 font-semibold text-14px text-primary hover:bg-[#E8ECEA] transition">
                                            التفاصيل
                                        </summary>
                                        <div class="absolute end-0 z-20 mt-2 w-64 rounded-12px border border-d9 bg-white p-4 shadow-xl">
                                            <p class="font-semibold text-14px text-primary mb-1">البنك</p>
                                            <p class="font-medium text-13px text-gray mb-3">{{ $row['bank'] ?? '—' }}</p>
                                            <p class="font-semibold text-14px text-primary mb-1">المبلغ</p>
                                            <p class="font-medium text-13px text-gray mb-3">{{ $row['amount'] }}</p>
                                            <p class="font-semibold text-14px text-primary mb-1">ملاحظة</p>
                                            <p class="font-medium text-13px text-gray">{{ $row['note'] ?? 'لا توجد ملاحظات' }}</p>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-16 text-center">
                                    <span class="icon-[tabler--cash] size-10 text-gray mx-auto mb-3 block"></span>
                                    <p class="font-semibold text-16px text-primary mb-1">لا توجد عمليات سحب</p>
                                    <p class="font-medium text-14px text-gray">جرّب تغيير الفلاتر أو أرسل طلب سحب جديد.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
