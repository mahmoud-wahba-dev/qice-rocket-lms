@php
    $input = $input ?? 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $draftIdValue = $draftIdValue ?? '';
    $isTemplate = !empty($isTemplate);
@endphp

<div class="rounded-14px border border-d9 overflow-hidden mb-4 last:mb-0" data-curriculum-unit="{{ $unit['id'] }}">
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-4 bg-[#FAFAF4] border-b border-d9">
        <div class="flex items-center gap-3 min-w-0">
            <span class="icon-[tabler--grip-vertical] size-5 text-gray shrink-0"></span>
            <div class="min-w-0 text-start">
                <p class="font-bold text-16px sm:text-17px text-primary truncate" data-unit-title>{{ $unit['title'] }}</p>
                <p class="font-medium text-13px text-gray"><span data-unit-lesson-count>{{ count($unit['lessons'] ?? []) }}</span> دروس</p>
            </div>
        </div>
        <form method="POST"
            action="{{ $unit['delete_url'] ?? route('panel.v1.instructor.curriculum.chapters.delete', ['chapterId' => $unit['id']]) }}"
            data-curriculum-ajax="delete-chapter"
            data-confirm="حذف الوحدة بكل محتوياتها؟">
            @csrf
            <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
            <button type="submit" class="size-9 rounded-8px center text-red-500 hover:bg-red-50 transition" aria-label="حذف الوحدة">
                <span class="icon-[tabler--trash] size-5"></span>
            </button>
        </form>
    </div>

    <div class="divide-y divide-d9" data-curriculum-lessons>
        @forelse ($unit['lessons'] ?? [] as $lesson)
            <div class="flex flex-wrap items-center gap-3 px-4 sm:px-5 py-3.5" data-curriculum-lesson="{{ $lesson['id'] }}" data-lesson-kind="{{ $lesson['kind'] }}">
                <span class="size-9 rounded-8px bg-primary/10 center shrink-0">
                    @if (($lesson['kind'] ?? '') === 'text')
                        <span class="icon-[tabler--file-text] size-4 text-primary"></span>
                    @elseif (($lesson['kind'] ?? '') === 'file')
                        <span class="icon-[tabler--paperclip] size-4 text-primary"></span>
                    @else
                        <span class="icon-[tabler--player-play] size-4 text-primary"></span>
                    @endif
                </span>
                <div class="min-w-0 flex-1 text-start">
                    <p class="font-semibold text-15px sm:text-16px text-primary truncate">{{ $lesson['title'] }}</p>
                    <p class="font-medium text-13px text-gray">{{ $lesson['duration'] }}</p>
                </div>
                <form method="POST"
                    action="{{ $lesson['delete_url'] ?? '#' }}"
                    data-curriculum-ajax="delete-lesson"
                    data-confirm="حذف هذا العنصر؟">
                    @csrf
                    <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
                    <button type="submit" class="size-8 rounded-8px center text-red-500 hover:bg-red-50" aria-label="حذف">
                        <span class="icon-[tabler--trash] size-4"></span>
                    </button>
                </form>
            </div>
        @empty
            <p class="font-medium text-14px text-gray px-4 sm:px-5 py-4" data-lessons-empty>لا يوجد محتوى بعد — أضف جلسة أو ملفًا أو درسًا نصيًا.</p>
        @endforelse
    </div>

    <div class="px-4 sm:px-5 py-4 border-t border-d9 bg-white space-y-4">
        <details class="rounded-10px border border-d9" open>
            <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary list-none flex items-center justify-between gap-2">
                <span>+ إضافة جلسة</span>
                <span class="icon-[tabler--chevron-down] size-4 text-gray"></span>
            </summary>
            <form method="POST" action="{{ $unit['session_store_url'] ?? route('panel.v1.instructor.curriculum.sessions.store') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 px-4 pb-4" data-curriculum-ajax="session">
                @csrf
                <input type="hidden" name="draft_id" value="{{ $draftIdValue }}" data-draft-id-input>
                <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                <input type="text" name="topic" required placeholder="عنوان الجلسة" class="{{ $input }}" data-field-label="عنوان الجلسة">
                <input type="datetime-local" name="date" required class="{{ $input }}" data-field-label="تاريخ الجلسة">
                <input type="number" name="duration" required min="1" placeholder="المدة (دقيقة)" class="{{ $input }}" data-field-label="مدة الجلسة">
                <button type="submit" class="inline-flex items-center justify-center h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-bold text-15px hover:opacity-90 transition">إضافة</button>
            </form>
            <p class="hidden px-4 pb-3 font-medium text-13px text-[#B91C1C]" data-curriculum-form-error></p>
        </details>
        <details class="rounded-10px border border-d9">
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
                    'hint' => 'فيديو، صورة، PDF أو مستند',
                    'required' => true,
                    'compact' => true,
                ])
                <button type="submit" class="inline-flex items-center justify-center h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-bold text-15px hover:opacity-90 transition">رفع</button>
            </form>
            <p class="hidden px-4 pb-3 font-medium text-13px text-[#B91C1C]" data-curriculum-form-error></p>
        </details>
        <details class="rounded-10px border border-d9">
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
