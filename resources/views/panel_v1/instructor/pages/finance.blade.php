@extends('panel_v1.instructor.layouts.app')

@section('content')
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
                    @foreach ($salesRows ?? [] as $row)
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
                                        <a href="#" class="font-semibold text-17px sm:text-18px text-[#0D9488] underline underline-offset-2 hover:opacity-80 transition truncate block mb-1">
                                            {{ $row['name'] }}
                                        </a>
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
        </div>

        {{-- Wallet --}}
        <div id="finance-panel-2" class="hidden" role="tabpanel">
            @php $noDataImg = ($panelInstructorImg ?? asset('assets/panel_v1/img/instructor')) . '/no-data.webp'; @endphp
            <div class="rounded-14px bg-transparent px-6 py-16 sm:py-20 center flex-col text-center">
                <img src="{{ $noDataImg }}" alt="" class="w-56 sm:w-72 max-w-full mb-8" loading="lazy" decoding="async">
                <p class="font-semibold text-22px sm:text-28px text-black">لا توجد معاملات مالية حالية</p>
            </div>
        </div>

        {{-- Financial summary --}}
        <div id="finance-panel-3" class="hidden" role="tabpanel">
            <div class="rounded-14px bg-transparent px-6 py-16 sm:py-20 center flex-col text-center">
                <img src="{{ $noDataImg }}" alt="" class="w-56 sm:w-72 max-w-full mb-8" loading="lazy" decoding="async">
                <p class="font-semibold text-22px sm:text-28px text-black">لا توجد معاملات مالية حالية</p>
            </div>
        </div>
    </div>
</div>
@endsection
