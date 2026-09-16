@php
    $assignmentCourses = $assignmentCourses ?? [];
    $inputClass = 'input input-bordered w-full h-12 rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary';
    $labelClass = 'font-semibold text-14px text-primary mb-2 block';
@endphp

<div id="instructor-assignment-create-modal"
    class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden"
    role="dialog" tabindex="-1" aria-labelledby="assignment-create-title">
    <div class="modal-dialog overlay-open:opacity-100 max-w-[640px] w-[92vw]">
        <form method="POST"
            action="{{ route('panel.v1.instructor.assignments.store') }}"
            class="modal-content relative rounded-20px border border-d9 p-0 overflow-hidden bg-white"
            id="instructor-assignment-create-form">
            @csrf

            <div class="flex items-center justify-between gap-3 px-6 pt-6 pe-14 border-b border-d9 pb-4">
                <h3 id="assignment-create-title" class="font-bold text-22px text-primary">إضافة تكليف جديد</h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="إغلاق"
                    data-overlay="#instructor-assignment-create-modal">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <div class="px-6 py-6 space-y-4 max-h-[70vh] overflow-y-auto">
                @if (empty($assignmentCourses))
                    <div class="rounded-12px bg-[#FFFBEB] border border-[#FDE68A] px-4 py-3">
                        <p class="font-medium text-14px text-[#92400E]">ليس لديك دورات بعد. أنشئ دورة أولاً ثم أضف التكليف.</p>
                        <a href="{{ route('panel.v1.instructor.courses.create') }}"
                            class="inline-flex mt-3 font-semibold text-14px text-primary hover:opacity-80">إنشاء دورة</a>
                    </div>
                @else
                    @if ($errors->any())
                        <div class="rounded-12px bg-[#FEF2F2] border border-[#FECACA] px-4 py-3">
                            <ul class="list-disc list-inside space-y-1 font-medium text-14px text-[#DC2626]">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label for="assign-webinar-id" class="{{ $labelClass }}">الدورة *</label>
                        <select id="assign-webinar-id" name="webinar_id" required class="select select-bordered {{ $inputClass }}">
                            <option value="">اختر الدورة</option>
                            @foreach ($assignmentCourses as $course)
                                <option value="{{ $course['id'] }}" @selected(old('webinar_id') == $course['id'])>
                                    {{ $course['title'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="assign-chapter-id" class="{{ $labelClass }}">الوحدة</label>
                        <select id="assign-chapter-id" name="chapter_id" class="select select-bordered {{ $inputClass }}">
                            <option value="">تُنشأ وحدة تلقائياً إن لزم</option>
                        </select>
                        <p class="font-medium text-12px text-gray mt-1.5">إن لم تختر وحدة، يُستخدم أول فصل في الدورة أو يُنشأ تلقائياً.</p>
                    </div>

                    <div>
                        <label for="assign-title" class="{{ $labelClass }}">عنوان التكليف *</label>
                        <input id="assign-title" name="title" type="text" required maxlength="255"
                            value="{{ old('title') }}" class="{{ $inputClass }}" placeholder="مثال: تسليم خطة تحسين العملية">
                    </div>

                    <div>
                        <label for="assign-description" class="{{ $labelClass }}">الوصف / المطلوب *</label>
                        <textarea id="assign-description" name="description" required rows="4"
                            class="textarea textarea-bordered w-full rounded-10px border-d9 bg-[#F8FAFC] font-medium text-15px text-primary min-h-28"
                            placeholder="اكتب تعليمات التكليف للطالب...">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="assign-grade" class="{{ $labelClass }}">الدرجة العظمى *</label>
                            <input id="assign-grade" name="grade" type="number" required min="1" max="1000"
                                value="{{ old('grade', 100) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="assign-pass-grade" class="{{ $labelClass }}">درجة النجاح *</label>
                            <input id="assign-pass-grade" name="pass_grade" type="number" required min="0" max="1000"
                                value="{{ old('pass_grade', 50) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="assign-deadline" class="{{ $labelClass }}">الموعد (أيام من الشراء)</label>
                            <input id="assign-deadline" name="deadline" type="number" min="1" max="365"
                                value="{{ old('deadline', 14) }}" class="{{ $inputClass }}" placeholder="اختياري">
                        </div>
                        <div>
                            <label for="assign-attempts" class="{{ $labelClass }}">عدد المحاولات</label>
                            <input id="assign-attempts" name="attempts" type="number" min="1" max="50"
                                value="{{ old('attempts', 2) }}" class="{{ $inputClass }}" placeholder="اختياري">
                        </div>
                    </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-d9 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" class="btn btn-ghost rounded-12px h-12 px-6 font-semibold text-15px text-gray"
                    data-overlay="#instructor-assignment-create-modal">
                    إلغاء
                </button>
                @if (!empty($assignmentCourses))
                    <button type="submit" class="btn btn-primary rounded-12px h-12 px-6 font-bold text-15px">
                        إنشاء التكليف
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const courses = @json($assignmentCourses);
    const webinarSelect = document.getElementById('assign-webinar-id');
    const chapterSelect = document.getElementById('assign-chapter-id');
    if (!webinarSelect || !chapterSelect) return;

    const oldChapterId = @json(old('chapter_id'));

    const fillChapters = (webinarId, preferredChapterId = null) => {
        const course = courses.find((c) => String(c.id) === String(webinarId));
        chapterSelect.innerHTML = '<option value="">تُنشأ وحدة تلقائياً إن لزم</option>';
        (course?.chapters || []).forEach((chapter) => {
            const opt = document.createElement('option');
            opt.value = chapter.id;
            opt.textContent = chapter.title;
            if (preferredChapterId && String(preferredChapterId) === String(chapter.id)) {
                opt.selected = true;
            }
            chapterSelect.appendChild(opt);
        });
    };

    webinarSelect.addEventListener('change', () => fillChapters(webinarSelect.value));
    if (webinarSelect.value) {
        fillChapters(webinarSelect.value, oldChapterId);
    }

    @if ($errors->any())
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.querySelector('[data-overlay="#instructor-assignment-create-modal"]');
        if (btn) btn.click();
    });
    @endif
})();
</script>
