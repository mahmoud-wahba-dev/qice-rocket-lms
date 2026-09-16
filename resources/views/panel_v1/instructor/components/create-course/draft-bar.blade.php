@php
    $step = (int) ($wizardStep ?? 1);
    $steps = $wizardSteps ?? [];
    $meta = $steps[$step] ?? ['label' => '', 'title' => '', 'next' => 'التالي', 'prev' => 'السابق', 'progress' => 20];
    $progress = (int) ($meta['progress'] ?? 20);
@endphp

<div class="border border-d9 rounded-14px bg-white overflow-hidden mb-5 sm:mb-6" data-wizard-draft-bar>
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4">
        <div class="flex items-center gap-3 min-w-0">
            <h1 class="font-semibold text-18px sm:text-20px text-primary truncate" data-draft-title>
                {{ $draftTitle ?? 'دورة تدريبية بدون عنوان' }}
            </h1>
            <span class="shrink-0 inline-flex items-center rounded-full bg-[#C99C69]/25 px-3 py-1 font-semibold text-13px text-[#8B6914]">
                مسودة
            </span>
            <span class="hidden sm:inline font-medium text-12px text-gray" data-autosave-status></span>
        </div>
        <div class="flex items-center gap-3 sm:gap-4">
            <p class="font-medium text-14px sm:text-15px text-gray whitespace-nowrap">اكتمال البيانات <span data-draft-progress-pct>{{ $progress }}</span>%</p>
            <button type="button" data-wizard-save
                class="inline-flex items-center gap-2 font-semibold text-15px text-primary hover:opacity-80 transition">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                حفظ كمسودة
            </button>
        </div>
    </div>
    <div class="h-1.5 w-full bg-[#EEEDE8]">
        <div class="h-full bg-primary transition-all duration-300" data-draft-progress-bar style="width: {{ $progress }}%"></div>
    </div>
</div>
