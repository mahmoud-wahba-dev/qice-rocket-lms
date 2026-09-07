{{-- Quiz builder modal: settings + questions tabs (FlyonUI modal-middle) --}}
<div id="instructor-quiz-builder-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="instructor-quiz-builder-title">
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-[50rem] max-h-[90vh] px-4 my-6">
        <div class="modal-content relative flex flex-col max-h-[90vh] rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl">
            {{-- Header --}}
            <div class="relative shrink-0 px-5 sm:px-7 pt-6 pb-4 border-b border-d9">
                <h3 id="instructor-quiz-builder-title" class="font-bold text-20px sm:text-22px text-primary pe-10">
                    إعدادات وبناء الاختبار
                </h3>
                <button type="button"
                    class="btn btn-text btn-circle btn-sm absolute end-3 top-4"
                    aria-label="إغلاق"
                    data-overlay="#instructor-quiz-builder-modal">
                    <span class="icon-[tabler--x] size-5 text-gray"></span>
                </button>

                <div class="grid grid-cols-2 gap-3 mt-5" role="tablist">
                    <button type="button"
                        class="quiz-builder-tab active flex items-center justify-center gap-2 rounded-12px border border-d9 bg-white h-12 px-3 font-semibold text-14px sm:text-15px text-primary transition"
                        data-quiz-builder-tab="settings" role="tab" aria-selected="true">
                        <span class="icon-[tabler--adjustments-horizontal] size-5 shrink-0"></span>
                        <span class="truncate">البيانات والإعدادات</span>
                    </button>
                    <button type="button"
                        class="quiz-builder-tab flex items-center justify-center gap-2 rounded-12px border border-transparent bg-[#FAF8F4] h-12 px-3 font-semibold text-14px sm:text-15px text-gray transition"
                        data-quiz-builder-tab="questions" role="tab" aria-selected="false">
                        <span class="icon-[tabler--list-details] size-5 shrink-0"></span>
                        <span class="truncate">إضافة أسئلة الاختبار</span>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto overflow-x-hidden overscroll-contain px-5 sm:px-7 py-5 sm:py-6">
                {{-- Settings panel --}}
                <div id="quiz-builder-panel-settings" data-quiz-builder-panel="settings" class="space-y-5">
                    <div>
                        <label for="quiz-lang" class="block font-semibold text-14px text-primary mb-2">لغة الاختبار</label>
                        <select id="quiz-lang"
                            class="select select-bordered w-full h-12 rounded-10px border-d9 bg-white font-medium text-15px text-primary">
                            <option value="" disabled selected>اختر لغة الاختبار</option>
                            <option value="ar">العربية</option>
                            <option value="en">English</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="quiz-course" class="block font-semibold text-14px text-primary mb-2">اختر الدورة</label>
                            <select id="quiz-course"
                                class="select select-bordered w-full h-12 rounded-10px border-d9 bg-white font-medium text-15px text-primary">
                                <option value="" disabled selected>اختر الدورة التي يجب إضافة الاختبار لها</option>
                                <option value="demo">دبلومة UI/UX الشاملة</option>
                                <option value="cphq">الممارس المعتمد CPHQ</option>
                            </select>
                        </div>
                        <div>
                            <label for="quiz-lecture" class="block font-semibold text-14px text-primary mb-2">اختر المحاضرة</label>
                            <select id="quiz-lecture"
                                class="select select-bordered w-full h-12 rounded-10px border-d9 bg-white font-medium text-15px text-primary">
                                <option value="" disabled selected>اختر المحاضرة التي يجب إضافة الاختبار لها</option>
                                <option value="1">المحاضرة الأولى</option>
                                <option value="2">المحاضرة الثانية</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="quiz-title" class="block font-semibold text-14px text-primary mb-2">
                            عنوان الاختبار <span class="text-[#E11D48]">*</span>
                        </label>
                        <input id="quiz-title" type="text" value="اختبار جديد"
                            class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-15px text-primary">
                    </div>

                    <div>
                        <label for="quiz-desc" class="block font-semibold text-14px text-primary mb-2">
                            وصف الاختبار <span class="text-[#E11D48]">*</span>
                        </label>
                        <textarea id="quiz-desc" rows="3"
                            class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-15px text-primary leading-relaxed min-h-24"
                            placeholder="اختبار جديد">اختبار جديد</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="quiz-pass" class="block font-semibold text-14px text-primary mb-2">درجة النجاح المطلوبة</label>
                            <div class="relative">
                                <input id="quiz-pass" type="number" value="70" min="0" max="100"
                                    class="input input-bordered w-full h-12 rounded-10px border-d9 font-semibold text-15px text-primary pe-10">
                                <span class="absolute top-1/2 end-3 -translate-y-1/2 font-semibold text-14px text-gray">%</span>
                            </div>
                        </div>
                        <div>
                            <label for="quiz-attempts" class="block font-semibold text-14px text-primary mb-2">عدد المحاولات المسموحة</label>
                            <select id="quiz-attempts"
                                class="select select-bordered w-full h-12 rounded-10px border-d9 bg-white font-medium text-15px text-primary">
                                <option value="1" selected>محاولة واحدة</option>
                                <option value="2">محاولتان</option>
                                <option value="3">3 محاولات</option>
                                <option value="unlimited">غير محدود</option>
                            </select>
                        </div>
                    </div>

                    {{-- Toggle groups (FlyonUI switch — avoids sr-only focus scroll jump in modal) --}}
                    <div class="space-y-3">
                        <div class="rounded-12px border border-d9 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <label for="quiz-no-time-limit" class="min-w-0 text-start cursor-pointer">
                                    <p class="font-semibold text-15px text-primary mb-0.5">بدون حد زمني</p>
                                    <p class="font-medium text-13px text-gray leading-snug">يمكن للطالب إنهاء الاختبار بدون مؤقت</p>
                                </label>
                                <input id="quiz-no-time-limit" type="checkbox"
                                    class="switch switch-primary shrink-0 mt-0.5"
                                    data-quiz-no-time-limit>
                            </div>
                            <div class="mt-4" data-quiz-duration-wrap>
                                <label for="quiz-duration" class="block font-semibold text-13px text-primary mb-2">المدة الزمنية (بالدقائق)</label>
                                <input id="quiz-duration" type="number" value="20" min="1"
                                    class="input input-bordered w-full sm:w-40 h-11 rounded-10px border-d9 font-semibold text-15px text-primary">
                            </div>
                        </div>

                        <div class="rounded-12px border border-d9 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <label for="quiz-randomize" class="min-w-0 text-start cursor-pointer">
                                    <p class="font-semibold text-15px text-primary mb-0.5">ترتيب عشوائي للأسئلة</p>
                                    <p class="font-medium text-13px text-gray leading-snug">تظهر الأسئلة بترتيب مختلف لكل طالب</p>
                                </label>
                                <input id="quiz-randomize" type="checkbox"
                                    class="switch switch-primary shrink-0 mt-0.5">
                            </div>
                        </div>

                        <div class="rounded-12px border border-d9 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <label for="quiz-certificate" class="min-w-0 text-start cursor-pointer">
                                    <p class="font-semibold text-15px text-primary mb-0.5">الحصول على شهادة</p>
                                    <p class="font-medium text-13px text-gray leading-snug">الحصول على شهادة تقديرية عند النجاح</p>
                                </label>
                                <input id="quiz-certificate" type="checkbox"
                                    class="switch switch-primary shrink-0 mt-0.5">
                            </div>
                        </div>

                        <div class="rounded-12px border border-d9 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <label for="quiz-show-answers" class="min-w-0 text-start cursor-pointer">
                                    <p class="font-semibold text-15px text-primary mb-0.5">إظهار الإجابات الصحيحة بعد التسليم</p>
                                    <p class="font-medium text-13px text-gray leading-snug">عرض الإجابات الصحيحة للطالب بعد إنهاء الاختبار</p>
                                </label>
                                <input id="quiz-show-answers" type="checkbox" checked
                                    class="switch switch-primary shrink-0 mt-0.5">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Questions panel --}}
                <div id="quiz-builder-panel-questions" data-quiz-builder-panel="questions" class="hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                        <p class="font-semibold text-15px sm:text-16px text-primary">
                            الأسئلة (0) — مجموع الدرجات 0
                        </p>
                        <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                            <button type="button"
                                class="dropdown-toggle inline-flex items-center justify-center gap-2 rounded-12px bg-primary h-11 px-4 font-semibold text-14px text-white hover:opacity-95 transition"
                                id="quiz-add-question-btn">
                                اضافة سؤال جديد
                            </button>
                            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-52 py-2 rounded-12px bg-primary border-0 shadow-xl z-30"
                                role="menu" aria-labelledby="quiz-add-question-btn">
                                <li>
                                    <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-white hover:!bg-white/10">
                                        اختيار من متعدد
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="dropdown-item px-4 py-2.5 font-medium text-15px text-white hover:!bg-white/10">
                                        سؤال مقالي
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="rounded-14px bg-[#FAF8F4] border border-d9 min-h-56 center flex-col gap-2 px-6 py-12 text-center">
                        <span class="icon-[tabler--file-description] size-10 text-primary mb-1"></span>
                        <p class="font-semibold text-16px text-primary">اضافة سؤال جديد</p>
                        <p class="font-medium text-14px text-gray">اختر نوع الاسئلة</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex flex-wrap items-center justify-start gap-3 px-5 sm:px-7 py-4 border-t border-d9 bg-white">
                <button type="button"
                    class="inline-flex items-center justify-center rounded-12px border border-d9 h-11 px-6 font-semibold text-15px text-primary bg-white hover:bg-fa transition"
                    data-overlay="#instructor-quiz-builder-modal">
                    إلغاء
                </button>
                <button type="button"
                    class="inline-flex items-center justify-center rounded-12px bg-primary h-11 px-6 font-bold text-15px text-white hover:opacity-95 transition"
                    data-overlay="#instructor-quiz-builder-modal">
                    حفظ وربط الاختبار
                </button>
            </div>
        </div>
    </div>
</div>
