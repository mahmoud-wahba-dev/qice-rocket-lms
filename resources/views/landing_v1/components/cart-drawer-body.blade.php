@php
    $courseItems = $cartItems->whereNotNull('webinar_id');
    $bundleItems = $cartItems->whereNotNull('bundle_id');
    $otherItems = $cartItems->filter(function ($cart) {
        return empty($cart->webinar_id) && empty($cart->bundle_id);
    });
@endphp

<div class="cart-drawer-items">
    @if ($courseItems->isNotEmpty())
        <p class="cart-drawer-section-label">الدورات</p>
        <div class="cart-drawer-list">
            @foreach ($courseItems as $cart)
                @php
                    $itemInfo = $cart->getItemInfo();
                    $cartItemId = $cart->getId();
                    $itemUrl = !empty($cart->webinar)
                        ? route('landing.v1.course-details', $cart->webinar->slug)
                        : ($itemInfo['itemUrl'] ?? '#');
                    $displayPrice = !empty($itemInfo['discountPrice'])
                        ? $itemInfo['discountPrice']
                        : ($itemInfo['price'] ?? \App\Models\Cart::getItemPrice($cart));
                @endphp

                @if ($cartItemId && !empty($itemInfo['title']))
                    <article class="cart-drawer-item" data-cart-item-id="{{ $cartItemId }}">
                        <a href="{{ $itemUrl }}" class="cart-drawer-item__thumb">
                            <img src="{{ $itemInfo['imgPath'] ?? asset('assets/landing_v1/img/contact/hero.webp') }}"
                                alt="{{ $itemInfo['title'] }}" loading="lazy">
                        </a>
                        <div class="cart-drawer-item__body">
                            <a href="{{ $itemUrl }}" class="cart-drawer-item__title">{{ $itemInfo['title'] }}</a>
                            @if (!empty($itemInfo['teacherName']))
                                <p class="cart-drawer-item__meta">{{ $itemInfo['teacherName'] }}</p>
                            @endif
                            <div class="cart-drawer-item__footer">
                                <div class="cart-drawer-item__price">
                                    <span class="cart-drawer-item__price-current">{!! handlePrice($displayPrice) !!}</span>
                                    @if (!empty($itemInfo['discountPrice']) && !empty($itemInfo['price']))
                                        <span class="cart-drawer-item__price-old">{!! handlePrice($itemInfo['price']) !!}</span>
                                    @endif
                                </div>
                                <button type="button" class="cart-drawer-item__remove" data-cart-remove="{{ $cartItemId }}"
                                    aria-label="إزالة من السلة">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    <span>حذف</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @endif

    @if ($bundleItems->isNotEmpty())
        <p class="cart-drawer-section-label {{ $courseItems->isNotEmpty() ? 'mt-5' : '' }}">الباقات</p>
        <div class="cart-drawer-list">
            @foreach ($bundleItems as $cart)
                @php
                    $itemInfo = $cart->getItemInfo();
                    $cartItemId = $cart->getId();
                    $displayPrice = !empty($itemInfo['discountPrice'])
                        ? $itemInfo['discountPrice']
                        : ($itemInfo['price'] ?? \App\Models\Cart::getItemPrice($cart));
                @endphp

                @if ($cartItemId && !empty($itemInfo['title']))
                    <article class="cart-drawer-item" data-cart-item-id="{{ $cartItemId }}">
                        <a href="{{ $itemInfo['itemUrl'] ?? '#' }}" class="cart-drawer-item__thumb">
                            <img src="{{ $itemInfo['imgPath'] ?? asset('assets/landing_v1/img/contact/hero.webp') }}"
                                alt="{{ $itemInfo['title'] }}" loading="lazy">
                        </a>
                        <div class="cart-drawer-item__body">
                            <a href="{{ $itemInfo['itemUrl'] ?? '#' }}" class="cart-drawer-item__title">{{ $itemInfo['title'] }}</a>
                            <div class="cart-drawer-item__footer">
                                <div class="cart-drawer-item__price">
                                    <span class="cart-drawer-item__price-current">{!! handlePrice($displayPrice) !!}</span>
                                </div>
                                <button type="button" class="cart-drawer-item__remove" data-cart-remove="{{ $cartItemId }}"
                                    aria-label="إزالة من السلة">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    <span>حذف</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @endif

    @if ($otherItems->isNotEmpty())
        <p class="cart-drawer-section-label {{ $courseItems->isNotEmpty() || $bundleItems->isNotEmpty() ? 'mt-5' : '' }}">عناصر أخرى</p>
        <div class="cart-drawer-list">
            @foreach ($otherItems as $cart)
                @php
                    $itemInfo = $cart->getItemInfo();
                    $cartItemId = $cart->getId();
                    $displayPrice = \App\Models\Cart::getItemPrice($cart);
                @endphp

                @if ($cartItemId && !empty($itemInfo['title']))
                    <article class="cart-drawer-item" data-cart-item-id="{{ $cartItemId }}">
                        <a href="{{ $itemInfo['itemUrl'] ?? '#' }}" class="cart-drawer-item__thumb">
                            <img src="{{ $itemInfo['imgPath'] ?? asset('assets/landing_v1/img/contact/hero.webp') }}"
                                alt="{{ $itemInfo['title'] }}" loading="lazy">
                        </a>
                        <div class="cart-drawer-item__body">
                            <a href="{{ $itemInfo['itemUrl'] ?? '#' }}" class="cart-drawer-item__title">{{ $itemInfo['title'] }}</a>
                            <div class="cart-drawer-item__footer">
                                <div class="cart-drawer-item__price">
                                    <span class="cart-drawer-item__price-current">{!! handlePrice($displayPrice) !!}</span>
                                </div>
                                <button type="button" class="cart-drawer-item__remove" data-cart-remove="{{ $cartItemId }}"
                                    aria-label="إزالة من السلة">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    <span>حذف</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @endif
</div>
