@php
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
@endphp

<section class="{{ $card }}">
    <div class="flex items-start gap-3 mb-6">
        <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--clipboard-check] size-5 text-primary"></span>
        </span>
        <div class="text-start min-w-0">
            <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">الاختبارات والتقييم</h2>
            <p class="font-medium text-14px text-gray">أنشئ اختبارات لقياس تقدم الطلاب</p>
        </div>
    </div>
    <div class="flex items-center justify-between gap-4 rounded-12px border border-d9 bg-[#FAFAF4] px-4 sm:px-5 py-4">
        <div class="min-w-0 text-start">
            <p class="font-semibold text-15px sm:text-16px text-primary mb-1">تفعيل الاختبارات والامتحانات</p>
            <p class="font-medium text-13px sm:text-14px text-gray">إضافة اختبارات داخل وحدات الدورة</p>
        </div>
        <input type="checkbox" class="switch switch-primary shrink-0" aria-label="تفعيل الاختبارات">
    </div>
</section>

<section class="{{ $card }}">
    <div class="flex items-start gap-3 mb-6">
        <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--certificate] size-5 text-primary"></span>
        </span>
        <div class="text-start min-w-0">
            <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">شهادة إتمام الدورة</h2>
            <p class="font-medium text-14px text-gray">صمم شهادة تُمنح للطلاب عند الإنجاز</p>
        </div>
    </div>
    <div class="flex items-center justify-between gap-4 rounded-12px border border-d9 bg-[#FAFAF4] px-4 sm:px-5 py-4">
        <div class="min-w-0 text-start">
            <p class="font-semibold text-15px sm:text-16px text-primary mb-1">إصدار شهادة إتمام</p>
            <p class="font-medium text-13px sm:text-14px text-gray">تُصدر تلقائيًا عند تحقق شرط الإنجاز</p>
        </div>
        <input type="checkbox" class="switch switch-primary shrink-0" aria-label="إصدار شهادة إتمام">
    </div>
</section>
