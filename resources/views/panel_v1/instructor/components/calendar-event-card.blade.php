@php
    $event = $event ?? [];
    $past = !empty($event['is_past']);
@endphp
<article class="flex items-start justify-between gap-3 rounded-12px border border-d9 {{ $past ? 'bg-[#F8FAFC] opacity-90' : 'bg-[#FAFAF4]' }} p-4">
    <div class="flex items-start gap-3 min-w-0">
        <span class="size-11 rounded-10px bg-white border border-d9 center shrink-0">
            <span class="{{ $event['icon'] ?? 'icon-[tabler--calendar]' }} size-5 {{ $past ? 'text-gray' : 'text-primary' }}"></span>
        </span>
        <div class="min-w-0 text-start">
            <p class="font-semibold text-13px {{ $past ? 'text-gray' : 'text-color2' }} mb-0.5">{{ $event['type_label'] ?? '' }}</p>
            <h3 class="font-bold text-15px text-primary leading-snug">{{ $event['title'] ?? '' }}</h3>
            <p class="font-medium text-13px text-gray mt-1">{{ $event['subtitle'] ?? '' }}</p>
        </div>
    </div>
    <div class="flex flex-col items-end gap-2 shrink-0">
        @if (!empty($event['time_label']))
            <span class="inline-flex rounded-8px bg-white border border-d9 px-2.5 py-1 font-semibold text-12px text-primary whitespace-nowrap">
                {{ $event['time_label'] }}
            </span>
        @endif
        <div class="flex items-center gap-1.5">
            @if ($past)
                <span class="inline-flex items-center h-8 px-3 rounded-8px bg-[#FEF2F2] text-[#DC2626] font-semibold text-12px">انتهت</span>
            @endif
            @if (!$past && !empty($event['calendar_url']))
                <a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener"
                    class="size-8 rounded-full bg-white border border-d9 center hover:bg-fa transition" title="إضافة للتقويم">
                    <span class="icon-[tabler--bell] size-4 text-gray"></span>
                </a>
            @endif
        </div>
    </div>
</article>
