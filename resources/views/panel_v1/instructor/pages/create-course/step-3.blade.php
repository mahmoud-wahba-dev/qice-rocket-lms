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
        <input type="checkbox" class="switch switch-primary shrink-0" aria-label="تفعيل الاختبارات" checked disabled>
    </div>
    <div class="mt-5">
        <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">ربط اختبار موجود بالدورة</label>
        <select name="quiz_id"
            class="select select-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary">
            <option value="">بدون ربط (يمكن الإضافة لاحقًا)</option>
            @foreach ($teacherQuizzes ?? [] as $teacherQuiz)
                <option value="{{ $teacherQuiz['id'] }}">{{ $teacherQuiz['title'] }}</option>
            @endforeach
        </select>
        <p class="font-medium text-13px text-gray mt-2">أو <a href="{{ route('panel.v1.instructor.quizzes.create') }}" class="text-primary font-bold">أنشئ اختبارًا جديدًا</a> ثم اربطه هنا.</p>
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
        <input type="checkbox" name="certificate" value="1" class="switch switch-primary shrink-0" aria-label="إصدار شهادة إتمام"
            {{ !empty($draftCertificate) ? 'checked' : '' }}>
    </div>
</section>
