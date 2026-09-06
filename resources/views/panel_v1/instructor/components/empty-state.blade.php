@php
    $title = $title ?? '';
    $noDataImg = ($panelInstructorImg ?? asset('assets/panel_v1/img/instructor')) . '/no-data.webp';
@endphp

<div class="border border-d9 rounded-20px bg-white px-6 py-14 center flex-col text-center">
    <img src="{{ $noDataImg }}" alt="" class="w-40 max-w-full mb-6" loading="lazy">
    <p class="font-semibold text-20px text-gray">{{ $title }}</p>
</div>
