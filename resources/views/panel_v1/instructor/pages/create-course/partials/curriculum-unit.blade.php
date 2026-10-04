@php
    $input = $input ?? 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $draftIdValue = $draftIdValue ?? '';
    $isTemplate = !empty($isTemplate);
    $courseTypeKey = $courseTypeKey ?? 'recorded';
    $openSession = $courseTypeKey === 'live';
    $openFile = $courseTypeKey === 'recorded';
    $openText = $courseTypeKey === 'text';
@endphp

<div class="rounded-14px border border-d9 overflow-hidden mb-4 last:mb-0" data-curriculum-unit="{{ $unit['id'] }}">
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-4 bg-[#FAFAF4] border-b border-d9">
        <div class="flex items-center gap-3 min-w-0">
            <span class="icon-[tabler--grip-vertical] size-5 text-gray shrink-0"></span>
            <div class="min-w-0 text-start flex-1">
                <p class="font-bold text-16px sm:text-17px text-primary truncate" data-unit-title>{{ $unit['title'] }}</p>
                <form method="POST" action="{{ $unit['update_url'] ?? route('panel.v1.instructor.curriculum.chapters.update', ['chapterId' => $unit['id']]) }}"
                    class="hidden flex flex-col sm:flex-row gap-2 mt-2" data-curriculum-ajax="chapter-update" data-unit-edit-form>
                    @csrf
                    <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
                    <input type="text" name="title" required value="{{ $unit['title'] }}"
                        class="{{ $input }} flex-1 min-w-0" data-field-label="عنوان الوحدة" data-unit-edit-input>
                    <button type="submit" class="inline-flex items-center justify-center h-11 px-4 rounded-10px bg-primary text-white font-semibold text-13px hover:opacity-90 transition shrink-0">حفظ</button>
                    <button type="button" class="inline-flex items-center justify-center h-11 px-4 rounded-10px border border-d9 font-semibold text-13px text-primary hover:bg-white transition shrink-0" data-unit-edit-cancel>إلغاء</button>
                </form>
                <p class="font-medium text-13px text-gray mt-0.5"><span data-unit-lesson-count>{{ count($unit['lessons'] ?? []) }}</span> دروس</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" class="size-9 rounded-8px center text-primary hover:bg-primary/10 transition" aria-label="تعديل الوحدة"
                data-unit-edit-toggle>
                <span class="icon-[tabler--pencil] size-5"></span>
            </button>
            <button type="button" class="size-9 rounded-8px center text-red-500 hover:bg-red-50 transition" aria-label="حذف الوحدة"
                data-curriculum-delete
                data-delete-url="{{ $unit['delete_url'] ?? route('panel.v1.instructor.curriculum.chapters.delete', ['chapterId' => $unit['id']]) }}"
                data-delete-title="حذف الوحدة"
                data-delete-message="حذف الوحدة بكل محتوياتها؟ لا يمكن التراجع بعد الحذف."
                data-delete-item="{{ $unit['title'] }}"
                data-delete-mode="delete-chapter"
                data-delete-draft-id="{{ $draftIdValue }}">
                <span class="icon-[tabler--trash] size-5"></span>
            </button>
        </div>
    </div>

    <div class="divide-y divide-d9" data-curriculum-lessons>
        @forelse ($unit['lessons'] ?? [] as $lesson)
            <div class="flex flex-wrap items-center gap-3 px-4 sm:px-5 py-3.5" data-curriculum-lesson="{{ $lesson['id'] }}" data-lesson-kind="{{ $lesson['kind'] }}">
                @if (($lesson['kind'] ?? '') === 'file' && ($lesson['preview_kind'] ?? '') === 'image' && !empty($lesson['preview_url']))
                    <button type="button"
                        class="size-12 rounded-10px overflow-hidden border border-d9 bg-fa shrink-0"
                        data-curriculum-preview
                        data-preview-url="{{ $lesson['preview_url'] }}"
                        data-preview-kind="image"
                        data-preview-title="{{ $lesson['title'] }}"
                        aria-label="معاينة الملف">
                        <img src="{{ $lesson['preview_url'] }}" alt="" class="size-full object-cover">
                    </button>
                @else
                    <span class="size-9 rounded-8px bg-primary/10 center shrink-0">
                        @if (($lesson['kind'] ?? '') === 'text')
                            <span class="icon-[tabler--file-text] size-4 text-primary"></span>
                        @elseif (($lesson['kind'] ?? '') === 'file')
                            <span class="icon-[tabler--paperclip] size-4 text-primary"></span>
                        @else
                            <span class="icon-[tabler--player-play] size-4 text-primary"></span>
                        @endif
                    </span>
                @endif
                <div class="min-w-0 flex-1 text-start">
                    <p class="font-semibold text-15px sm:text-16px text-primary truncate">{{ $lesson['title'] }}</p>
                    <p class="font-medium text-13px text-gray">{{ $lesson['duration'] }}</p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    @if (($lesson['kind'] ?? '') === 'file' && !empty($lesson['view_url']))
                        <button type="button"
                            class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-8px bg-primary/10 text-primary font-semibold text-12px hover:bg-primary/15 transition"
                            data-curriculum-preview
                            data-preview-url="{{ $lesson['view_url'] }}"
                            data-preview-kind="{{ $lesson['preview_kind'] ?? 'file' }}"
                            data-preview-title="{{ $lesson['title'] }}"
                            aria-label="عرض الملف">
                            <span class="icon-[tabler--eye] size-4"></span>
                            عرض
                        </button>
                    @endif
                    <button type="button"
                        class="size-8 rounded-8px center text-red-500 hover:bg-red-50"
                        aria-label="حذف"
                        data-curriculum-delete
                        data-delete-url="{{ $lesson['delete_url'] ?? '#' }}"
                        data-delete-title="تأكيد الحذف"
                        data-delete-message="{{ ($lesson['kind'] ?? '') === 'file' ? 'حذف هذا الملف من المنهج؟' : ((($lesson['kind'] ?? '') === 'text') ? 'حذف هذا الدرس النصي؟' : 'حذف هذه الجلسة؟') }}"
                        data-delete-item="{{ $lesson['title'] }}"
                        data-delete-mode="delete-lesson"
                        data-delete-draft-id="{{ $draftIdValue }}">
                        <span class="icon-[tabler--trash] size-4"></span>
                    </button>
                </div>
            </div>
        @empty
            <p class="font-medium text-14px text-gray px-4 sm:px-5 py-4" data-lessons-empty>لا يوجد محتوى بعد — أضف جلسة أو ملفًا أو درسًا نصيًا.</p>
        @endforelse
    </div>

    <div class="px-4 sm:px-5 py-4 border-t border-d9 bg-white space-y-4">
        <details class="rounded-10px border border-d9 {{ $openSession ? 'border-primary/30' : '' }}" @if($openSession) open @endif>
            <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary list-none flex items-center justify-between gap-2">
                <span>+ إضافة جلسة
                    @if ($courseTypeKey === 'live')
                        مباشرة
                        <span class="font-medium text-12px text-gray">(تاريخ ووقت مطلوبان)</span>
                    @elseif ($courseTypeKey === 'recorded')
                        / محاضرة
                        <span class="font-medium text-12px text-gray">(المدة مطلوبة — التاريخ اختياري)</span>
                    @endif
                </span>
                <span class="icon-[tabler--chevron-down] size-4 text-gray"></span>
            </summary>
            <form method="POST" action="{{ $unit['session_store_url'] ?? route('panel.v1.instructor.curriculum.sessions.store') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 px-4 pb-4" data-curriculum-ajax="session"
                data-session-mode="{{ $courseTypeKey === 'live' ? 'live' : 'recorded' }}">
                @csrf
                <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
                <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                <input type="hidden" name="date" value="" data-session-datetime>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block font-medium text-12px text-gray mb-1.5">عنوان الجلسة</label>
                    <input type="text" name="topic" required placeholder="عنوان الجلسة" class="{{ $input }}" data-field-label="عنوان الجلسة">
                </div>
                <div>
                    <label class="block font-medium text-12px text-gray mb-1.5">
                        تاريخ الجلسة
                        @if ($courseTypeKey === 'live') <span class="text-red-500">*</span> @endif
                    </label>
                    <input type="date" name="session_date"
                        {{ $courseTypeKey === 'live' ? 'required' : '' }}
                        class="{{ $input }}" data-field-label="تاريخ الجلسة" data-session-date>
                </div>
                <div>
                    <label class="block font-medium text-12px text-gray mb-1.5">
                        وقت البدء
                        @if ($courseTypeKey === 'live') <span class="text-red-500">*</span> @endif
                    </label>
                    <input type="time" name="session_time"
                        {{ $courseTypeKey === 'live' ? 'required' : '' }}
                        class="{{ $input }}" data-field-label="وقت الجلسة" data-session-time>
                </div>
                <div>
                    <label class="block font-medium text-12px text-gray mb-1.5">المدة (دقيقة) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration" required min="1" placeholder="90" class="{{ $input }}" data-field-label="مدة الجلسة">
                </div>
                <div class="{{ $courseTypeKey === 'live' ? 'sm:col-span-2' : 'hidden' }}">
                    <label class="block font-medium text-12px text-gray mb-1.5">
                        رابط الاجتماع
                        @if ($courseTypeKey === 'live') <span class="text-red-500">*</span> @endif
                    </label>
                    <input type="url" name="link" placeholder="https://meet.google.com/... أو Zoom"
                        {{ $courseTypeKey === 'live' ? 'required' : '' }}
                        class="{{ $input }}" data-field-label="رابط الاجتماع" data-session-link
                        {{ $courseTypeKey === 'live' ? '' : 'disabled' }}>
                    <p class="mt-1.5 font-medium text-12px text-gray">رابط Zoom / Google Meet / Microsoft Teams أو أي منصة مباشرة</p>
                </div>
                <div class="flex items-end {{ $courseTypeKey === 'live' ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                    <button type="submit" class="inline-flex items-center justify-center w-full h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-bold text-15px hover:opacity-90 transition">إضافة</button>
                </div>
            </form>
            <p class="hidden px-4 pb-3 font-medium text-13px text-[#B91C1C]" data-curriculum-form-error></p>
        </details>
        <details class="rounded-10px border border-d9" @if($openFile) open @endif>
            <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary list-none flex items-center justify-between gap-2">
                <span>+ إضافة ملف</span>
                <span class="icon-[tabler--chevron-down] size-4 text-gray"></span>
            </summary>
            <form method="POST" action="{{ $unit['file_store_url'] ?? route('panel.v1.instructor.curriculum.files.store') }}" enctype="multipart/form-data"
                class="grid grid-cols-1 gap-3 px-4 pb-4" data-curriculum-ajax="file">
                @csrf
                <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
                <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                <input type="text" name="title" required placeholder="عنوان الملف" class="{{ $input }}" data-field-label="عنوان الملف">
                @include('panel_v1.components.file-upload', [
                    'name' => 'upload',
                    'accept' => 'video/*,image/*,.pdf,.doc,.docx,.zip',
                    'label' => 'اختر الملف من جهازك',
                    'hint' => 'فيديو بلا حد حجم من التطبيق (حسب مساحة السيرفر)، صورة، PDF أو مستند',
                    'required' => true,
                    'compact' => true,
                ])
                <div class="hidden rounded-10px border border-d9 bg-[#FAFAF4] px-4 py-3" data-upload-progress>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <span class="font-semibold text-13px text-primary" data-upload-progress-label>جاري الرفع...</span>
                        <span class="font-bold text-13px text-primary tabular-nums" data-upload-progress-percent>0%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-white overflow-hidden border border-d9">
                        <div class="h-full rounded-full bg-primary transition-[width] duration-150" style="width: 0%" data-upload-progress-bar></div>
                    </div>
                    <p class="mt-2 font-medium text-12px text-gray" data-upload-progress-meta></p>
                </div>
                <button type="submit" class="inline-flex items-center justify-center h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-bold text-15px hover:opacity-90 transition">رفع</button>
            </form>
            <p class="hidden px-4 pb-3 font-medium text-13px text-[#B91C1C]" data-curriculum-form-error></p>
        </details>
        <details class="rounded-10px border border-d9" @if($openText) open @endif>
            <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary list-none flex items-center justify-between gap-2">
                <span>+ إضافة درس نصي</span>
                <span class="icon-[tabler--chevron-down] size-4 text-gray"></span>
            </summary>
            <form method="POST" action="{{ $unit['text_store_url'] ?? route('panel.v1.instructor.curriculum.texts.store') }}"
                class="grid grid-cols-1 gap-3 px-4 pb-4" data-curriculum-ajax="text">
                @csrf
                <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
                <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                <input type="text" name="title" required placeholder="عنوان الدرس" class="{{ $input }}" data-field-label="عنوان الدرس">
                <textarea name="summary" rows="3" placeholder="ملخص الدرس" class="{{ $input }}" data-field-label="ملخص الدرس"></textarea>
                <button type="submit" class="inline-flex items-center justify-center h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-bold text-15px hover:opacity-90 transition">إضافة</button>
            </form>
            <p class="hidden px-4 pb-3 font-medium text-13px text-[#B91C1C]" data-curriculum-form-error></p>
        </details>
    </div>
</div>
