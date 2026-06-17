@props([
    'title',
    'description',
    'teacherName',
    'teacherAvatar' => null,
    'price',
    'image',
    'buttonText' => 'اشترك الان',
    'slug' => null,
])

<div class="rounded-8px border border-[#0000002B] overflow-hidden bg-white text-card-text">
    <div class="h-48 overflow-hidden">
        <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover rounded-tl-[8px] rounded-tr-[8px]">
    </div>
    <div class="px-4 py-6">
        <h6 class="font-semibold text-24px text-primary mb-1.5 text-card-text line-clamp-2">{{ $title }}</h6>
        <p class="font-normal text-14px mb-5 text-primary text-card-text line-clamp-3">{{ strip_tags(html_entity_decode($description)) }}</p>
        <div class="flex items-center gap-2 mb-5 ">
            <div class="avatar">
                <div class="size-10 rounded-full">
                    <img src="{{ !empty($teacherAvatar) ? $teacherAvatar : 'https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png' }}" alt="avatar" />
                </div>
            </div>
            <p class="font-medium text-77 text-base">{{ $teacherName }}</p>
        </div>
        <div class="flex items-center justify-between">
            <span class="font-bold text-22px text-card-text">{{ $price }}</span>
            <a href="{{ !empty($slug) ? route('landing.v1.course-details', $slug) : route('landing.v1.course-details') }}" class="btn btn-primary h-10 font-medium text-14px px-7">
                {{ $buttonText }}
            </a>
        </div>
    </div>
</div>
