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
    <a href="{{ $detailUrl }}" class="block h-52 overflow-hidden rounded-t-[19px]">
        <img class="h-full w-full object-contain"
            src="{{ $avatarUrl }}" alt="{{ $name }}">
    </a>
    <div class="p-6">
        <a href="{{ $detailUrl }}">
            <h6 class="font-semibold text-24px text-blue mb-3 hover:text-primary transition-colors">{{ $name }}</h6>
        </a>
        @if (!empty($bio))
            <p class="font-normal text-14px text-blue line-clamp-4">{{ $bio }}</p>
        @endif
    </div>
</div>
