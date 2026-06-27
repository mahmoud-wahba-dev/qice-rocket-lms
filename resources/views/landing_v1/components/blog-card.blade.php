@props([
    'title' => '',
    'image' => null,
    'slug' => null,
])

@php($landingImg = asset('assets/landing_v1/img'))
@php($cardImage = $image ?? ($landingImg . '/home/news1-opt.webp'))
@php($detailUrl = !empty($slug) ? route('landing.v1.blog-details', $slug) : route('landing.v1.blogs'))

<a href="{{ $detailUrl }}"
    class="h-[396px] max-sm:h-[200px] bg-e3 rounded-20px p-7 flex flex-col items-center justify-end text-center relative overflow-hidden block"
    style="background-image: url('{{ $cardImage }}'); background-size: cover; background-position: center;">
    <p class="font-bold text-22px text-white z-20 relative line-clamp-3">{{ $title }}</p>
    <div class="layer bg-linear absolute inset-0 rounded-20px"></div>
</a>
