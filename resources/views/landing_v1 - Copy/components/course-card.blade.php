@php
/**
 * Blade component view for landing_v1::course-card
 * Variables:
 *   $title, $description, $teacherName, $teacherAvatar, $price, $image, $slug
 */
@endphp
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <a href="{{ route('landing.v1.course-details', $slug) }}" class="block">
        <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-48 object-cover">
        <div class="p-4">
            <h3 class="text-lg font-semibold text-primary mb-2">{{ $title }}</h3>
            <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $description }}</p>
            <div class="flex items-center mb-2">
                @if($teacherAvatar)
                    <img src="{{ $teacherAvatar }}" alt="{{ $teacherName }}" class="w-6 h-6 rounded-full mr-2">
                @endif
                <span class="text-sm text-gray-800">{{ $teacherName }}</span>
            </div>
            <div class="text-primary font-bold">{{ $price }}</div>
        </div>
    </a>
</div>
