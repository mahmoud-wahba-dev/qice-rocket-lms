@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $salesRows = $salesRows ?? [];
    $walletRows = $walletRows ?? [];
    $summaryCards = $summaryCards ?? [];
    $noDataImg = ($panelInstructorImg ?? asset('assets/panel_v1/img/instructor')) . '/no-data.webp';
@endphp

<div class="space-y-8 sm:space-y-10 pb-10">
    <div class="min-w-0">
        <h1 class="font-semibold text-28px sm:text-32px text-primary mb-2">المالية والأرباح</h1>
        <p class="font-medium text-16px sm:text-18px text-gray leading-relaxed">
            متابعة المبيعات، المعاملات المالية والتقارير.
        </p>
    </div>

    <div>
        <nav class="tabs tabs-bordered flex w-full overflow-x-auto mb-6 sm:mb-8 border-b border-d9" role="tablist">
            <button type="button"
                class="tab active justify-center whitespace-nowrap font-semibold text-18px sm:text-20px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#finance-panel-1" role="tab" aria-selected="true">تقرير المبيعات</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-18px sm:text-20px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#finance-panel-2" role="tab" aria-selected="false">المحفظة والرصيد</button>
            <button type="button"
                class="tab justify-center whitespace-nowrap font-semibold text-18px sm:text-20px text-gray pb-5 active-tab:text-primary active-tab:border-b-primary"
                data-v1-tab="#finance-panel-3" role="tab" aria-selected="false">الملخص المالي</button>
        </nav>

        {{-- Sales report --}}
        <div id="finance-panel-1" role="tabpanel" class="bg-white border border-d9 rounded-14px overflow-x-auto shadow-sm">
            @if (!empty($salesRows))
                <table class="table w-full text-16px sm:text-18px">
                    <thead>
                        <tr class="border-b border-d9 text-gray bg-f9">
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">المتدرب - المستفيد</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">الخدمة</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">السعر الأصلي</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">الخصم</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">المبلغ الإجمالي</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">صافي الدخل</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">نوع الخدمة</th>
                            <th class="px-5 sm:px-6 py-4 sm:py-5 text-start font-semibold text-15px sm:text-16px">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salesRows as $row)
                            @php
                                $type = $row['type'] ?? 'course';
                                $isCourse = $type === 'course';
                            @endphp
                            <tr class="border-b border-d9 last:border-0">
                                <td class="px-5 sm:px-6 py-5 sm:py-6">
                                    <div class="flex items-center gap-3.5 sm:gap-4 min-w-52">
                                        <span class="size-12 sm:size-14 rounded-full bg-primary/10 center shrink-0 overflow-hidden">
                                            <span class="font-bold text-16px sm:text-18px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-17px sm:text-18px text-primary truncate mb-1">
                                                {{ $row['name'] }}
                                            </p>
                                            <p class="font-medium text-14px sm:text-15px text-gray truncate">{{ $row['email'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6 min-w-44">
                                    <p class="font-semibold text-16px sm:text-18px text-primary mb-1">{{ $row['service'] }}</p>
                                    <p class="font-medium text-14px sm:text-15px text-gray">ID:{{ $row['service_id'] }}</p>
                                </td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6 font-semibold text-primary whitespace-nowrap">{{ $row['original_price'] }}</td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6 font-medium text-gray">{{ $row['discount'] }}</td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6 font-semibold text-primary whitespace-nowrap">{{ $row['total'] }}</td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6 font-semibold text-primary whitespace-nowrap">{{ $row['net'] }}</td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6">
                                    <span class="inline-flex items-center gap-2.5 font-semibold text-16px sm:text-17px {{ $isCourse ? 'text-primary' : 'text-[#0FC787]' }}">
                                        <span class="size-2.5 rounded-full shrink-0 {{ $isCourse ? 'bg-[#22D3EE]' : 'bg-[#0FC787]' }}"></span>
                                        {{ $row['type_label'] }}
                                    </span>
                                </td>
                                <td class="px-5 sm:px-6 py-5 sm:py-6 whitespace-nowrap">
                                    <p class="font-semibold text-16px sm:text-17px text-primary mb-1">{{ $row['date'] }}</p>
                                    <p class="font-medium text-14px sm:text-15px text-gray">{{ $row['time'] }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-16 sm:py-20 center flex-col text-center">
                    <img src="{{ $noDataImg }}" alt="" class="w-56 sm:w-72 max-w-full mb-8" loading="lazy" decoding="async">
                    <p class="font-semibold text-22px sm:text-28px text-black">لا توجد مبيعات حالياً</p>
                </div>
            @endif
        </div>

        {{-- Wallet --}}
        <div id="finance-panel-2" class="hidden" role="tabpanel">
            @if (!empty($walletRows))
                <div class="bg-white border border-d9 rounded-14px overflow-x-auto shadow-sm">
                    <table class="table w-full text-16px sm:text-18px">
                        <thead>
                            <tr class="border-b border-d9 text-gray bg-f9">
                                <th class="px-5 sm:px-6 py-4 text-start font-semibold text-15px">الوصف</th>
                                <th class="px-5 sm:px-6 py-4 text-start font-semibold text-15px">النوع</th>
                                <th class="px-5 sm:px-6 py-4 text-start font-semibold text-15px">المبلغ</th>
                                <th class="px-5 sm:px-6 py-4 text-start font-semibold text-15px">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($walletRows as $row)
                                <tr class="border-b border-d9 last:border-0">
                                    <td class="px-5 sm:px-6 py-5 font-semibold text-primary">{{ $row['title'] }}</td>
                                    <td class="px-5 sm:px-6 py-5">
                                        <span class="inline-flex items-center gap-2 font-semibold {{ ($row['type'] ?? '') === 'credit' ? 'text-[#0FC787]' : 'text-[#EF4444]' }}">
                                            <span class="size-2 rounded-full {{ ($row['type'] ?? '') === 'credit' ? 'bg-[#0FC787]' : 'bg-[#EF4444]' }}"></span>
                                            {{ $row['type_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 sm:px-6 py-5 font-bold text-primary whitespace-nowrap">{{ $row['amount'] }}</td>
                                    <td class="px-5 sm:px-6 py-5 whitespace-nowrap">
                                        <p class="font-semibold text-primary">{{ $row['date'] }}</p>
                                        <p class="font-medium text-14px text-gray">{{ $row['time'] }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-14px bg-transparent px-6 py-16 sm:py-20 center flex-col text-center">
                    <img src="{{ $noDataImg }}" alt="" class="w-56 sm:w-72 max-w-full mb-8" loading="lazy" decoding="async">
                    <p class="font-semibold text-22px sm:text-28px text-black">لا توجد معاملات مالية حالية</p>
                </div>
            @endif
        </div>

        {{-- Financial summary --}}
        <div id="finance-panel-3" class="hidden" role="tabpanel">
            @if (!empty($summaryCards))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    @foreach ($summaryCards as $card)
                        @php
                            // Full static class names so Iconify/Tailwind can scan them at build time.
                            $iconClass = match ($card['icon'] ?? '') {
                                'tabler--shopping-cart' => 'icon-[tabler--shopping-cart]',
                                'tabler--currency-riyal' => 'icon-[tabler--currency-riyal]',
                                'tabler--wallet' => 'icon-[tabler--wallet]',
                                'tabler--chart-bar' => 'icon-[tabler--chart-bar]',
                                'tabler--receipt' => 'icon-[tabler--receipt]',
                                'tabler--credit-card' => 'icon-[tabler--credit-card]',
                                'tabler--coin' => 'icon-[tabler--currency-riyal]',
                                'tabler--discount' => 'icon-[tabler--receipt]',
                                'tabler--percentage' => 'icon-[tabler--credit-card]',
                                default => 'icon-[tabler--wallet]',
                            };
                        @endphp
                        <div class="rounded-14px border border-d9 bg-white px-5 py-6 flex items-center justify-between gap-4 shadow-sm">
                            <div class="min-w-0">
                                <p class="font-medium text-14px text-gray mb-2">{{ $card['label'] }}</p>
                                <p class="font-bold text-22px sm:text-24px text-primary truncate">{{ $card['value'] }}</p>
                            </div>
                            <span class="size-12 rounded-full bg-primary/10 center shrink-0">
                                <span class="{{ $iconClass }} size-6 text-primary"></span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-14px bg-transparent px-6 py-16 sm:py-20 center flex-col text-center">
                    <img src="{{ $noDataImg }}" alt="" class="w-56 sm:w-72 max-w-full mb-8" loading="lazy" decoding="async">
                    <p class="font-semibold text-22px sm:text-28px text-black">لا توجد معاملات مالية حالية</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
