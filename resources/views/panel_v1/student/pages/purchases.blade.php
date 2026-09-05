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
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    {{-- Current balance --}}
                    <div class="lg:col-span-7 rounded-20px bg-primary text-white px-10 py-12 relative overflow-hidden flex flex-col justify-between min-h-[22rem]"
                        style="background: linear-gradient(135deg, #1a6b60 0%, #0f4c45 45%, #0a3833 100%);">
                        {{-- Decorative blur circles: top-left + bottom-right --}}
                        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                            <div class="absolute -top-24 -end-24 size-72 rounded-full bg-[#0FC787] blur-[97px]">
                            </div>
                            <div class="absolute -bottom-24 -start-24 size-72 rounded-full bg-[#0FC787] blur-[97px]">
                            </div>
                        </div>

                        <p class="relative font-medium text-24px text-white mb-10">الرصيد الحالي</p>

                        <div class="relative flex items-center gap-3 mb-14">

                            <span
                                class="font-semibold text-48px text-white leading-none tracking-tight">184,250.75</span>

                            <span class=""><svg width="32" height="36" viewBox="0 0 32 36" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_56_980)">
                                        <path
                                            d="M19.9154 31.4121C19.3443 32.6675 18.9669 34.0298 18.8223 35.4585L30.9065 32.9116C31.4775 31.6565 31.8547 30.2939 31.9996 28.8652L19.9154 31.4121Z"
                                            fill="white" />
                                        <path
                                            d="M30.9069 25.2824C31.4779 24.0273 31.8554 22.6647 32 21.236L22.5868 23.221V19.4052L30.9066 17.6523C31.4776 16.3972 31.8551 15.0345 31.9997 13.6059L22.5865 15.5891V1.86641C21.1441 2.66936 19.8631 3.73817 18.8219 4.99891V16.3828L15.0572 17.1761V0C13.6148 0.80267 12.3338 1.87177 11.2925 3.1325V17.9692L2.86911 19.7439C2.29808 20.999 1.92033 22.3616 1.77544 23.7903L11.2925 21.785V26.5903L1.0931 28.7392C0.52207 29.9943 0.144608 31.3569 0 32.7856L10.676 30.5362C11.545 30.357 12.292 29.8475 12.7776 29.1465L14.7355 26.2685V26.268C14.9388 25.9702 15.0572 25.6112 15.0572 25.2246V20.9916L18.8219 20.1983V27.8299L30.9066 25.2819L30.9069 25.2824Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_56_980">
                                            <rect width="32" height="35.4595" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                        </div>

                        <button type="button"
                            class="relative btn bg-white hover:bg-white/95 text-primary border-0 rounded-12px h-14 font-bold text-20px w-full">
                            + اضف رصيد الان
                        </button>
                    </div>

                    {{-- Latest transactions --}}
                    <div class="lg:col-span-5 border border-[#DDDDDD] rounded-20px bg-white px-8 py-8">
                        <h3 class="font-medium text-20px text-[#7C7C7C] mb-8">اخر التعاملات</h3>

                        <div class="divide-y divide-d9">
                            @foreach ([1, 2, 3, 4] as $i)
                            <div class="flex items-center justify-between gap-4 py-5 first:pt-0 last:pb-0">
                                <div>
                                    <p class="font-semibold text-18px text-colorsub mb-1">إيداع تحويل بنكي</p>
                                    <p class="font-medium text-12px text-colorborder">اليوم</p>
                                </div>
                                <p class="font-bold text-16px text-[#00331E] shrink-0">+ 50,000</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div id="purchase-tabs-3" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-3">
                <div class="bg-[#F9F5F5] rounded-12px px-8 py-6 mb-10">
                    <h3 class="font-semibold text-24px text-primary mb-2">إدارة طلبات سحب الأرباح والعمولات</h3>
                    <p class="font-semibold text-15px text-[#64748B]">
                        متابعة رصيدك المتاح وتقديم طلبات السحب للحساب البنكي.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-primary rounded-16px px-8 py-8 text-white">
                        <p class="font-bold text-20px text-white mb-10">الرصيد الجاهز للدفع</p>
                        <p class="font-semibold text-32px text-white  leading-none">0.00</p>
                    </div>
                    <div class="bg-primary rounded-16px px-8 py-8 text-white">
                        <p class="font-bold text-16px text-white mb-10">قيد المعالجة / معلق</p>
                        <p class="font-semibold text-32px text-white  leading-none">0.00</p>
                    </div>
                    <div class="bg-primary rounded-16px px-8 py-8 text-white">
                        <p class="font-bold text-16px text-white mb-10">إجمالي الأرباح المكتسبة</p>
                        <p class="font-semibold text-32px text-white  leading-none">0.00</p>
                    </div>
                </div>

                <div
                    class="border border-d9 rounded-16px bg-white px-8 py-6 mb-12 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="min-w-0">
                        <p class="font-bold text-20px text-[#0F172A] mb-3">
                            طريقة السحب المحددة:
                            <span class="text-[#C99C69]">حساب بنكي (**** 4821)</span>
                        </p>
                        <p class="font-medium text-20px text-gray flex items-start gap-2 leading-relaxed">
                            <span class="icon-[tabler--bulb] size-5 text-[#E8B84A] shrink-0 mt-0.5"></span>
                            تتم معالجة المدفوعات آليا يوم 15 من كل شهر بشرط وصول الرصيد للحد الأدنى ( 500 ريال).
                        </p>
                    </div>
                    <button type="button"
                        class="btn bg-[#8E8F8F] hover:bg-[#7a7b7b] text-white border-0 rounded-10px h-12 px-8 font-bold text-16px shrink-0">
                        طلب سحب الارباح
                    </button>
                </div>

                <h3 class="font-bold text-20px text-[#0F172A] mb-6">سجل عمليات السحب الأخيرة</h3>
                <div class="border border-d9 rounded-12px bg-white px-8 py-20 center flex-col text-center">
                    <div class="size-16 rounded-full bg-[#E8EEF5] center mb-6">
                        <span class="icon-[tabler--plus] size-8 text-[#7A8FA8]"></span>
                    </div>
                    <p class="font-semibold text-24px text-gray mb-3">لا توجد عمليات سحب سابقة</p>
                    <p class="font-normal text-22px text-gray max-w-xl leading-relaxed">
                        ستظهر جميع تفاصيل السحوبات والتحويلات البنكية هنا مباشرة.
                    </p>
                </div>
            </div>

            <div id="purchase-tabs-4" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-4">
                <div
                    class="bg-fa rounded-12px px-8 py-6 mb-12 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div>
                        <h3 class="font-semibold text-24px text-black mb-2">لا توجد خطة اشتراك!</h3>
                        <p class="font-semibold text-20px text-gray">
                            فعل خطة اشتراك من القائمة أدناه للوصول إلى المزيد من الميزات.
                        </p>
                    </div>
                    <button type="button"
                        class="btn btn-primary rounded-10px h-12 px-8 font-semibold text-20px shrink-0">
                        اختر خطة دفع
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Bronze --}}
                    <article class="border-2 border-primary rounded-13px bg-white px-8 py-10 flex flex-col">
                        <div class=" flex items-center  gap-3 mb-8">
                            <svg width="31" height="34" viewBox="0 0 31 34" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M30.3243 24.4279C30.3243 25.0681 29.9705 25.6241 29.4314 25.9104L16.1224 33.3904C15.8529 33.5926 15.516 33.6937 15.1622 33.6937C14.8084 33.6937 14.4715 33.5926 14.2019 33.3904L0.892892 25.9104C0.622682 25.7687 0.396548 25.5555 0.239113 25.2941C0.0816778 25.0327 -0.00102475 24.7331 9.58439e-06 24.4279V9.26577C9.58439e-06 8.62559 0.353793 8.06964 0.892892 7.78324L14.2019 0.303243C14.4715 0.101081 14.8084 0 15.1622 0C15.516 0 15.8529 0.101081 16.1224 0.303243L29.4314 7.78324C29.9705 8.06964 30.3243 8.62559 30.3243 9.26577V24.4279ZM15.1622 3.62207L5.12145 9.26577L15.1622 14.9095L25.2029 9.26577L15.1622 3.62207ZM3.36938 23.434L13.4775 29.1282V17.824L3.36938 12.1466V23.434ZM26.955 23.434V12.1466L16.8469 17.824V29.1282L26.955 23.434Z"
                                    fill="#8B572A" />
                            </svg>
                            <h4 class="font-bold text-28px text-[#8B572A]">الباقة البرونزية</h4>
                        </div>
                        <p class="font-normal text-20px text-colorTextPrimary mb-4"><span class="font-bold">المدة</span>
                            : صالحة لمدة 6 أشهر</p>
                        <p class="font-bold text-18px text-black mb-3">يشمل:</p>
                        <ul class="font-normal text-20px text-colorTextPrimary space-y-2 mb-10 grow list-none">
                            <li>- 30 يوم تحضير</li>
                            <li>- 25 ساعة فيديوهات</li>
                            <li>- 5 اختبارات</li>
                        </ul>
                        <div class="flex items-center gap-3 mb-8">
                            <span class="font-bold text-32px text-primary">690 ريال</span>
                            <span class="font-medium text-20px text-[#B3B3B3] line-through">950 ريال</span>
                        </div>
                        <button type="button" class="btn btn-primary rounded-10px h-13 font-semibold text-20px w-full">
                            اشترك الآن
                        </button>
                    </article>

                    {{-- Silver --}}
                     <article class="border-2 border-primary rounded-13px bg-white px-8 py-10 flex flex-col">
                        <div class=" flex items-center  gap-3 mb-8">
                        <svg width="42" height="38" viewBox="0 0 42 38" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M31.9697 0H9.95605L0 13.2742L20.9629 37.4629L41.9258 13.2742L31.9697 0ZM37.1062 11.7406H29.1138L23.6104 2.93516H30.5021L37.1062 11.7406ZM12.6006 14.6758L18.1099 29.6891L5.09837 14.6758H12.6006ZM15.7266 14.6758H26.1992L20.9614 28.9421L15.7266 14.6758ZM16.274 11.7406L20.9629 4.2369L25.6533 11.7406H16.274ZM29.3251 14.6758H36.8274L23.8144 29.6906L29.3251 14.6758ZM11.4236 2.93516H18.3154L12.812 11.7406H4.81953L11.4236 2.93516Z" fill="#3A5161"/>
