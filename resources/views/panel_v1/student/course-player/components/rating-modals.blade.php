{{-- Student rating modals: course content + instructor (panel_v1) --}}
@php
    $webinarId = $webinar->id ?? null;
    $courseRateUrl = !empty($slug)
        ? route('panel.v1.student.course.reviews.course', ['slug' => $slug])
        : '#';
    $instructorRateUrl = !empty($slug)
        ? route('panel.v1.student.course.reviews.instructor', ['slug' => $slug])
        : '#';
    $existingReview = $existingReview ?? null;
    $courseStars = (int) ($existingReview->content_quality ?? 0);
    $instructorStars = (int) ($existingReview->instructor_skills ?? 0);
@endphp

{{-- Course content rating --}}
<div id="student-rate-course-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="student-rate-course-title"
    data-rating-modal="course">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-md max-h-[90vh] px-4 my-6">
        <form method="POST" action="{{ $courseRateUrl }}"
            class="modal-content relative rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl"
            data-rating-form="course">
            @csrf
            <input type="hidden" name="webinar_id" value="{{ $webinarId }}">
            <input type="hidden" name="rate" value="{{ $courseStars ?: 5 }}" data-rating-value>

            <button type="button" class="absolute start-3 top-3 size-9 rounded-full border border-d9 center hover:bg-fa transition z-10"
                aria-label="إغلاق" data-overlay="#student-rate-course-modal">
                <span class="icon-[tabler--x] size-5 text-primary"></span>
            </button>

            <div class="px-6 sm:px-8 pt-12 pb-8 text-center">
                <h3 id="student-rate-course-title" class="font-bold text-20px sm:text-22px text-primary mb-2">
                    ما رأيك في محتوى الدورة التدريبية؟
                </h3>
                <p class="font-medium text-14px text-gray mb-6 leading-relaxed">
                    قيم مدى استفادتك من المادة العلمية والأنشطة التطبيقية
                </p>

                <div class="flex items-center justify-center gap-2 mb-3" data-rating-stars role="radiogroup" aria-label="تقييم الدورة">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" data-star="{{ $i }}"
                            class="size-10 sm:size-11 center transition text-[#F5C451] hover:scale-110"
                            aria-label="{{ $i }} نجوم">
                            <span class="icon-[tabler--star-filled] size-8 sm:size-9 {{ $i <= ($courseStars ?: 5) ? '' : 'opacity-25 text-gray' }}" data-star-icon></span>
                        </button>
                    @endfor
                </div>
                <p class="font-semibold text-15px text-[#0F3D36] mb-5 min-h-6" data-rating-label>دورة ممتازة ومحتوى قيم جداً! 🔥</p>

                <textarea name="description" rows="4"
                    class="textarea textarea-bordered w-full rounded-14px border-d9 font-medium text-14px text-primary focus:outline-none focus:border-primary min-h-28 resize-y text-start"
                    placeholder="شاركنا ما الذي أعجبك في الدورة أو اقتراحات للتطوير...">{{ $existingReview->description ?? '' }}</textarea>

                <p class="hidden mt-3 font-medium text-13px text-[#B91C1C]" data-rating-error></p>

                <button type="submit"
                    class="mt-5 w-full h-12 rounded-12px bg-[#0F3D36] hover:opacity-95 text-white font-bold text-15px transition border-0">
                    إرسال تقييم الدورة
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Instructor rating --}}
<div id="student-rate-instructor-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="student-rate-instructor-title"
    data-rating-modal="instructor">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-md max-h-[90vh] px-4 my-6">
        <form method="POST" action="{{ $instructorRateUrl }}"
            class="modal-content relative rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl"
            data-rating-form="instructor">
            @csrf
            <input type="hidden" name="webinar_id" value="{{ $webinarId }}">
            <input type="hidden" name="rate" value="{{ $instructorStars ?: 4 }}" data-rating-value>

            <button type="button" class="absolute start-3 top-3 size-9 rounded-full border border-d9 center hover:bg-fa transition z-10"
                aria-label="إغلاق" data-overlay="#student-rate-instructor-modal">
                <span class="icon-[tabler--x] size-5 text-primary"></span>
            </button>

            <div class="px-6 sm:px-8 pt-12 pb-8 text-center">
                <h3 id="student-rate-instructor-title" class="font-bold text-20px sm:text-22px text-primary mb-2">
                    كيف كانت تجربتك مع المدرب؟
                </h3>
                <p class="font-medium text-14px text-gray mb-6 leading-relaxed">
                    تقييمك لمساعدتنا في تحسين جودة الدورات والدروس المباشرة
                </p>

                <div class="flex items-center justify-center gap-2 mb-3" data-rating-stars role="radiogroup" aria-label="تقييم المدرب">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" data-star="{{ $i }}"
                            class="size-10 sm:size-11 center transition text-[#F5C451] hover:scale-110"
                            aria-label="{{ $i }} نجوم">
                            <span class="icon-[tabler--star-filled] size-8 sm:size-9 {{ $i <= ($instructorStars ?: 4) ? '' : 'opacity-25 text-gray' }}" data-star-icon></span>
                        </button>
                    @endfor
                </div>
                <p class="font-semibold text-15px text-[#0F3D36] mb-5 min-h-6" data-rating-label>ممتاز جداً! ✨</p>

                <textarea name="description" rows="4"
                    class="textarea textarea-bordered w-full rounded-14px border-d9 font-medium text-14px text-primary focus:outline-none focus:border-primary min-h-28 resize-y text-start"
                    placeholder="اكتب انطباعك أو أي ملاحظات إضافية للمدرب (اختياري)..."></textarea>

                <p class="hidden mt-3 font-medium text-13px text-[#B91C1C]" data-rating-error></p>

                <button type="submit"
                    class="mt-5 w-full h-12 rounded-12px bg-[#0F3D36] hover:opacity-95 text-white font-bold text-15px transition border-0">
                    إرسال التقييم
                </button>
            </div>
        </form>
    </div>
</div>
