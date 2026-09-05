@extends('panel_v1.student.layouts.app')

@section('content')
<section class="">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">عمليات الشراء الخاصة بي</h1>
            <p class="font-semibold text-24px text-gray">عمليات الشراء الخاصة بي</p>
        </div>

        <nav class="student-dash-tabs tabs tabs-bordered tabs-lg w-[75%] overflow-x-auto mb-12"
            aria-label="أقسام عمليات الشراء" role="tablist" aria-orientation="horizontal">
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5 active"
                id="purchase-tabs-item-1" data-tab="#purchase-tabs-1" aria-controls="purchase-tabs-1" role="tab"
                aria-selected="true">
                تفاصيل الشراء
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="purchase-tabs-item-2" data-tab="#purchase-tabs-2" aria-controls="purchase-tabs-2" role="tab"
                aria-selected="false">
                المحفظة
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="purchase-tabs-item-3" data-tab="#purchase-tabs-3" aria-controls="purchase-tabs-3" role="tab"
                aria-selected="false">
                الأرباح والعمولات
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="purchase-tabs-item-4" data-tab="#purchase-tabs-4" aria-controls="purchase-tabs-4" role="tab"
                aria-selected="false">
                اشتراك الباقات
            </button>
        </nav>

        <div>
            <div id="purchase-tabs-1" role="tabpanel" aria-labelledby="purchase-tabs-item-1">
                <div class="hidden md:grid md:grid-cols-[minmax(0,2.4fr)_minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,0.9fr)] md:gap-x-6 px-6 mb-13"
                    role="row">
                    <p class="font-semibold text-24px text-gray" role="columnheader">الشراء</p>
                    <p class="font-semibold text-24px text-gray text-center" role="columnheader">المبلغ</p>
                    <p class="font-semibold text-24px text-gray text-center" role="columnheader">تاريخ الدفع</p>
                    <p class="font-semibold text-24px text-gray text-center" role="columnheader">حالة الدفع</p>
                </div>

                <div class="flex flex-col gap-9" role="rowgroup">
                    {{-- Static purchase rows — replace with backend data when ready --}}
                    <article
                        class="grid grid-cols-1 gap-y-4 md:grid-cols-[minmax(0,2.4fr)_minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,0.9fr)] md:gap-x-6 md:items-center border border-d9 rounded-16px bg-white px-6 py-5"
                        role="row">
                        <div class="flex items-center gap-4 min-w-0" role="cell">
                            <div class="size-14 rounded-12px bg-primary shrink-0"></div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-24px text-black mb-1 leading-snug">
                                    قياس النجاح والجودة
                                </h2>
                                <p class="font-medium text-16px text-gray">الادارة والتنفيذ</p>
                            </div>
                        </div>

                        <p class="font-semibold text-24px text-black md:text-center" role="cell">
                            1,234 ريال
                        </p>

                        <div class="md:text-center" role="cell">
                            <p class="font-semibold text-20px text-[#AAAAAA] mb-2">8 نوفمبر</p>
                            <a href="#"
                                class="inline-flex items-center justify-center gap-2 font-bold text-20px text-primary hover:opacity-80 transition">
                                <span class="icon-[tabler--file-invoice] size-5"></span>
                                تحميل الفاتورة
                            </a>
                        </div>

                        <p class="font-semibold text-24px text-[#00B31B] md:text-center" role="cell">
                            مدفوع
                        </p>
                    </article>
     <article
                        class="grid grid-cols-1 gap-y-4 md:grid-cols-[minmax(0,2.4fr)_minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,0.9fr)] md:gap-x-6 md:items-center border border-d9 rounded-16px bg-white px-6 py-5"
                        role="row">
                        <div class="flex items-center gap-4 min-w-0" role="cell">
                            <div class="size-14 rounded-12px bg-primary shrink-0"></div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-24px text-black mb-1 leading-snug">
                                    قياس النجاح والجودة
                                </h2>
                                <p class="font-medium text-16px text-gray">الادارة والتنفيذ</p>
                            </div>
                        </div>

                        <p class="font-semibold text-24px text-black md:text-center" role="cell">
                            1,234 ريال
                        </p>

                        <div class="md:text-center" role="cell">
                            <p class="font-semibold text-20px text-[#AAAAAA] mb-2">8 نوفمبر</p>
                            <a href="#"
                                class="inline-flex items-center justify-center gap-2 font-bold text-20px text-primary hover:opacity-80 transition">
                                <span class="icon-[tabler--file-invoice] size-5"></span>
                                تحميل الفاتورة
                            </a>
                        </div>

                        <p class="font-semibold text-24px text-[#00B31B] md:text-center" role="cell">
                            مدفوع
                        </p>
                    </article>

                </div>
            </div>

            <div id="purchase-tabs-2" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-2">
                <p class="font-medium text-18px text-gray py-10 text-center">المحفظة</p>
            </div>

            <div id="purchase-tabs-3" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-3">
                <p class="font-medium text-18px text-gray py-10 text-center">الأرباح والعمولات</p>
            </div>

            <div id="purchase-tabs-4" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-4">
                <p class="font-medium text-18px text-gray py-10 text-center">اشتراك الباقات</p>
            </div>
        </div>
    </div>
</section>
@endsection
