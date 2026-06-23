{{--
    Landing V1 — Cart Drawer Body (FlyonUI / Tailwind)
    Rendered by CartManagerController::getDrawerInfo() via AJAX.
    Each item exposes [data-cart-item-id] so the JS can count & bind remove handlers.
--}}

@forelse($cartItems as $cartItem)
    @php
        $info       = $cartItem->getItemInfo();
        $uid        = $cartItem->uid ?? $cartItem->id;
        $title      = $info['title']        ?? '—';
        $cover      = $info['imgPath']     ?? null;
        $price      = $info['price']        ?? 0;
        $discountPrice = $info['discountPrice'] ?? null;
        $isProduct  = !empty($info['isProduct']);
        $cartTaxType = $isProduct ? 'store' : 'general';
        $displayPrice = $discountPrice
            ? handlePrice($discountPrice, true, true, false, null, true, $cartTaxType)
            : handlePrice($price,         true, true, false, null, true, $cartTaxType);
        $oldPrice = $discountPrice
            ? handlePrice($price, true, true, false, null, true, $cartTaxType)
            : null;
    @endphp

    <div class="flex gap-3 items-start p-4 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors duration-150"
         data-cart-item-id="{{ $uid }}">

        {{-- Thumbnail --}}
        <div class="w-16 h-16 rounded-8px overflow-hidden shrink-0 bg-gray-100">
            @if($cover)
                <img src="{{ $cover }}" alt="{{ $title }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-primary/20">
                    <span class="icon-[tabler--photo] size-7"></span>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="flex-grow min-w-0">
            <p class="font-bold text-13px text-primary leading-tight line-clamp-2 mb-1">{{ $title }}</p>

            <div class="flex items-center gap-2">
                <span class="font-bold text-14px text-secondary">{!! $displayPrice !!}</span>
                @if($oldPrice)
                    <span class="font-medium text-12px text-primary/40 line-through">{!! $oldPrice !!}</span>
                @endif
            </div>
        </div>

        {{-- Remove --}}
        <button type="button"
                class="shrink-0 text-primary/30 hover:text-red-500 transition-colors duration-150 mt-0.5"
                data-cart-remove="{{ $uid }}"
                aria-label="حذف">
            <span class="icon-[tabler--trash] size-4"></span>
        </button>
    </div>
@empty
    {{-- Fallback empty (shouldn't normally show; controller renders empty.blade for empty carts) --}}
    <div class="flex flex-col items-center justify-center h-48 gap-2 text-primary/30">
        <span class="icon-[tabler--shopping-cart-off] size-10"></span>
        <p class="font-medium text-14px">السلة فارغة</p>
    </div>
@endforelse
