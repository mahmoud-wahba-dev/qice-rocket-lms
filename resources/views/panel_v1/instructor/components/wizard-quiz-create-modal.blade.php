{{-- Create quiz from course wizard (outside main form — avoids nested forms) --}}
@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $select = 'select select-bordered w-full h-12 sm:h-14 min-h-12 sm:min-h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $quizStoreUrl = $wizardQuizStoreUrl ?? (
        !empty($isAdminWizard)
            ? route('panel.v1.admin.education.curriculum.quizzes.store')
            : route('panel.v1.instructor.curriculum.quizzes.store')
    );
@endphp
<div id="wizard-quiz-create-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="wizard-quiz-create-title"
    data-wizard-quiz-modal>
    <div class="modal-dialog overlay-open:opacity-100 w-full max-w-lg max-h-[90vh] px-4 my-6">
        <form method="POST" action="{{ $quizStoreUrl }}"
            class="modal-content relative rounded-20px border border-d9 bg-white p-0 overflow-hidden shadow-xl"
            data-wizard-quiz-form>
            @csrf
            <input type="hidden" name="draft_id" value="{{ $draftId ?? '' }}" data-draft-id-input>

            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-d9">
                <h3 id="wizard-quiz-create-title" class="font-bold text-18px text-primary">إضافة اختبار جديد</h3>
                <button type="button" class="size-9 rounded-10px center hover:bg-fa transition" aria-label="إغلاق"
                    data-overlay="#wizard-quiz-create-modal" data-wizard-quiz-close>
                    <span class="icon-[tabler--x] size-5 text-primary"></span>
                </button>
            </div>

            <div class="px-5 sm:px-6 py-5 space-y-4">
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">عنوان الاختبار <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required class="{{ $input }}" placeholder="مثال: اختبار الوحدة الأولى" data-field-label="عنوان الاختبار">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-14px text-primary mb-2">درجة النجاح % <span class="text-red-500">*</span></label>
                        <input type="number" name="pass_mark" required min="0" max="100" value="70" class="{{ $input }}" data-field-label="درجة النجاح">
                    </div>
                    <div>
                        <label class="block font-semibold text-14px text-primary mb-2">المدة (دقيقة)</label>
                        <input type="number" name="time" min="0" value="30" class="{{ $input }}" placeholder="0 = بلا حد" data-field-label="مدة الاختبار">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-14px text-primary mb-2">عدد المحاولات</label>
                    <select name="attempt" class="{{ $select }}">
                        <option value="1" selected>محاولة واحدة</option>
                        <option value="2">محاولتان</option>
                        <option value="3">3 محاولات</option>
                        <option value="">غير محدود</option>
                    </select>
                </div>
                <p class="hidden font-medium text-13px text-[#B91C1C]" data-wizard-quiz-error></p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 px-5 sm:px-6 py-4 border-t border-d9 bg-[#FAFAF4]">
                <button type="button" class="btn btn-ghost rounded-12px h-11 px-5 font-semibold text-14px border border-d9"
                    data-overlay="#wizard-quiz-create-modal" data-wizard-quiz-close>
                    إلغاء
                </button>
                <button type="submit" class="btn rounded-12px h-11 px-5 font-bold text-14px bg-primary text-white border-0" data-wizard-quiz-submit>
                    إنشاء وربط بالدورة
                </button>
            </div>
        </form>
    </div>
</div>
