@extends('landing_v1.layouts.app')

@section('content')
<main class="min-h-screen bg-gray-50 pt-32 pb-20" id="cart-page" data-cart-page>
    <div class="container max-w-6xl mx-auto px-4">

        {{-- Page Header --}}
        <div class="mb-10">
            <h1 class="font-bold text-40px text-primary mb-1">سلة التسوق</h1>
            <p class="font-medium text-16px text-primary/60">
                @if(!empty($carts) && $carts->isNotEmpty())
                    {{ $carts->count() }} {{ $carts->count() == 1 ? 'دورة' : 'دورات' }} في سلتك
                @else
                    سلتك فارغة
                @endif
            </p>
        </div>

        @if(!empty($carts) && $carts->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- ── Left Column: Cart Items ── --}}
            <div class="lg:col-span-8 space-y-4" id="cart-items-container">
                @foreach($carts as $cart)
                    @php
                        $item = null;
                        $itemTitle = '';
                        $itemImage = '';
                        $itemPrice = 0;
                        $itemOriginalPrice = 0;
                        $itemHasDiscount = false;
                        $cartItemId = $cart->getId();

                        if (!empty($cart->webinar_id) && !empty($cart->webinar)) {
                            $item = $cart->webinar;
                            $itemTitle = $item->title;
                            $itemImage = !empty($item->image_cover) ? asset($item->image_cover) : asset('assets/landing_v1/img/contact/hero.webp');
                            $itemOriginalPrice = $item->price;
                            // Get discount if user is logged in
                            $discount = auth()->check() ? $item->getDiscount($cart->ticket ?? null, auth()->user()) : 0;
                            $itemHasDiscount = $discount > 0;
                            $itemPrice = handlePrice($itemOriginalPrice - $discount);
                            $itemOriginalPriceFormatted = $itemHasDiscount ? handlePrice($itemOriginalPrice) : null;
                        } elseif (!empty($cart->product_order_id) && !empty($cart->productOrder) && !empty($cart->productOrder->product)) {
                            $item = $cart->productOrder->product;
                            $itemTitle = $item->title;
                            $itemImage = !empty($item->image) ? asset($item->image) : asset('assets/landing_v1/img/contact/hero.webp');
                            $itemOriginalPrice = $item->price * $cart->productOrder->quantity;
                            $discount = $item->getDiscountPrice() * $cart->productOrder->quantity;
                            $itemHasDiscount = $discount > 0;
                            $itemPrice = handlePrice($itemOriginalPrice - $discount);
                            $itemOriginalPriceFormatted = $itemHasDiscount ? handlePrice($itemOriginalPrice) : null;
                        }
                    @endphp

                    @if($item)
                    <div class="bg-white rounded-12px border border-gray-100 shadow-sm p-5 flex gap-5 items-center group hover:shadow-md transition-all duration-300"
                        id="cart-item-row-{{ $cartItemId }}" data-cart-item-id="{{ $cartItemId }}">

                        {{-- Image --}}
                        <div class="w-20 h-20 rounded-8px overflow-hidden shrink-0 bg-gray-100">
                            <img src="{{ $itemImage }}" alt="{{ $itemTitle }}"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </div>

                        {{-- Info --}}
                        <div class="flex-grow min-w-0">
                            <h3 class="font-bold text-16px text-primary mb-1 truncate">{{ $itemTitle }}</h3>
                            @if(!empty($item->teacher))
                            <p class="font-medium text-13px text-primary/50 mb-2">{{ $item->teacher->full_name }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1 bg-secondary/10 text-secondary text-12px font-bold px-3 py-1 rounded-full">
                                <span class="icon-[tabler--shopping-bag] size-3.5"></span>
                                دورة تدريبية
                            </span>
                        </div>

                        {{-- Price + Remove --}}
                        <div class="flex flex-col items-end gap-3 shrink-0">
                            <div class="flex flex-col items-end gap-0.5">
                                <span class="font-bold text-20px text-primary">{{ $itemPrice }}</span>
                                @if($itemHasDiscount)
                                    <span class="font-medium text-13px text-primary/40 line-through">{{ $itemOriginalPriceFormatted }}</span>
                                @endif
                            </div>
                            <button type="button"
                                class="cart-remove-btn flex items-center gap-1.5 text-12px font-medium text-red-400 hover:text-red-600 transition-colors"
                                data-cart-remove="{{ $cartItemId }}"
                                aria-label="حذف من السلة">
                                <span class="icon-[tabler--trash] size-4"></span>
                                حذف
                            </button>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            {{-- ── Right Column: Order Summary ── --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-12px border border-gray-100 shadow-sm p-6 sticky top-28" id="cart-summary-box">

                    <h2 class="font-bold text-20px text-primary mb-6 pb-4 border-b border-gray-100">ملخص الطلب</h2>

                    {{-- Prices --}}
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-14px text-primary/70">الإجمالي الفرعي</span>
                            <span class="font-bold text-15px text-primary">{{ handlePrice($calculatePrices['sub_total']) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-14px text-primary/70">الخصم</span>
                            @if($calculatePrices['total_discount'] > 0)
                                <span class="font-bold text-15px text-green-600">- {{ handlePrice($calculatePrices['total_discount']) }}</span>
                            @else
                                <span class="font-bold text-15px text-primary/40">{{ handlePrice(0) }}</span>
                            @endif
                        </div>
                        @if($calculatePrices['tax_price'] > 0)
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-14px text-primary/70">الضريبة ({{ $calculatePrices['tax'] }}%)</span>
                            <span class="font-bold text-15px text-primary">{{ handlePrice($calculatePrices['tax_price']) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Total --}}
                    <div class="flex justify-between items-center py-4 border-t border-b border-gray-100 mb-6">
                        <span class="font-bold text-18px text-primary">المجموع</span>
                        <span class="font-bold text-24px text-primary">{{ handlePrice($calculatePrices['total']) }}</span>
                    </div>

                    {{-- Coupon Code --}}
                    <div class="mb-6">
                        <p class="font-bold text-14px text-primary mb-3">
                            <span class="icon-[tabler--ticket] size-4 inline-block me-1"></span>
                            هل لديك كوبون؟
                        </p>
                        <div class="flex gap-2" id="coupon-form-row">
                            <input type="text" id="coupon-code-input"
                                class="input input-bordered flex-1 text-14px font-medium text-primary h-10 rounded-8px focus:border-secondary"
                                placeholder="أدخل رمز الخصم هنا">
                            <button type="button" id="apply-coupon-btn"
                                class="btn btn-outline btn-primary h-10 px-4 text-14px font-medium rounded-8px"
                                onclick="applyCoupon()">
                                تطبيق
                            </button>
                        </div>
                        <div id="coupon-result" class="mt-2 text-13px font-medium hidden"></div>
                    </div>

                    {{-- Hidden discount ID for checkout --}}
                    <input type="hidden" id="cart-discount-id" value="">

                    {{-- Checkout Button --}}
                    <form action="{{ route('landing.v1.checkout') }}" method="post">
                        @csrf
                        <input type="hidden" name="discount_id" id="checkout-discount-id" value="">
                        <button type="submit"
                            class="btn btn-primary btn-block h-14 rounded-8px font-bold text-18px shadow-md hover:shadow-lg transition-all duration-300">
                            <span class="icon-[tabler--lock] size-5 me-2"></span>
                            متابعة الدفع
                        </button>
                    </form>

                    {{-- Secure Badge --}}
                    <div class="flex items-center justify-center gap-2 mt-4 text-primary/40">
                        <span class="icon-[tabler--shield-check] size-4"></span>
                        <span class="font-medium text-12px">مدفوعات آمنة ومشفرة</span>
                    </div>

                    {{-- Payment logos --}}
                    <div class="flex items-center justify-center gap-3 mt-3 grayscale opacity-50">
                        <img src="{{ asset('assets/landing_v1/img/visa.png') }}" alt="Visa" class="h-5 object-contain" onerror="this.style.display='none'">
                        <img src="{{ asset('assets/landing_v1/img/mastercard.png') }}" alt="Mastercard" class="h-5 object-contain" onerror="this.style.display='none'">
                        <img src="{{ asset('assets/landing_v1/img/myfatoorah.png') }}" alt="MyFatoorah" class="h-5 object-contain" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>

        @else
        {{-- Empty Cart State --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-32 h-32 bg-primary/5 rounded-full flex items-center justify-center mb-8">
                <svg width="52" height="56" viewBox="0 0 25 29" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-primary/30">
                    <path d="M4.33074 8.58362H20.3943C20.8028 8.58357 21.2065 8.67186 21.5777 8.84244C21.949 9.01302 22.2789 9.26186 22.5449 9.57189C22.8109 9.88192 23.0068 10.2458 23.119 10.6386C23.2312 11.0314 23.2572 11.4438 23.1951 11.8476L21.4172 23.3963C21.2628 24.3999 20.7542 25.3151 19.9835 25.9762C19.2127 26.6373 18.2308 27.0006 17.2153 27.0003H7.50833C6.49314 27.0002 5.51151 26.6368 4.74105 25.9757C3.9706 25.3147 3.46222 24.3997 3.30791 23.3963L1.52999 11.8476C1.4679 11.4438 1.49385 11.0314 1.60607 10.6386C1.7183 10.2458 1.91414 9.88192 2.18016 9.57189C2.44619 9.26186 2.77612 9.01302 3.14732 8.84244C3.51852 8.67186 3.92222 8.58357 4.33074 8.58362Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.11328 12.8303V5.74695C8.11328 4.61978 8.56105 3.53877 9.35808 2.74174C10.1551 1.94471 11.2361 1.49695 12.3633 1.49695C13.4905 1.49695 14.5715 1.94471 15.3685 2.74174C16.1655 3.53877 16.6133 4.61978 16.6133 5.74695V12.8303" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2 class="font-bold text-28px text-primary mb-3">سلتك فارغة!</h2>
            <p class="font-medium text-16px text-primary/50 mb-8 max-w-sm">لم تضف أي دورات بعد. استكشف دوراتنا وابدأ رحلة التعلم.</p>
            <a href="{{ route('landing.v1.courses') }}"
                class="btn btn-primary h-13 px-10 rounded-8px font-bold text-17px shadow-md hover:shadow-lg transition-all duration-300">
                <span class="icon-[tabler--books] size-5 me-2"></span>
                استكشف الدورات
            </a>
        </div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
/**
 * Apply coupon code
 */
async function applyCoupon() {
    const input = document.getElementById('coupon-code-input');
    const result = document.getElementById('coupon-result');
    const btn = document.getElementById('apply-coupon-btn');
    const coupon = input ? input.value.trim() : '';

    if (!coupon) {
        result.className = 'mt-2 text-13px font-medium text-red-500';
        result.textContent = 'الرجاء إدخال رمز الكوبون';
        result.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="icon-[tabler--loader-2] size-4 animate-spin inline-block"></span>';

    try {
        const response = await fetch('/cart/coupon/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ coupon }),
        });

        const data = await response.json();

        if (response.ok && data.code === 200) {
            result.className = 'mt-2 text-13px font-medium text-green-600';
            result.innerHTML = '<span class="icon-[tabler--circle-check] size-4 inline-block me-1"></span>تم تطبيق الكوبون بنجاح!';
            result.classList.remove('hidden');

            // Update summary HTML if returned
            if (data.html) {
                const summaryBox = document.getElementById('cart-summary-box');
                if (summaryBox) {
                    // Inject updated prices from response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data.html, 'text/html');
                    // Try to extract discount ID
                    const discountId = doc.querySelector('[data-discount-id]')?.getAttribute('data-discount-id');
                    if (discountId) {
                        document.getElementById('checkout-discount-id').value = discountId;
                    }
                }
            }
        } else {
            const err = data.error || {};
            result.className = 'mt-2 text-13px font-medium text-red-500';
            result.textContent = err.msg || 'الكوبون غير صالح';
            result.classList.remove('hidden');
        }
    } catch (_) {
        result.className = 'mt-2 text-13px font-medium text-red-500';
        result.textContent = 'خطأ في الاتصال بالخادم';
        result.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'تطبيق';
    }
}
</script>
@endpush
