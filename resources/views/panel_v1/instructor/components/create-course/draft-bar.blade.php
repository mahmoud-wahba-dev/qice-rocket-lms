@php
    $step = (int) ($wizardStep ?? 1);
    $steps = $wizardSteps ?? [];
    $meta = $steps[$step] ?? ['label' => '', 'title' => '', 'next' => 'التالي', 'prev' => 'السابق', 'progress' => 20];
    $progress = (int) ($meta['progress'] ?? 20);
    $overallPct = (int) round((($step - 1) / 5) * 100);
@endphp

<div class="border border-d9 rounded-14px bg-white overflow-hidden mb-5 sm:mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4">
        <div class="flex items-center gap-3 min-w-0">
            <h1 class="font-semibold text-18px sm:text-20px text-primary truncate">
                {{ $draftTitle ?? 'دورة تدريبية بدون عنوان' }}
            </h1>
            <span class="shrink-0 inline-flex items-center rounded-full bg-[#C99C69]/25 px-3 py-1 font-semibold text-13px text-[#8B6914]">
                مسودة
            </span>
        </div>
        <div class="flex items-center gap-3 sm:gap-4">
            <p class="font-medium text-14px sm:text-15px text-gray whitespace-nowrap">اكتمال البيانات {{ $progress }}%</p>
            <button type="button"
                class="inline-flex items-center gap-2 font-semibold text-15px text-primary hover:opacity-80 transition">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                حفظ كمسودة
            </button>
        </div>
    </div>
    <div class="h-1.5 w-full bg-[#EEEDE8]">
        <div class="h-full bg-primary transition-all duration-300" style="width: {{ $progress }}%"></div>
    </div>
</div>
