@php
    $title = $title ?? '';
    $cta = $cta ?? null;
    $ctaHref = $ctaHref ?? null;
    $noDataImg = ($panelStudentImg ?? asset('assets/panel_v1/img/student')) . '/no-data.webp';
@endphp

<div class="border border-d9 rounded-20px bg-white px-6 py-14 center flex-col text-center">
    <img src="{{ $noDataImg }}" alt="" class="w-40 max-w-full mb-6" loading="lazy" decoding="async">
    <p class="font-semibold text-20px sm:text-24px text-gray">{{ $title }}</p>
    @if ($cta)
        <a href="{{ $ctaHref ?? '#' }}"
            class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px mt-6">
            {{ $cta }}
        </a>
    @endif
</div>
