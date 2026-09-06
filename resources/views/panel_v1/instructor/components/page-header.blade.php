{{-- Shared instructor page header — matches home typography/spacing --}}
@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="font-semibold text-24px text-black mb-1">{{ $title }}</h1>
        @if ($subtitle)
            <p class="font-medium text-16px text-gray">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
