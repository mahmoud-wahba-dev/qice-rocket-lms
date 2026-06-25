@props([
    'title' => '',
    'description' => '',
    'teacherName' => '',
    'teacherAvatar' => null,
    'price' => '',
    'image' => null,
    'buttonText' => 'سجّل الآن',
    'slug' => null,
    'categoryTitle' => '',
])

@php($landingImg = asset('assets/landing_v1/img'))
@php($cardImage = $image ?? $landingImg . '/home/course.webp')
@php($detailUrl = !empty($slug) ? route('landing.v1.course-details', $slug) : route('landing.v1.courses-paid'))

<div class="bg-white  rounded-19px  border border-[#E0D4BC] h-full">
    <div class="h-52  overflow-hidden">
        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]" src="{{ $cardImage }}"
            alt="{{ $title }}">
    </div>

    <div class="p-6 pt-2 pb-4">
        <h6 class="font-semibold text-20px text-primary mb-2 line-clamp-2">{{ $title }}</h6>
        @if (!empty($categoryTitle))
            <p class="font-normal text-12px text-7a mb-2">{{ $categoryTitle }}</p>
        @endif
        @if (!empty($description))
            <p class="font-normal text-13px text-7a mb-3 line-clamp-2">
                {{ Str::limit(strip_tags(html_entity_decode($description)), 80) }}
            </p>
        @endif
        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
            <div class="avatar">
                <div class="size-10 rounded-full">
                    <img src="{{ !empty($teacherAvatar) ? $teacherAvatar : 'https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png' }}"
                        alt="{{ $teacherName }}" />
                </div>
            </div>
            <p class="font-medium text-77 text-base">{{ $teacherName }}</p>
        </div>

        <div class="flex items-center justify-between">
            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                {{ $price }}
            </span>
            <a href="{{ $detailUrl }}"
                class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">{{ $buttonText }}</a>
        </div>
    </div>
</div>
