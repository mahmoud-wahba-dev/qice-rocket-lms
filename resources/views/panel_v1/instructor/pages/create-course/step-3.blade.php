@php
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $select = 'select select-bordered w-full h-12 sm:h-14 min-h-12 sm:min-h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $linkedQuizId = old('quiz_id');
    if ($linkedQuizId === null && !empty($draftId) && !empty($teacherQuizzes)) {
        foreach ($teacherQuizzes as $q) {
            if ((int) ($q['webinar_id'] ?? 0) === (int) $draftId) {
                $linkedQuizId = $q['id'];
                break;
            }
        }
    }
    $quizStoreUrl = $wizardQuizStoreUrl ?? (
        !empty($isAdminWizard)
            ? route('panel.v1.admin.education.curriculum.quizzes.store')
            : route('panel.v1.instructor.curriculum.quizzes.store')
    );
@endphp

<section class="{{ $card }}" data-wizard-quizzes data-quiz-store-url="{{ $quizStoreUrl }}">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div class="flex items-start gap-3 min-w-0">
            <span class="size-11 rounded-12px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--clipboard-check] size-5 text-primary"></span>
            </span>
            <div class="text-start min-w-0">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-1">الاختبارات والتقييم</h2>
                <p class="font-medium text-14px text-gray">أنشئ اختبارًا أو اربط اختبارًا موجودًا بالدورة</p>
            </div>
        </div>
        <button type="button"
            class="inline-flex items-center justify-center gap-2 h-11 px-4 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-90 transition shrink-0"
            data-open-wizard-quiz>
            <span class="icon-[tabler--plus] size-4"></span>
            إضافة اختبار
        </button>
    </div>

    <div class="space-y-5">
        <div>
            <label class="block font-semibold text-14px sm:text-15px text-primary mb-2">الاختبار المرتبط بالدورة</label>
            <select name="quiz_id" data-wizard-quiz-select
                class="{{ $select }} {{ $errors->has('quiz_id') ? 'border-[#FECACA]' : '' }}">
                <option value="">بدون ربط (يمكن الإضافة لاحقًا)</option>
                @foreach ($teacherQuizzes ?? [] as $teacherQuiz)
                    <option value="{{ $teacherQuiz['id'] }}"
                        {{ (string) $linkedQuizId === (string) $teacherQuiz['id'] ? 'selected' : '' }}>
                        {{ $teacherQuiz['title'] }}
                    </option>
                @endforeach
            </select>
            @error('quiz_id')
                <p class="mt-2 font-medium text-13px text-[#B91C1C]">{{ $message }}</p>
            @enderror
            <p class="font-medium text-13px text-gray mt-2">
                اضغط «إضافة اختبار» لإنشاء اختبار جديد وربطه تلقائيًا بهذه الدورة.
            </p>
        </div>

        <div class="rounded-12px border border-d9 bg-[#FAFAF4] px-4 sm:px-5 py-4 hidden" data-wizard-quiz-created>
            <p class="font-semibold text-14px text-primary" data-wizard-quiz-created-title></p>
            <p class="font-medium text-13px text-gray mt-1">تم إنشاء الاختبار وربطه بالدورة. يمكنك إضافة الأسئلة لاحقًا من قائمة الاختبارات.</p>
        </div>
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
        <input type="hidden" name="certificate" value="0">
        <input type="checkbox" name="certificate" value="1" class="switch switch-primary shrink-0" aria-label="إصدار شهادة إتمام"
            {{ old('certificate', !empty($draftCertificate) ? '1' : '0') == '1' ? 'checked' : '' }}>
    </div>
</section>
