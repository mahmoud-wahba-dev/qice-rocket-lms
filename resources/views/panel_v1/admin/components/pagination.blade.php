@props(['pagination' => ['from' => 1, 'to' => 10, 'total' => 0]])

<div class="flex flex-wrap items-center justify-between gap-3 pt-4">
    <p class="font-medium text-14px text-gray">
        عرض النتائج {{ $pagination['from'] }} إلى {{ $pagination['to'] }} من أصل {{ $pagination['total'] }}.
    </p>
    <div class="flex items-center gap-1.5">
        <button type="button" class="size-9 rounded-10px border border-d9 center text-gray hover:bg-white" aria-label="السابق">
            <span class="icon-[tabler--chevron-right] size-4"></span>
        </button>
        @foreach ([1, 2, 3, '…', 20, 21] as $page)
            @if ($page === '…')
                <span class="px-1 text-gray">…</span>
            @else
                <button type="button"
                    class="size-9 rounded-10px font-semibold text-14px {{ $page === 1 ? 'bg-primary text-white' : 'border border-d9 text-primary hover:bg-white' }}">
                    {{ $page }}
                </button>
            @endif
        @endforeach
        <button type="button" class="size-9 rounded-10px border border-d9 center text-gray hover:bg-white" aria-label="التالي">
            <span class="icon-[tabler--chevron-left] size-4"></span>
        </button>
    </div>
</div>
