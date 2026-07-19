@extends('landing_v1.layouts.app')

@section('content')
@php
    $landingImg      = asset('assets/landing_v1/img');
    $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));
    $userCurrency    = currency();
@endphp

<main class="min-h-screen bg-gray-50 pt-32 pb-20">
    <div class="container max-w-6xl mx-auto px-4">

        {{-- Page Header --}}
        <div class="mb-10 text-center">
            <h1 class="font-bold text-40px text-primary mb-1">إتمام الدفع</h1>
            <p class="font-medium text-16px text-primary/50">
                {{ $count }} {{ $count == 1 ? 'دورة' : 'دورات' }} — {{ handlePrice($calculatePrices['total']) }}
            </p>
        </div>

        <form action="/payments/payment-request" method="post" enctype="multipart/form-data" id="checkout-form">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="discount_id" id="checkout-discount-id" value="{{ !empty($discountCoupon) ? $discountCoupon->id : '' }}">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- ── Left: Payment Gateways ── --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- Gateway selector --}}
                    <div class="bg-white rounded-12px border border-gray-100 shadow-sm p-6">
                        <h2 class="font-bold text-20px text-primary mb-6 pb-4 border-b border-gray-100 flex items-center gap-2">
                            <span class="icon-[tabler--credit-card] size-5 text-gold"></span>
                            اختر طريقة الدفع
                        </h2>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="gateway-grid">

                            {{-- Payment channels --}}
                            @forelse($paymentChannels as $channel)
                            <label for="gateway_{{ $channel->id }}"
                                class="gateway-card-label relative flex flex-col items-center justify-center gap-3 p-5 rounded-10px border-2 border-gray-200 bg-white cursor-pointer transition-all duration-200 hover:border-primary hover:shadow-md has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:shadow-md">
                                <input type="radio" name="gateway" id="gateway_{{ $channel->id }}"
                                    value="{{ $channel->id }}"
                                    data-class="{{ $channel->class_name }}"
                                    class="sr-only gateway-radio" required>
                                {{-- Selected indicator --}}
                                <span class="gateway-check absolute top-2 end-2 size-5 rounded-full bg-primary text-white flex items-center justify-center opacity-0 transition-opacity duration-200">
                                    <span class="icon-[tabler--check] size-3"></span>
                                </span>
                                <div class="h-12 flex items-center justify-center">
                                    <img src="{{ $channel->image }}" alt="{{ $channel->title }}"
                                        class="max-h-12 max-w-[100px] object-contain">
                                </div>
                                <span class="font-semibold text-14px text-primary text-center">{{ $channel->title }}</span>
                            </label>
                            @empty
                            <div class="col-span-full text-center py-10 text-primary/40">
                                <span class="icon-[tabler--alert-circle] size-10 mb-2 block mx-auto"></span>
                                <p class="font-medium text-14px">لا توجد بوابات دفع متاحة حالياً</p>
                            </div>
                            @endforelse

                            {{-- Account charge (wallet) --}}
                            @php $userChargeSufficient = !empty($userCharge) && $userCharge >= $calculatePrices['total']; @endphp
                            <label for="gateway_credit"
                                class="gateway-card-label relative flex flex-col items-center justify-center gap-3 p-5 rounded-10px border-2 cursor-pointer transition-all duration-200
                                    {{ $userChargeSufficient ? 'border-gray-200 bg-white hover:border-primary hover:shadow-md has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:shadow-md' : 'border-gray-100 bg-gray-50 opacity-50 cursor-not-allowed' }}">
                                <input type="radio" name="gateway" id="gateway_credit" value="credit"
                                    class="sr-only gateway-radio"
                                    {{ $userChargeSufficient ? '' : 'disabled' }}>
                                <span class="gateway-check absolute top-2 end-2 size-5 rounded-full bg-primary text-white flex items-center justify-center opacity-0 transition-opacity duration-200">
                                    <span class="icon-[tabler--check] size-3"></span>
                                </span>
                                <div class="h-12 flex items-center justify-center">
                                    <span class="icon-[tabler--wallet] size-10 text-primary/40"></span>
                                </div>
                                <span class="font-semibold text-14px text-primary text-center">رصيد الحساب</span>
                                <span class="text-12px font-medium text-primary/50">{{ handlePrice($userCharge ?? 0) }}</span>
                            </label>

                            {{-- Offline bank transfer --}}
                            @if(!empty(getOfflineBankSettings('offline_banks_status')))
                            <label for="gateway_offline"
                                class="gateway-card-label relative flex flex-col items-center justify-center gap-3 p-5 rounded-10px border-2 border-gray-200 bg-white cursor-pointer transition-all duration-200 hover:border-primary hover:shadow-md has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:shadow-md">
                                <input type="radio" name="gateway" id="gateway_offline" value="offline"
                                    class="sr-only gateway-radio">
                                <span class="gateway-check absolute top-2 end-2 size-5 rounded-full bg-primary text-white flex items-center justify-center opacity-0 transition-opacity duration-200">
                                    <span class="icon-[tabler--check] size-3"></span>
                                </span>
                                <div class="h-12 flex items-center justify-center">
                                    <span class="icon-[tabler--building-bank] size-10 text-primary/40"></span>
                                </div>
                                <span class="font-semibold text-14px text-primary text-center">تحويل بنكي</span>
                            </label>
                            @endif

                        </div>

                        {{-- Validation error --}}
                        <p id="gateway-error" class="hidden mt-4 text-13px font-medium text-red-500 flex items-center gap-1">
                            <span class="icon-[tabler--alert-circle] size-4"></span>
                            يرجى اختيار طريقة الدفع أولاً
                        </p>
                    </div>

                    {{-- Offline bank fields (shown when offline is selected) --}}
                    @if(!empty(getOfflineBankSettings('offline_banks_status')))
                    <div id="offline-fields" class="hidden bg-white rounded-12px border border-gray-100 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-18px text-primary mb-2 flex items-center gap-2">
                            <span class="icon-[tabler--building-bank] size-5 text-gold"></span>
                            بيانات التحويل البنكي
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label label-text font-medium text-16px text-primary mb-2">الحساب البنكي</label>
                                <select name="account" class="select bg-f7 h-12 rounded-7px w-full text-primary font-medium">
                                    <option selected disabled>اختر الحساب</option>
                                    @foreach($offlineBanks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label label-text font-medium text-16px text-primary mb-2">رقم المرجع</label>
                                <input type="text" name="referral_code" placeholder="رقم المرجع / الإيصال"
                                    class="input bg-f7 h-12 rounded-7px w-full text-primary">
                            </div>
                            <div>
                                <label class="label label-text font-medium text-16px text-primary mb-2">تاريخ التحويل</label>
                                <input type="text" name="date" placeholder="YYYY/MM/DD HH:mm"
                                    class="input bg-f7 h-12 rounded-7px w-full text-primary">
                            </div>
                            <div>
                                <label class="label label-text font-medium text-16px text-primary mb-2">إيصال الدفع (صورة)</label>
                                <input type="file" name="attachment" accept="image/*"
                                    class="file-input bg-f7 h-12 rounded-7px w-full text-primary">
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Disabled gateways notice --}}
                    @if(!empty($invalidChannels) && $invalidChannels->count() > 0 && empty(getFinancialSettings('hide_disabled_payment_gateways')))
                    <div class="bg-white rounded-12px border border-gray-100 shadow-sm p-6">
                        <div class="flex items-start gap-3 p-4 rounded-8px bg-amber-50 border border-amber-200 mb-4">
                            <span class="icon-[tabler--info-circle] size-5 text-amber-500 shrink-0 mt-0.5"></span>
                            <p class="font-medium text-14px text-amber-700">بعض بوابات الدفع غير متاحة لعملتك الحالية</p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($invalidChannels as $ch)
                            <div class="flex items-center gap-3 p-3 rounded-8px border border-gray-100 bg-gray-50 opacity-60">
                                <img src="{{ $ch->image }}" alt="{{ $ch->title }}" class="h-8 object-contain">
                                <span class="font-medium text-13px text-primary/60">{{ $ch->title }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ── Right: Order Summary ── --}}
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-12px border border-gray-100 shadow-sm p-6 sticky top-28">

                        <h2 class="font-bold text-20px text-primary mb-6 pb-4 border-b border-gray-100">ملخص الطلب</h2>

                        {{-- Items list --}}
                        <div class="space-y-3 mb-5">
                            @foreach($carts as $cart)
                                @php
                                    $itemTitle = '';
                                    $itemImage = '';
                                    if (!empty($cart->webinar_id) && !empty($cart->webinar)) {
                                        $itemTitle = $cart->webinar->title;
                                        $itemImage = !empty($cart->webinar->image_cover)
                                            ? asset($cart->webinar->image_cover)
                                            : asset('assets/landing_v1/img/contact/hero.webp');
                                    } elseif (!empty($cart->bundle_id) && !empty($cart->bundle)) {
                                        $itemTitle = $cart->bundle->title;
                                        $itemImage = asset('assets/landing_v1/img/contact/hero.webp');
                                    } elseif (!empty($cart->productOrder) && !empty($cart->productOrder->product)) {
                                        $itemTitle = $cart->productOrder->product->title;
                                        $itemImage = !empty($cart->productOrder->product->image)
                                            ? asset($cart->productOrder->product->image)
                                            : asset('assets/landing_v1/img/contact/hero.webp');
                                    }
                                @endphp
                                @if($itemTitle)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-6px overflow-hidden shrink-0 bg-gray-100">
                                        <img src="{{ $itemImage }}" alt="{{ $itemTitle }}" class="w-full h-full object-cover">
                                    </div>
                                    <p class="font-medium text-13px text-primary flex-grow truncate">{{ $itemTitle }}</p>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-3 mb-5">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-14px text-primary/70">الإجمالي الفرعي</span>
                                <span class="font-bold text-14px text-primary" id="checkout-subtotal">{{ handlePrice($calculatePrices['sub_total']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-14px text-primary/70">الخصم</span>
                                @if($calculatePrices['total_discount'] > 0)
                                    <span class="font-bold text-14px text-green-600" id="checkout-discount">- {{ handlePrice($calculatePrices['total_discount']) }}</span>
                                @else
                                    <span class="font-bold text-14px text-primary/40" id="checkout-discount">{{ handlePrice(0) }}</span>
                                @endif
                            </div>
                            @if($calculatePrices['tax_price'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-14px text-primary/70">الضريبة ({{ $calculatePrices['tax'] }}%)</span>
                                <span class="font-bold text-14px text-primary" id="checkout-tax">{{ handlePrice($calculatePrices['tax_price']) }}</span>
                            </div>
                            @endif
                            @if(!empty($calculatePrices['product_delivery_fee']) && $calculatePrices['product_delivery_fee'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-14px text-primary/70">رسوم الشحن</span>
                                <span class="font-bold text-14px text-primary">{{ handlePrice($calculatePrices['product_delivery_fee']) }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Coupon --}}
                        <div class="mb-6">
                            <p class="font-bold text-14px text-primary mb-3">
                                <span class="icon-[tabler--ticket] size-4 inline-block me-1"></span>
                                هل لديك كوبون؟
                            </p>

                            @if(!empty($discountCoupon))
                                <div class="flex items-center justify-between gap-3 p-3 rounded-8px bg-green-50 border border-green-200 mb-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-13px text-green-700 truncate">{{ $discountCoupon->code }}</p>
                                        <p class="font-medium text-12px text-green-600">
                                            تم تطبيق الخصم
                                            @if(!empty($discountCoupon->percent))
                                                ({{ $discountCoupon->percent }}%)
                                            @endif
                                        </p>
                                    </div>
                                    <button type="button" id="remove-coupon-btn"
                                        class="btn btn-ghost btn-sm text-red-500 hover:bg-red-50 shrink-0"
                                        onclick="removeCoupon()">
                                        إزالة
                                    </button>
                                </div>
                            @else
                                <div class="flex gap-2" id="coupon-form-row">
                                    <input type="text" id="coupon-code-input"
                                        class="input input-bordered flex-1 text-14px font-medium text-primary h-10 rounded-8px focus:border-secondary"
                                        placeholder="أدخل رمز الخصم هنا"
                                        autocomplete="off">
                                    <button type="button" id="apply-coupon-btn"
                                        class="btn btn-outline btn-primary h-10 px-4 text-14px font-medium rounded-8px"
                                        onclick="applyCoupon()">
                                        تطبيق
                                    </button>
                                </div>
                            @endif

                            <div id="coupon-result" class="mt-2 text-13px font-medium hidden"></div>
                        </div>

                        {{-- Total --}}
                        <div class="flex justify-between items-center py-4 border-t border-b border-gray-100 mb-6">
                            <span class="font-bold text-18px text-primary">المجموع</span>
                            <span class="font-bold text-24px text-primary" id="checkout-total">{{ handlePrice($calculatePrices['total']) }}</span>
                        </div>

                        {{-- Pay button --}}
                        <button type="submit" id="pay-now-btn"
                            class="btn btn-gold btn-block h-14 rounded-8px font-bold text-18px shadow-md hover:shadow-lg transition-all duration-300">
                            <span class="icon-[tabler--lock] size-5 me-2"></span>
                            ادفع الآن
                        </button>

                        {{-- Secure badge --}}
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
        </form>

    </div>
</main>

{{-- Razorpay auto-submit --}}
@if(!empty($razorpay) && $razorpay)
<form action="/payments/verify/Razorpay" method="get" id="razorpay-form">
    <input type="hidden" name="order_id" value="{{ $order->id }}">
    <script src="https://checkout.razorpay.com/v1/checkout.js"
        data-key="{{ getRazorpayApiKey()['api_key'] }}"
        data-amount="{{ (int)($order->total_amount * 100) }}"
        data-buttontext=""
        data-description="Payment"
        data-currency="{{ currency() }}"
        data-prefill.name="{{ auth()->user()->full_name }}"
        data-prefill.email="{{ auth()->user()->email }}"
        data-theme.color="#B9A685">
    </script>
</form>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Visual selection feedback ──────────────────────────────
    document.querySelectorAll('.gateway-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            // Reset all cards
            document.querySelectorAll('.gateway-card-label').forEach(function (lbl) {
                lbl.querySelector('.gateway-check').style.opacity = '0';
            });
            // Highlight selected
            if (this.checked) {
                const lbl = this.closest('.gateway-card-label');
                if (lbl) lbl.querySelector('.gateway-check').style.opacity = '1';
            }

            // Toggle offline fields
            const offlineFields = document.getElementById('offline-fields');
            if (offlineFields) {
                offlineFields.classList.toggle('hidden', this.value !== 'offline');
            }

            // Hide error
            document.getElementById('gateway-error').classList.add('hidden');
        });
    });

    // ── Form submit validation ─────────────────────────────────
    document.getElementById('checkout-form').addEventListener('submit', function (e) {
        const selected = document.querySelector('.gateway-radio:checked');
        if (!selected) {
            e.preventDefault();
            document.getElementById('gateway-error').classList.remove('hidden');
            document.getElementById('gateway-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Show loading state on button
        const btn = document.getElementById('pay-now-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <span class="icon-[tabler--loader-2] size-5 me-2 animate-spin inline-block"></span>
                جاري التحويل...
            `;
        }
    });

});

/**
 * Validate coupon via backend, then recreate checkout order with discount_id
 * so totals are recalculated with handlePrice on the server.
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
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ coupon }),
        });

        const data = await response.json();

        if (response.ok && data.code === 200) {
            // Extract discount_id from the backend summary HTML (uses handlePrice)
            let discountId = '';
            if (data.html) {
                const doc = new DOMParser().parseFromString(data.html, 'text/html');
                discountId = doc.querySelector('input[name="discount_id"]')?.value || '';
            }

            if (!discountId) {
                result.className = 'mt-2 text-13px font-medium text-red-500';
                result.textContent = 'تعذر تطبيق الكوبون، يرجى المحاولة مجدداً';
                result.classList.remove('hidden');
                return;
            }

            result.className = 'mt-2 text-13px font-medium text-green-600';
            result.innerHTML = '<span class="icon-[tabler--circle-check] size-4 inline-block me-1"></span>تم تطبيق الكوبون — جاري تحديث الطلب...';
            result.classList.remove('hidden');

            // Reload checkout so order is recreated with the coupon (payment uses order totals)
            reloadCheckoutWithDiscount(discountId);
            return;
        }

        const err = data.error || {};
        result.className = 'mt-2 text-13px font-medium text-red-500';
        result.textContent = err.msg || 'الكوبون غير صالح';
        result.classList.remove('hidden');
    } catch (_) {
        result.className = 'mt-2 text-13px font-medium text-red-500';
        result.textContent = 'خطأ في الاتصال بالخادم';
        result.classList.remove('hidden');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'تطبيق';
        }
    }
}

function removeCoupon() {
    reloadCheckoutWithDiscount('');
}

function reloadCheckoutWithDiscount(discountId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('landing.v1.checkout') }}';
    form.style.display = 'none';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    form.appendChild(csrf);

    if (discountId) {
        const discountInput = document.createElement('input');
        discountInput.type = 'hidden';
        discountInput.name = 'discount_id';
        discountInput.value = discountId;
        form.appendChild(discountInput);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
