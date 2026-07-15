@props([
    'name' => '',
    'avatar' => null,
    'bio' => '',
    'username' => null,
    'profileUrl' => null,
])

@php($landingImg = asset('assets/landing_v1/img'))
@php($avatarUrl = $avatar ?? ($landingImg . '/home/instructor.webp'))
@php($detailUrl = $profileUrl ?? (!empty($username) ? route('landing.v1.instructor-details', $username) : route('landing.v1.instructors')))

<div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
    {{-- Aspect-ratio box: uniform image area that scales with the card. Change aspect-[4/3] (e.g. aspect-square, aspect-[4/5]) --}}
    <a href="{{ $detailUrl }}" class="block w-full aspect-[4/3] overflow-hidden rounded-t-[19px] bg-[#f5f6f8]">
        <img class="block h-full w-full object-cover object-top"
            src="{{ $avatarUrl }}" alt="{{ $name }}" width="400" height="300" loading="lazy"
            decoding="async">
    </a>
    {{-- To shrink the overall card, reduce padding (p-3) and bio lines (line-clamp-2) here --}}
    <div class="p-4">
        <a href="{{ $detailUrl }}">
            <h6 class="font-semibold text-24px text-blue mb-1 hover:text-primary transition-colors">{{ $name }}</h6>
        </a>
        @if (!empty($bio))
            <p class="font-normal text-14px text-blue line-clamp-2">{{ $bio }}</p>
        @endif
    </div>
</div>
