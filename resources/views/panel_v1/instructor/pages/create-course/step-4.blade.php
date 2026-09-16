@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $isPaid = old('price', $draftPrice ?? null) !== null && (int) old('price', $draftPrice ?? 0) > 0;
    $accessDays = old('access_days', $draftAccessDays ?? null);
    $accessDuration = old('access_duration', $accessDays ? 'limited' : 'lifetime');
@endphp

{{-- Pricing model --}}
<section class="{{ $card }}" data-pricing-model>
    <div class="mb-5 sm:mb-6 text-start">
        <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">نموذج التسعير</h2>
        <p class="font-medium text-14px text-gray">حدد ما إذا كانت الدورة مجانية أم مدفوعة</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-6">
        <button type="button" data-price-type="free"
            class="text-start rounded-14px border p-5 transition {{ $isPaid ? 'border-d9 bg-white hover:border-primary/40' : 'border-primary bg-[#F7F0E6]' }}">
            <p class="font-bold text-17px text-primary mb-1">دورة مجانية</p>
            <p class="font-medium text-14px text-gray">متاحة لجميع الطلاب دون رسوم</p>
        </button>
        <button type="button" data-price-type="paid"
            class="text-start rounded-14px border p-5 transition {{ $isPaid ? 'border-primary bg-[#F7F0E6]' : 'border-d9 bg-white hover:border-primary/40' }}">
            <p class="font-bold text-17px text-primary mb-1">دورة مدفوعة</p>
            <p class="font-medium text-14px text-gray">حدد السعر والعملة وخطط الدفع</p>
        </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-6 {{ $isPaid ? '' : 'hidden' }}" data-paid-fields>
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">السعر الأساسي</label>
            <input type="number" name="price" id="wizard-price-input"
                value="{{ old('price', $draftPrice ?? '') }}" min="0"
                class="{{ $input }} {{ $errors->has('price') ? 'border-[#FECACA]' : '' }}"
                data-field-label="السعر">
            @error('price')
                <p class="mt-2 font-medium text-13px text-[#B91C1C]">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">العملة</label>
            <input type="text" value="{{ $currency ?? 'SAR' }}" class="{{ $input }}" readonly>
        </div>
    </div>
    <div>
        <p class="font-semibold text-15px sm:text-16px text-primary mb-4 text-start">مدة الوصول للمحتوى</p>
        <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="access_duration" value="lifetime" class="radio radio-primary"
                    {{ $accessDuration === 'lifetime' ? 'checked' : '' }} data-access-duration>
                <span class="font-medium text-15px text-primary">وصول مدى الحياة</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="access_duration" value="limited" class="radio radio-primary"
                    {{ $accessDuration === 'limited' ? 'checked' : '' }} data-access-duration>
                <span class="font-medium text-15px text-primary">مدة وصول محدودة</span>
            </label>
        </div>
        <div class="mt-4 {{ $accessDuration === 'limited' ? '' : 'hidden' }}" data-access-days-wrap>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">عدد أيام الوصول</label>
            <input type="number" name="access_days" value="{{ old('access_days', $accessDays ?? 30) }}" min="1" max="3650"
                class="{{ $input }}" data-field-label="عدد أيام الوصول">
            @error('access_days')
                <p class="mt-2 font-medium text-13px text-[#B91C1C]">{{ $message }}</p>
            @enderror
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
        <input type="number" name="capacity" value="{{ old('capacity', $draftCapacity ?? '') }}" min="1"
            class="{{ $input }} {{ $errors->has('capacity') ? 'border-[#FECACA]' : '' }}"
            placeholder="اتركه فارغًا لسعة غير محدودة" data-field-label="سعة الطلاب">
        @error('capacity')
            <p class="mt-2 font-medium text-13px text-[#B91C1C]">{{ $message }}</p>
        @enderror
    </div>
</section>
