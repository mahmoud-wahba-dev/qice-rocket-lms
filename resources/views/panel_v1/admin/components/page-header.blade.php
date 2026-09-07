@props([
    'title' => '',
    'subtitle' => '',
])

<div class="flex flex-wrap items-start justify-between gap-4 mb-6 sm:mb-8">
    <div class="min-w-0 text-start">
        <h1 class="font-semibold text-26px sm:text-30px text-primary mb-2">{{ $title }}</h1>
        @if ($subtitle !== '')
            <p class="font-medium text-15px sm:text-16px text-gray leading-relaxed max-w-3xl">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="shrink-0">{{ $actions }}</div>
    @endisset
</div>
