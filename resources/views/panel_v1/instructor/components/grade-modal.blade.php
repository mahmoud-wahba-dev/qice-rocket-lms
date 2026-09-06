@php
    $maxGrade = $maxGrade ?? 50;
    $passGrade = $passGrade ?? 25;
@endphp

<div id="instructor-grade-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
    <div class="modal-dialog overlay-open:opacity-100 max-w-md">
        <div class="modal-content rounded-20px border border-d9 p-0 overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-6 pt-6">
                <h3 class="font-bold text-22px text-primary">تقييم التكليف</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm" aria-label="إغلاق"
                    data-overlay="#instructor-grade-modal">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <div class="px-6 py-8 text-center">
                <div class="size-16 rounded-full bg-[#0FC787] center mx-auto mb-5">
                    <span class="icon-[tabler--check] size-8 text-white"></span>
                </div>
                <h4 class="font-bold text-20px text-primary mb-2">اعتماد وتقييم درجة الطالب</h4>
                <p class="font-medium text-14px text-gray leading-relaxed mb-8">
                    بمجرد إرسال الدرجة، سيتم إغلاق الواجب ولن يتمكن الطالب من إضافة تعديلات أخرى.
                </p>

                <div class="relative mb-4 text-start">
                    <label for="grade-input"
                        class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-primary">
                        درجة الواجب *
                    </label>
                    <div class="flex items-center border-2 border-primary rounded-12px overflow-hidden">
                        <input id="grade-input" type="number" value="25" min="0" max="{{ $maxGrade }}"
                            class="input border-0 w-full h-14 font-bold text-24px text-primary focus:outline-none">
                        <span class="px-4 font-medium text-13px text-gray whitespace-nowrap">
                            الدرجة العظمى: {{ $maxGrade }}
                        </span>
                    </div>
                </div>

                <p class="font-medium text-14px text-start mb-8">
                    <span class="text-[#E11D48]">*</span>
                    <span class="text-gray">درجة النجاح المطلوبة</span>
                    <span class="font-bold text-[#0FC787] ms-2">{{ $passGrade }} / {{ $maxGrade }}</span>
                </p>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <button type="button" class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-gray"
                        data-overlay="#instructor-grade-modal">
                        إلغاء
                    </button>
                    <button type="button" class="btn btn-primary rounded-12px h-12 px-6 font-bold text-15px"
                        data-overlay="#instructor-grade-modal">
                        إرسال الدرجة والاعتماد
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
