{{-- Quiz answer review modal (FlyonUI modal-middle) --}}
@php
    $reviewQuestions = $quizReviewQuestions ?? [
        [
            'question' => 'هل تتوافق معايير الجودة الصحية الحديثة مع تقليص تكاليف التشغيل؟ وضح ذلك.',
            'model' => 'الإجابة هي نعم. حيث يسهم تطبيق المعايير في تقليل الأخطاء وإعادة العمل، مما يعزز الكفاءة.',
            'submitted' => 'الإجابة هي نعم. حيث يسهم تطبيق المعايير في تقليل الأخطاء وإعادة العمل، مما يعزز الكفاءة.',
            'score' => 10,
            'max' => 10,
        ],
        [
            'question' => 'اذكر مؤشرَين من مؤشرات الأداء المرتبطة برضا المريض في أقسام الطوارئ.',
            'model' => 'زمن الانتظار قبل الكشف، ونسبة الشكاوى المستجابة خلال 48 ساعة.',
            'submitted' => 'زمن الانتظار، ومعدل رضا المرضى بعد الزيارة.',
            'score' => 7,
            'max' => 10,
        ],
    ];
@endphp

<div id="instructor-quiz-review-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-quiz-review-title"
    data-quiz-review-modal
    data-quiz-review-questions='@json($reviewQuestions)'>
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[36rem] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative flex flex-col max-h-[90vh] rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl">
            {{-- Header --}}
            <div class="relative shrink-0 px-5 sm:px-7 pt-6 pb-4 pe-14">
                <h3 id="instructor-quiz-review-title" class="font-bold text-20px sm:text-22px text-primary leading-snug mb-1">
                    مراجعة إجابة السؤال
                    (<span data-quiz-review-counter>(1 من {{ count($reviewQuestions) }})</span>
                </h3>
                <p class="font-medium text-14px text-gray">
                    {{ $quizReviewSubtitle ?? 'اختبار كورس التست • دورة CPHQ' }}
                </p>
                <button type="button"
                    class="btn btn-text btn-circle btn-sm absolute end-3 top-4 bg-[#F1F5F9] hover:bg-[#E8ECEA]"
                    aria-label="إغلاق"
                    data-overlay="#instructor-quiz-review-modal">
                    <span class="icon-[tabler--x] size-5 text-gray"></span>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto overflow-x-hidden overscroll-contain px-5 sm:px-7 pb-5 space-y-4">
                <p class="font-medium text-15px sm:text-16px text-primary leading-relaxed text-start">
                    <span class="font-bold">السؤال:</span>
                    <span data-quiz-review-question></span>
                </p>

                {{-- Model answer --}}
                <div class="rounded-14px border border-[#BFDBFE] bg-[#EFF6FF] px-4 py-4 text-start">
                    <span class="inline-flex rounded-full bg-[#DBEAFE] px-3 py-1 font-semibold text-12px text-[#1D4ED8] mb-3">
                        الإجابة النموذجية
                    </span>
                    <p class="font-medium text-14px sm:text-15px text-primary leading-relaxed" data-quiz-review-model></p>
                </div>

                {{-- Submitted answer --}}
                <div class="rounded-14px border border-[#A7F3D0] bg-[#ECFDF5] px-4 py-4 text-start">
                    <span class="inline-flex rounded-full bg-[#D1FAE5] px-3 py-1 font-semibold text-12px text-[#059669] mb-3">
                        إجابتك المقدمة
                    </span>
                    <p class="font-medium text-14px sm:text-15px text-primary leading-relaxed" data-quiz-review-submitted></p>
                </div>

                {{-- Grade --}}
                <div class="text-start">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-semibold text-15px text-primary">الدرجة</span>
                        <button type="button" class="font-semibold text-13px text-[#3B82F6] hover:opacity-80 transition"
                            data-quiz-review-edit>
                            تعديل
                        </button>
                    </div>
                    <div class="rounded-12px border border-d9 bg-white px-4 py-3.5">
                        <p class="font-semibold text-16px text-start">
                            <span class="text-[#00B31B]" data-quiz-review-score>10</span>
                            <span class="text-primary"> من </span>
                            <span class="text-primary" data-quiz-review-max>10</span>
                        </p>
                        <input type="number" min="0" class="hidden input input-bordered w-full h-11 rounded-10px border-d9 font-semibold text-16px text-primary mt-2"
                            data-quiz-review-score-input>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex flex-wrap items-center justify-between gap-3 px-5 sm:px-7 py-4 border-t border-d9">
                <button type="button"
                    class="inline-flex items-center justify-center rounded-12px border border-d9 h-11 px-5 font-semibold text-15px text-primary bg-white hover:bg-fa transition"
                    data-overlay="#instructor-quiz-review-modal">
                    إغلاق
                </button>
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="inline-flex items-center justify-center rounded-12px bg-[#F1F5F9] h-11 px-5 font-semibold text-15px text-gray hover:bg-[#E8ECEA] transition disabled:opacity-40 disabled:pointer-events-none"
                        data-quiz-review-prev disabled>
                        السابق
                    </button>
                    <button type="button"
                        class="inline-flex items-center justify-center rounded-12px bg-primary h-11 px-5 font-bold text-15px text-white hover:opacity-95 transition disabled:opacity-40 disabled:pointer-events-none"
                        data-quiz-review-next>
                        التالي
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
