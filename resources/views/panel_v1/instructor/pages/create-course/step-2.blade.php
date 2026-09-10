@php
    $input = 'input input-bordered w-full h-12 sm:h-14 rounded-10px border-d9 font-medium text-15px sm:text-16px text-black focus:outline-none focus:border-primary';
    $card = 'border border-d9 rounded-14px bg-white px-5 sm:px-7 py-6 sm:py-8';
    $draftIdValue = $draftId ?? request('draft');
@endphp

{{-- Curriculum --}}
<section class="{{ $card }}">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5 sm:mb-6">
        <div class="flex items-center gap-3">
            <span class="size-10 rounded-10px bg-primary/10 center shrink-0">
                <span class="icon-[tabler--books] size-5 text-primary"></span>
            </span>
            <h2 class="font-bold text-18px sm:text-20px text-primary">بناء المنهج الدراسي</h2>
        </div>
    </div>

    @if (!empty($draftIdValue))
        <form method="POST" action="{{ route('panel.v1.instructor.curriculum.chapters.store') }}"
            class="flex flex-col sm:flex-row gap-3 mb-6">
            @csrf
            <input type="hidden" name="draft_id" value="{{ $draftIdValue }}">
            <input type="text" name="title" required placeholder="عنوان وحدة جديدة (مثال: الوحدة الأولى)"
                class="{{ $input }} flex-1">
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 h-12 sm:h-14 px-5 rounded-10px bg-primary text-white font-semibold text-15px hover:opacity-90 transition shrink-0">
                <span class="icon-[tabler--plus] size-4"></span>
                إضافة وحدة
            </button>
        </form>
    @else
        <div class="rounded-12px bg-[#FAFAF4] border border-d9 px-5 py-4 mb-6">
            <p class="font-medium text-14px text-gray">احفظ بيانات الخطوة الأولى أولاً لتتمكن من بناء المنهج.</p>
        </div>
    @endif

    @forelse ($curriculumUnits ?? [] as $unit)
        <div class="rounded-14px border border-d9 overflow-hidden mb-4 last:mb-0">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-4 bg-[#FAFAF4] border-b border-d9">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="icon-[tabler--grip-vertical] size-5 text-gray shrink-0"></span>
                    <div class="min-w-0 text-start">
                        <p class="font-bold text-16px sm:text-17px text-primary truncate">{{ $unit['title'] }}</p>
                        <p class="font-medium text-13px text-gray">{{ count($unit['lessons'] ?? []) }} دروس</p>
                    </div>
                </div>
                <form method="POST"
                    action="{{ route('panel.v1.instructor.curriculum.chapters.delete', ['chapterId' => $unit['id']]) }}"
                    onsubmit="return confirm('حذف الوحدة بكل محتوياتها؟');">
                    @csrf
                    <input type="hidden" name="draft_id" value="{{ $draftIdValue }}">
                    <button type="submit" class="size-9 rounded-8px center text-red-500 hover:bg-red-50 transition" aria-label="حذف الوحدة">
                        <span class="icon-[tabler--trash] size-5"></span>
                    </button>
                </form>
            </div>

            <div class="divide-y divide-d9">
                @forelse ($unit['lessons'] ?? [] as $lesson)
                    <div class="flex flex-wrap items-center gap-3 px-4 sm:px-5 py-3.5">
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
                            action="{{ ($lesson['kind'] ?? '') === 'file' ? route('panel.v1.instructor.curriculum.files.delete', ['fileId' => $lesson['id']]) : ((($lesson['kind'] ?? '') === 'text') ? route('panel.v1.instructor.curriculum.texts.delete', ['textId' => $lesson['id']]) : route('panel.v1.instructor.curriculum.sessions.delete', ['sessionId' => $lesson['id']])) }}"
                            onsubmit="return confirm('حذف هذا العنصر؟');">
                            @csrf
                            <input type="hidden" name="draft_id" value="{{ $draftIdValue }}">
                            <button type="submit" class="size-8 rounded-8px center text-red-500 hover:bg-red-50" aria-label="حذف">
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="font-medium text-14px text-gray px-4 sm:px-5 py-4">لا يوجد محتوى بعد — أضف جلسة أو ملفًا أو درسًا نصيًا.</p>
                @endforelse
            </div>

            <div class="px-4 sm:px-5 py-4 border-t border-d9 bg-white space-y-4">
                <details class="rounded-10px border border-d9">
                    <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary">+ إضافة جلسة</summary>
                    <form method="POST" action="{{ route('panel.v1.instructor.curriculum.sessions.store') }}"
                        class="grid grid-cols-1 sm:grid-cols-4 gap-3 px-4 pb-4">
                        @csrf
                        <input type="hidden" name="draft_id" value="{{ $draftIdValue }}">
                        <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                        <input type="text" name="topic" required placeholder="عنوان الجلسة" class="{{ $input }}">
                        <input type="datetime-local" name="date" required class="{{ $input }}">
                        <input type="number" name="duration" required min="1" placeholder="المدة (دقيقة)" class="{{ $input }}">
                        <button type="submit" class="btn btn-primary rounded-10px h-12 font-bold text-15px">إضافة</button>
                    </form>
                </details>
                <details class="rounded-10px border border-d9">
                    <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary">+ إضافة ملف</summary>
                    <form method="POST" action="{{ route('panel.v1.instructor.curriculum.files.store') }}" enctype="multipart/form-data"
                        class="grid grid-cols-1 sm:grid-cols-3 gap-3 px-4 pb-4">
                        @csrf
                        <input type="hidden" name="draft_id" value="{{ $draftIdValue }}">
                        <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                        <input type="text" name="title" required placeholder="عنوان الملف" class="{{ $input }}">
                        <input type="file" name="upload" required class="{{ $input }}">
                        <button type="submit" class="btn btn-primary rounded-10px h-12 font-bold text-15px">رفع</button>
                    </form>
                </details>
                <details class="rounded-10px border border-d9">
                    <summary class="cursor-pointer px-4 py-3 font-semibold text-14px text-primary">+ إضافة درس نصي</summary>
                    <form method="POST" action="{{ route('panel.v1.instructor.curriculum.texts.store') }}"
                        class="grid grid-cols-1 gap-3 px-4 pb-4">
                        @csrf
                        <input type="hidden" name="draft_id" value="{{ $draftIdValue }}">
                        <input type="hidden" name="chapter_id" value="{{ $unit['id'] }}">
                        <input type="text" name="title" required placeholder="عنوان الدرس" class="{{ $input }}">
                        <textarea name="summary" rows="3" placeholder="ملخص الدرس" class="{{ $input }}"></textarea>
                        <button type="submit" class="btn btn-primary rounded-10px h-12 font-bold text-15px">إضافة</button>
                    </form>
                </details>
            </div>
        </div>
    @empty
        <div class="rounded-14px border border-dashed border-d9 px-6 py-10 center flex-col text-center">
            <p class="font-semibold text-18px text-gray">لا توجد وحدات بعد</p>
            <p class="font-medium text-14px text-gray mt-2">أضف أول وحدة من الأعلى لبدء بناء المنهج.</p>
        </div>
    @endforelse
</section>
