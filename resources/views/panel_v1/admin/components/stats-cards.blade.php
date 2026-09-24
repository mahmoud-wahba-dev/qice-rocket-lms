@props(['stats' => []])

{{-- Figma stats tile: 150px, vertical center — gold icon, value, label --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
    @foreach ($stats as $stat)
        <div class="rounded-14px bg-primary h-[150px] px-4 flex flex-col items-center justify-center text-center gap-2.5 shadow-sm">
            <span class="{{ $stat['icon'] ?? 'icon-[tabler--school]' }} size-6 text-color2 shrink-0"></span>
            <p class="font-bold text-22px sm:text-26px leading-none text-[#F5E6C8]">{{ $stat['value'] }}</p>
            <p class="font-medium text-13px sm:text-14px text-white leading-snug max-w-full px-1">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>
