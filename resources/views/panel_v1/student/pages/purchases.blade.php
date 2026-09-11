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
                id="purchase-tabs-item-1" data-v1-tab="#purchase-tabs-1" aria-controls="purchase-tabs-1" role="tab"
                aria-selected="true">
                تفاصيل الشراء
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="purchase-tabs-item-2" data-v1-tab="#purchase-tabs-2" aria-controls="purchase-tabs-2" role="tab"
                aria-selected="false">
                المحفظة
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="purchase-tabs-item-3" data-v1-tab="#purchase-tabs-3" aria-controls="purchase-tabs-3" role="tab"
                aria-selected="false">
                الأرباح والعمولات
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="purchase-tabs-item-4" data-v1-tab="#purchase-tabs-4" aria-controls="purchase-tabs-4" role="tab"
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
                    @forelse ($sales ?? [] as $sale)
                        @php
                            $saleWebinar = $sale->webinar ?? null;
                            $saleTitle = $saleWebinar->title ?? ($sale->type === 'meeting' ? 'جلسة استشارية' : 'عملية شراء #' . $sale->id);
                            $saleCategory = $saleWebinar->category->title ?? '';
                        @endphp
                        <article
                            class="grid grid-cols-1 gap-y-4 md:grid-cols-[minmax(0,2.4fr)_minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,0.9fr)] md:gap-x-6 md:items-center border border-d9 rounded-16px bg-white px-6 py-5"
                            role="row">
                            <div class="flex items-center gap-4 min-w-0" role="cell">
                                <div class="size-14 rounded-12px bg-primary shrink-0 overflow-hidden center">
                                    @if (!empty($saleWebinar->thumbnail))
                                        <img src="{{ $saleWebinar->thumbnail }}" alt="" class="size-full object-cover"
                                            loading="lazy" decoding="async">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-24px text-black mb-1 leading-snug">
                                        {{ $saleTitle }}
                                    </h2>
                                    @if (!empty($saleCategory))
                                        <p class="font-medium text-16px text-gray">{{ $saleCategory }}</p>
                                    @endif
                                </div>
                            </div>

                            <p class="font-semibold text-24px text-black md:text-center" role="cell">
                                {{ handlePrice($sale->total_amount) }}
                            </p>

                            <div class="md:text-center" role="cell">
                                <p class="font-semibold text-20px text-[#AAAAAA] mb-2">
                                    {{ date('j F Y', (int) $sale->created_at) }}</p>
                                @if (!empty($saleWebinar))
                                    <a href="{{ url('/panel/courses/' . $saleWebinar->id . '/sale/' . $sale->id . '/invoice') }}"
                                        class="inline-flex items-center justify-center gap-2 font-bold text-20px text-primary hover:opacity-80 transition">
                                        <span class="icon-[tabler--file-invoice] size-5"></span>
                                        تحميل الفاتورة
                                    </a>
                                @endif
                            </div>

                            <p class="font-semibold text-24px text-[#00B31B] md:text-center" role="cell">
                                {{ empty($sale->refund_at) ? 'مدفوع' : 'مسترد' }}
                            </p>
                        </article>
                    @empty
                        <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                            <p class="font-semibold text-24px text-gray">لا توجد عمليات شراء بعد</p>
                            <p class="font-medium text-16px text-gray mt-3">تصفح الدورات وابدأ رحلة التعلم.</p>
                            <a href="{{ route('landing.v1.courses') }}"
                                class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px mt-6">تصفح الدورات</a>
                        </div>
                    @endforelse

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
                                class="font-semibold text-48px text-white leading-none tracking-tight">{{ handlePrice($balance ?? 0) }}</span>

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
                            @forelse ($transactions ?? [] as $transaction)
                                <div class="flex items-center justify-between gap-4 py-5 first:pt-0 last:pb-0">
                                    <div>
                                        <p class="font-semibold text-18px text-colorsub mb-1">
                                            {{ $transaction->type === 'addiction' ? 'إيداع' : 'خصم' }}{{ !empty($transaction->description) ? ' — ' . \Illuminate\Support\Str::limit($transaction->description, 60) : '' }}
                                        </p>
                                        <p class="font-medium text-12px text-colorborder">
                                            {{ date('Y/m/d', (int) $transaction->created_at) }}</p>
                                    </div>
                                    <p class="font-bold text-16px text-[#00331E] shrink-0">
                                        {{ $transaction->type === 'addiction' ? '+' : '−' }}
                                        {{ handlePrice($transaction->amount) }}</p>
                                </div>
                            @empty
                                <p class="font-medium text-16px text-gray py-5">لا توجد تعاملات بعد.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div id="purchase-tabs-3" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-3">
                <div class="bg-[#F9F5F5] rounded-12px px-8 py-6 mb-10">
                    <h3 class="font-semibold text-24px text-primary mb-2">رصيدك وعمليات السحب</h3>
                    <p class="font-semibold text-15px text-[#64748B]">
                        سحب الأرباح متاح لحسابات المدربين — رصيدك الحالي يظهر أدناه.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-primary rounded-16px px-8 py-8 text-white">
                        <p class="font-bold text-20px text-white mb-10">الرصيد الحالي</p>
                        <p class="font-semibold text-32px text-white leading-none">{{ handlePrice($balance ?? 0) }}</p>
                    </div>
                </div>

                <h3 class="font-bold text-20px text-[#0F172A] mb-6">سجل عمليات السحب الأخيرة</h3>
                @forelse ($userPayouts ?? [] as $payout)
                    <div class="border border-d9 rounded-12px bg-white px-8 py-5 mb-4 flex items-center justify-between gap-4">
                        <p class="font-semibold text-18px text-primary">{{ handlePrice($payout->amount) }}</p>
                        <p class="font-medium text-14px text-gray">{{ date('Y/m/d', (int) $payout->created_at) }} — {{ $payout->status }}</p>
                    </div>
                @empty
                    <div class="border border-d9 rounded-12px bg-white px-8 py-20 center flex-col text-center">
                        <p class="font-semibold text-24px text-gray mb-3">لا توجد عمليات سحب سابقة</p>
                    </div>
                @endforelse
            </div>

            <div id="purchase-tabs-4" class="hidden" role="tabpanel" aria-labelledby="purchase-tabs-item-4">
                @if (($userSubscribes ?? collect())->isNotEmpty())
                    <div class="bg-fa rounded-12px px-8 py-6 mb-12">
                        <h3 class="font-semibold text-24px text-black mb-4">اشتراكاتك النشطة</h3>
                        @foreach ($userSubscribes as $userSubscribe)
                            <p class="font-semibold text-18px text-primary mb-2">
                                {{ $userSubscribe->subscribe->title ?? '' }}
                                ({{ $userSubscribe->subscribe->days ?? 0 }} يوم)
                            </p>
                        @endforeach
                    </div>
                @else
                    <div class="bg-fa rounded-12px px-8 py-6 mb-12 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                        <div>
                            <h3 class="font-semibold text-24px text-black mb-2">لا توجد خطة اشتراك!</h3>
                            <p class="font-semibold text-20px text-gray">
                                فعل خطة اشتراك من القائمة أدناه للوصول إلى المزيد من الميزات.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @forelse ($subscribePlans ?? [] as $plan)
                        <article class="border-2 border-primary rounded-13px bg-white px-8 py-10 flex flex-col">
                            <h4 class="font-bold text-28px text-primary mb-4">{{ $plan->title }}</h4>
                            <p class="font-normal text-20px text-colorTextPrimary mb-4">
                                <span class="font-bold">المدة</span>: {{ $plan->days }} يوم
                            </p>
                            <p class="font-normal text-20px text-colorTextPrimary mb-10">
                                الاستخدامات: {{ $plan->infinite_use ? 'غير محدود' : $plan->usable_count }}
                            </p>
                            <div class="flex items-center gap-3 mb-8">
                                <span class="font-bold text-32px text-primary">{{ handlePrice($plan->price) }}</span>
                            </div>
                            <a href="{{ url('/panel/financial/subscribes') }}"
                                class="btn btn-primary rounded-10px h-13 font-semibold text-20px w-full center">
                                اشترك الآن
                            </a>
                        </article>
                    @empty
                        <div class="md:col-span-3 border border-d9 rounded-13px bg-white px-8 py-16 center flex-col text-center">
                            <p class="font-semibold text-22px text-gray">لا توجد خطط اشتراك متاحة حالياً</p>
                            <p class="font-medium text-16px text-gray mt-2">تُدار خطط الاشتراك من لوحة الإدارة.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
