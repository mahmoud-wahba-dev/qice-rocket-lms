@props(['stats' => []])

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
    @foreach ($stats as $stat)
        <div class="rounded-14px bg-primary text-white px-5 py-5 flex items-center justify-between gap-3 shadow-sm">
            <div class="min-w-0 text-start">
                <p class="font-bold text-22px sm:text-24px leading-none mb-2">{{ $stat['value'] }}</p>
                <p class="font-medium text-14px text-white/85 truncate">{{ $stat['label'] }}</p>
            </div>
            <span class="size-11 rounded-full bg-white/15 center shrink-0">
                <span class="{{ $stat['icon'] ?? 'icon-[tabler--chart-bar]' }} size-5"></span>
            </span>
        </div>
    @endforeach
</div>
