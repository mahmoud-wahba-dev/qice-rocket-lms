@php
    $step = (int) ($wizardStep ?? 1);
    $steps = $wizardSteps ?? [];
@endphp

<nav class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 mb-6 sm:mb-8" aria-label="خطوات إنشاء الدورة">
    @foreach ($steps as $num => $item)
        @php
            $done = $num < $step;
            $active = $num === $step;
            $href = route('panel.v1.instructor.courses.create', ['step' => $num]);
        @endphp
        <a href="{{ $href }}"
            class="flex items-center gap-2.5 sm:gap-3 rounded-12px border px-3 sm:px-4 py-3 sm:py-3.5 transition
                {{ $active ? 'border-[#C99C69]/50 bg-[#F7F0E6]' : 'border-d9 bg-white hover:border-primary/30' }}">
            <span @class([
                'size-8 sm:size-9 shrink-0 rounded-full center font-bold text-14px sm:text-15px',
                'bg-primary text-white' => $active || $done,
                'bg-[#EDEDED] text-gray' => ! $active && ! $done,
            ])>
                @if ($done)
                    <span class="icon-[tabler--check] size-4 sm:size-5"></span>
                @else
                    {{ $num }}
                @endif
            </span>
            <span @class([
                'font-semibold text-13px sm:text-15px leading-snug text-start',
                'text-primary' => $active || $done,
                'text-gray' => ! $active && ! $done,
            ])>
                {{ $item['label'] }}
            </span>
        </a>
    @endforeach
</nav>
