@props(['pagination' => null, 'paginator' => null])
@php
    $from = 0; $to = 0; $total = 0; $hasPaginator = false;
    if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
        $hasPaginator = true;
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator->total();
    } elseif (is_array($pagination)) {
        $from = $pagination['from'] ?? 1;
        $to = $pagination['to'] ?? 0;
        $total = $pagination['total'] ?? 0;
        if ($total===0 && isset($pagination['total'])) $total=$pagination['total'];
    } elseif ($pagination instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
        $hasPaginator = true;
        $paginator = $pagination;
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator->total();
    }
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 pt-4">
    <p class="font-medium text-14px text-gray">
        عرض النتائج {{ $from }} إلى {{ $to }} من أصل {{ $total }}.
    </p>
    @if($hasPaginator && $paginator->hasPages())
        <div class="flex items-center gap-1.5">
            @if($paginator->onFirstPage())
                <span class="size-9 rounded-10px border border-d9 center text-gray/40" aria-label="السابق"><span class="icon-[tabler--chevron-right] size-4"></span></span>
            @else
                <a href="{{ $paginator->appends(request()->query())->previousPageUrl() }}" class="size-9 rounded-10px border border-d9 center text-primary hover:bg-white" aria-label="السابق"><span class="icon-[tabler--chevron-right] size-4"></span></a>
            @endif
            @foreach ($paginator->getUrlRange(max(1,$paginator->currentPage()-2), min($paginator->lastPage(),$paginator->currentPage()+2)) as $page => $url)
                @if($page == $paginator->currentPage())
                    <span class="size-9 rounded-10px bg-primary text-white font-semibold text-14px center">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="size-9 rounded-10px border border-d9 text-primary hover:bg-white font-semibold text-14px center">{{ $page }}</a>
                @endif
            @endforeach
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->appends(request()->query())->nextPageUrl() }}" class="size-9 rounded-10px border border-d9 center text-primary hover:bg-white" aria-label="التالي"><span class="icon-[tabler--chevron-left] size-4"></span></a>
            @else
                <span class="size-9 rounded-10px border border-d9 center text-gray/40" aria-label="التالي"><span class="icon-[tabler--chevron-left] size-4"></span></span>
            @endif
        </div>
    @else
        <div class="flex items-center gap-1.5">
            <span class="size-9 rounded-10px border border-d9 center text-gray/40" aria-label="السابق"><span class="icon-[tabler--chevron-right] size-4"></span></span>
            <span class="size-9 rounded-10px bg-primary text-white font-semibold text-14px center">1</span>
            <span class="size-9 rounded-10px border border-d9 center text-gray/40" aria-label="التالي"><span class="icon-[tabler--chevron-left] size-4"></span></span>
        </div>
    @endif
</div>
