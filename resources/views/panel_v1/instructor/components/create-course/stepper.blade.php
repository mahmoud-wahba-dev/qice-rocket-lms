@php
    $step = (int) ($wizardStep ?? 1);
    $steps = $wizardSteps ?? [];
@endphp

<nav class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 mb-6 sm:mb-8" aria-label="خطوات إنشاء الدورة" data-wizard-stepper>
    @foreach ($steps as $num => $item)
        @php
            $done = $num < $step;
            $active = $num === $step;
        @endphp
        <button type="button" data-wizard-goto="{{ $num }}"
            class="flex items-center gap-2.5 sm:gap-3 rounded-12px border px-3 sm:px-4 py-3 sm:py-3.5 transition text-start
                {{ $active ? 'border-[#C99C69]/50 bg-[#F7F0E6]' : 'border-d9 bg-white hover:border-primary/30' }}">
            <span data-step-badge
                class="size-8 sm:size-9 shrink-0 rounded-full center font-bold text-14px sm:text-15px {{ ($active || $done) ? 'bg-primary text-white' : 'bg-[#EDEDED] text-gray' }}">
                @if ($done)
                    <span class="icon-[tabler--check] size-4 sm:size-5"></span>
                @else
                    {{ $num }}
                @endif
            </span>
            <span data-step-label
                class="font-semibold text-13px sm:text-15px leading-snug {{ ($active || $done) ? 'text-primary' : 'text-gray' }}">
                {{ $item['label'] }}
            </span>
        </button>
    @endforeach
</nav>
