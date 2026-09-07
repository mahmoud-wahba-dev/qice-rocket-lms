@php
    $step = (int) ($wizardStep ?? 1);
    $steps = $wizardSteps ?? [];
    $meta = $steps[$step] ?? [];
    $progress = (int) ($meta['progress'] ?? 20);
    $prevStep = $step > 1 ? $step - 1 : null;
    $nextStep = $step < 5 ? $step + 1 : null;
@endphp

<div class="sticky bottom-0 z-30 -mx-4 sm:-mx-6 lg:-mx-8 mt-8 border-t border-d9 bg-white/95 backdrop-blur px-4 sm:px-6 lg:px-8 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-3 sm:gap-4 order-2 sm:order-1">
            <a href="{{ route('panel.v1.instructor.courses') }}"
                class="font-semibold text-15px sm:text-16px text-red-500 hover:opacity-80 transition">
                إلغاء
            </a>
            <button type="button"
                class="inline-flex items-center gap-2 font-semibold text-15px sm:text-16px text-primary hover:opacity-80 transition">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                حفظ المسودة
            </button>
        </div>

        <p class="font-medium text-14px sm:text-15px text-gray order-1 sm:order-2 w-full sm:w-auto text-center">
            الخطوة {{ $step }} من 5 — {{ $progress }}% مكتمل
        </p>

        <div class="flex items-center gap-2.5 sm:gap-3 order-3 ms-auto sm:ms-0">
            @if ($prevStep)
                <a href="{{ route('panel.v1.instructor.courses.create', ['step' => $prevStep]) }}"
                    class="inline-flex items-center gap-1.5 h-11 sm:h-12 px-4 sm:px-5 rounded-10px border border-d9 bg-white font-semibold text-15px sm:text-16px text-primary hover:bg-[#FAFAF4] transition">
                    <span class="icon-[tabler--chevron-right] size-5"></span>
                    السابق
                </a>
            @else
                <button type="button" disabled
                    class="inline-flex items-center gap-1.5 h-11 sm:h-12 px-4 sm:px-5 rounded-10px border border-d9 bg-[#F5F5F5] font-semibold text-15px sm:text-16px text-gray cursor-not-allowed opacity-60">
                    <span class="icon-[tabler--chevron-right] size-5"></span>
                    السابق
                </button>
            @endif

            @if ($nextStep)
                <a href="{{ route('panel.v1.instructor.courses.create', ['step' => $nextStep]) }}"
                    class="inline-flex items-center gap-2 h-11 sm:h-12 px-5 sm:px-6 rounded-10px bg-primary text-white font-semibold text-15px sm:text-16px hover:opacity-90 transition">
                    {{ $meta['next'] ?? 'التالي' }}
                    <span class="icon-[tabler--chevron-left] size-5"></span>
                </a>
            @else
                <button type="button"
                    class="inline-flex items-center gap-2 h-11 sm:h-12 px-5 sm:px-6 rounded-10px bg-primary text-white font-semibold text-15px sm:text-16px hover:opacity-90 transition">
                    {{ $meta['next'] ?? 'إرسال للمراجعة' }}
                    <span class="icon-[tabler--send] size-5"></span>
                </button>
            @endif
        </div>
    </div>
</div>
