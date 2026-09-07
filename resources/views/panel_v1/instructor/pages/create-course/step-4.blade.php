@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
@endphp

{{-- Pricing model --}}
<section class="{{ $card }}" data-pricing-model>
    <div class="mb-5 sm:mb-6 text-start">
        <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">نموذج التسعير</h2>
        <p class="font-medium text-14px text-gray">حدد ما إذا كانت الدورة مجانية أم مدفوعة</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-6">
        <button type="button" data-price-type="free"
            class="text-start rounded-14px border border-d9 bg-white p-5 hover:border-primary/40 transition">
            <p class="font-bold text-17px text-primary mb-1">دورة مجانية</p>
            <p class="font-medium text-14px text-gray">متاحة لجميع الطلاب دون رسوم</p>
        </button>
        <button type="button" data-price-type="paid"
            class="text-start rounded-14px border border-primary bg-[#F7F0E6] p-5 transition">
            <p class="font-bold text-17px text-primary mb-1">دورة مدفوعة</p>
            <p class="font-medium text-14px text-gray">حدد السعر والعملة وخطط الدفع</p>
        </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-6" data-paid-fields>
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">السعر الأساسي</label>
            <input type="number" value="{{ $suggestedPrice ?? '299' }}" class="{{ $input }}">
        </div>
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">العملة</label>
            <input type="text" value="{{ $currency ?? 'SAR' }}" class="{{ $input }}">
        </div>
    </div>
    <div>
        <p class="font-semibold text-15px sm:text-16px text-primary mb-4 text-start">مدة الوصول للمحتوى</p>
        <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="access_duration" value="lifetime" class="radio radio-primary" checked>
                <span class="font-medium text-15px text-primary">وصول مدى الحياة</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="access_duration" value="limited" class="radio radio-primary">
                <span class="font-medium text-15px text-primary">مدة وصول محدودة</span>
            </label>
        </div>
    </div>
</section>

{{-- Discounts --}}
<section class="{{ $card }}">
    <div class="mb-5 sm:mb-6 text-start">
        <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">الخصومات وخطط الدفع</h2>
        <p class="font-medium text-14px text-gray">عروض ترويجية وتقسيط واشتراكات</p>
    </div>
    <div class="divide-y divide-d9">
        <div class="flex items-center justify-between gap-4 py-4 first:pt-0">
            <div class="min-w-0 text-start">
                <p class="font-semibold text-15px sm:text-16px text-primary mb-1">تفعيل سعر الخصم</p>
                <p class="font-medium text-13px sm:text-14px text-gray">عرض سعر مخفض لفترة محدودة</p>
            </div>
            <input type="checkbox" class="switch switch-primary shrink-0" aria-label="تفعيل سعر الخصم">
        </div>
        <div class="flex items-center justify-between gap-4 py-4 last:pb-0">
            <div class="min-w-0 text-start">
                <p class="font-semibold text-15px sm:text-16px text-primary mb-1">خطط الاشتراك والتقسيط</p>
                <p class="font-medium text-13px sm:text-14px text-gray">السماح بالدفع على دفعات شهرية</p>
            </div>
            <input type="checkbox" class="switch switch-primary shrink-0" aria-label="خطط الاشتراك والتقسيط">
        </div>
    </div>
</section>

{{-- Capacity --}}
<section class="{{ $card }}">
    <div class="mb-5 text-start">
        <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">السعة والاستضافة</h2>
        <p class="font-medium text-14px text-gray">حدود عدد الطلاب</p>
    </div>
    <div>
        <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">حدد عدد الطلاب</label>
        <input type="number" class="{{ $input }}" placeholder="اتركه فارغًا لسعة غير محدودة">
    </div>
</section>
