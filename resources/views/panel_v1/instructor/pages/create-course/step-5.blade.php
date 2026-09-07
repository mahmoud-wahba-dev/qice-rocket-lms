@php
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $textarea = 'textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary min-h-36 resize-y';
@endphp

<section class="{{ $card }}">
    <div class="flex items-start gap-3 mb-5">
        <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--message-circle] size-5 text-primary"></span>
        </span>
        <div class="text-start min-w-0">
            <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">ملاحظات لفريق المراجعة</h2>
            <p class="font-medium text-14px text-gray">أي تفاصيل تساعد الفريق على مراجعة الدورة</p>
        </div>
    </div>
    <textarea rows="5" class="{{ $textarea }}" placeholder="اكتب ملاحظاتك هنا..."></textarea>
</section>

<section class="{{ $card }}">
    <div class="flex items-start gap-3 mb-5">
        <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
            <span class="icon-[tabler--shield-check] size-5 text-primary"></span>
        </span>
        <div class="text-start min-w-0">
            <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">الشروط وقائمة النشر</h2>
            <p class="font-medium text-14px text-gray">تأكيدات مطلوبة قبل الإرسال</p>
        </div>
    </div>
    <div class="space-y-3">
        <label class="flex items-start gap-3 rounded-12px bg-[#F7F0E6] border border-[#C99C69]/30 px-4 sm:px-5 py-4 cursor-pointer">
            <input type="checkbox" class="checkbox checkbox-primary mt-0.5 shrink-0">
            <span class="font-medium text-14px sm:text-15px text-primary leading-relaxed text-start">
                أؤكد أنني أملك حقوق الملكية الفكرية الكاملة لهذا المحتوى.
            </span>
        </label>
        <label class="flex items-start gap-3 rounded-12px bg-[#F7F0E6] border border-[#C99C69]/30 px-4 sm:px-5 py-4 cursor-pointer">
            <input type="checkbox" class="checkbox checkbox-primary mt-0.5 shrink-0">
            <span class="font-medium text-14px sm:text-15px text-primary leading-relaxed text-start">
                أوافق على شروط وإرشادات المدربين في المنصة.
            </span>
        </label>
    </div>
</section>