</svg>

                            <h4 class="font-bold text-28px text-[#3A5161]">الباقة الفضية</h4>
                        </div>
                        <p class="font-normal text-20px text-colorTextPrimary mb-4"><span class="font-bold">المدة</span>
                            : صالحة لمدة 6 أشهر</p>
                        <p class="font-bold text-18px text-black mb-3">يشمل:</p>
                        <ul class="font-normal text-20px text-colorTextPrimary space-y-2 mb-10 grow list-none">
                            <li>- 30 يوم تحضير</li>
                            <li>25 ساعة من الفيديوهات التعليمية المسجلة</li>
                            <li>اختبار قصير بعد كل درس</li>
                            <li>ملخصات الباوربوينت لكل درس</li>
                            <li>اختبار نهائي شامل – 240 سؤال</li>
                        </ul>
                        <div class="flex items-center gap-3 mb-8">
                            <span class="font-bold text-32px text-primary">690 ريال</span>
                            <span class="font-medium text-20px text-[#B3B3B3] line-through">950 ريال</span>
                        </div>
                        <button type="button" class="btn btn-primary rounded-10px h-13 font-semibold text-20px w-full">
                            اشترك الآن
                        </button>
                    </article>

                    {{-- Gold --}}
                      <article class="border-2 border-primary rounded-13px bg-white px-8 py-10 flex flex-col">
                        <div class=" flex items-center  gap-3 mb-8">
     <svg width="36" height="32" viewBox="0 0 36 32" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.3125 31.875V28.3333H30.1042V31.875H5.3125ZM5.3125 25.6771L3.05469 11.4662C2.99566 11.4662 2.92896 11.4738 2.85459 11.4892C2.78021 11.5045 2.7141 11.5116 2.65625 11.5104C1.91841 11.5104 1.29153 11.2519 0.775629 10.7348C0.259726 10.2177 0.00118458 9.59084 4.02462e-06 8.85417C-0.00117653 8.1175 0.257365 7.49063 0.775629 6.97355C1.29389 6.45646 1.92077 6.19792 2.65625 6.19792C3.39174 6.19792 4.01921 6.45646 4.53865 6.97355C5.05809 7.49063 5.31605 8.1175 5.3125 8.85417C5.3125 9.06077 5.29007 9.25261 5.24521 9.42969C5.20035 9.60678 5.149 9.7691 5.09115 9.91667L10.625 12.3958L16.1589 4.82552C15.8342 4.58941 15.5686 4.27952 15.362 3.89584C15.1554 3.51216 15.0521 3.09896 15.0521 2.65625C15.0521 1.91841 15.3106 1.29094 15.8277 0.773858C16.3448 0.256775 16.9717 -0.00117652 17.7083 4.03379e-06C18.445 0.00118459 19.0725 0.259726 19.5907 0.775629C20.109 1.29153 20.3669 1.91841 20.3646 2.65625C20.3646 3.09896 20.2613 3.51216 20.0547 3.89584C19.8481 4.27952 19.5825 4.58941 19.2578 4.82552L24.7917 12.3958L30.3255 9.91667C30.2665 9.7691 30.2146 9.60678 30.1697 9.42969C30.1248 9.25261 30.103 9.06077 30.1042 8.85417C30.1042 8.11632 30.3627 7.48886 30.8798 6.97178C31.3969 6.45469 32.0238 6.19674 32.7604 6.19792C33.4971 6.1991 34.1246 6.45764 34.6428 6.97355C35.1611 7.48945 35.419 8.11632 35.4167 8.85417C35.4143 9.59202 35.1564 10.2195 34.6428 10.7366C34.1293 11.2536 33.5018 11.5116 32.7604 11.5104C32.7014 11.5104 32.6353 11.5033 32.5621 11.4892C32.4889 11.475 32.4222 11.4673 32.362 11.4662L30.1042 25.6771H5.3125ZM8.32292 22.1354H27.0938L28.2448 14.7422L23.5964 16.7787L17.7083 8.67709L11.8203 16.7787L7.17188 14.7422L8.32292 22.1354Z" fill="#F5A623"/>
</svg>


                            <h4 class="font-bold text-28px text-[#F5A623]">الباقة الذهبية</h4>
                        </div>
                        <p class="font-normal text-20px text-colorTextPrimary mb-4"><span class="font-bold">المدة</span>
                            : صالحة لمدة 6 أشهر</p>
                        <p class="font-bold text-18px text-black mb-3">يشمل:</p>
                        <ul class="font-normal text-20px text-colorTextPrimary space-y-2 mb-10 grow list-none">
                            <li>- 30 يوم تحضير</li>
                            <li>25 ساعة من الفيديوهات التعليمية المسجلة</li>
                            <li>اختبار قصير بعد كل درس</li>
                            <li>ملخصات الباوربوينت لكل درس</li>
                            <li>اختبار نهائي شامل – 240 سؤال</li>
                        </ul>
                        <div class="flex items-center gap-3 mb-8">
                            <span class="font-bold text-32px text-primary">690 ريال</span>
                            <span class="font-medium text-20px text-[#B3B3B3] line-through">950 ريال</span>
                        </div>
                        <button type="button" class="btn btn-primary rounded-10px h-13 font-semibold text-20px w-full">
                            اشترك الآن
                        </button>
                    </article>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
