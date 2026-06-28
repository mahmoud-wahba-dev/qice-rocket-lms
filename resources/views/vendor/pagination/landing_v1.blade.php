@if ($paginator->hasPages())
    <nav class="mt-10 flex w-full flex-col items-center gap-4" aria-label="Pagination">
        @if ($paginator->total() > 0)
            <p class="m-0 text-14px font-medium text-primary/60">
                عرض {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} من {{ $paginator->total() }}
            </p>
        @endif

        <ul class="m-0 flex list-none flex-row flex-wrap items-center justify-center gap-2 p-0">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="inline-flex min-h-10 min-w-10 cursor-default items-center justify-center rounded-full border border-[#ECECEC] bg-[#F8F8F8] px-3 text-primary/30"
                        aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="icon icon-[tabler--chevron-right] size-5" aria-hidden="true"></span>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}"
                        class="landing-pagination__link inline-flex min-h-10 min-w-10 items-center justify-center rounded-full border border-[#E5E5E5] bg-white px-3 text-primary transition-colors hover:border-primary/35 hover:bg-primary/5"
                        rel="prev" aria-label="@lang('pagination.previous')">
                        <span class="icon icon-[tabler--chevron-right] size-5" aria-hidden="true"></span>
                    </a>
                </li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="inline-flex min-h-10 min-w-10 items-center justify-center text-14px font-medium text-primary/40"
                            aria-hidden="true">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="inline-flex min-h-10 min-w-10 cursor-default items-center justify-center rounded-full border border-primary bg-primary px-3.5 text-14px font-semibold text-white"
                                    aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}"
                                    class="landing-pagination__link inline-flex min-h-10 min-w-10 items-center justify-center rounded-full border border-[#E5E5E5] bg-white px-3.5 text-14px font-semibold text-primary transition-colors hover:border-primary/35 hover:bg-primary/5">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}"
                        class="landing-pagination__link inline-flex min-h-10 min-w-10 items-center justify-center rounded-full border border-[#E5E5E5] bg-white px-3 text-primary transition-colors hover:border-primary/35 hover:bg-primary/5"
                        rel="next" aria-label="@lang('pagination.next')">
                        <span class="icon icon-[tabler--chevron-left] size-5" aria-hidden="true"></span>
                    </a>
                </li>
            @else
                <li>
                    <span class="inline-flex min-h-10 min-w-10 cursor-default items-center justify-center rounded-full border border-[#ECECEC] bg-[#F8F8F8] px-3 text-primary/30"
                        aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="icon icon-[tabler--chevron-left] size-5" aria-hidden="true"></span>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
