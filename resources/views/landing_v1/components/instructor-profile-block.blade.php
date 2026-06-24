@props([
    'teacher' => null,
    'coursesCount' => 0,
    'studentsCount' => 0,
    'rating' => 0,
    'variant' => 'light',
])

@php($landingImg = asset('assets/landing_v1/img'))
@php($isDark = $variant === 'dark')
@php($textClass = $isDark ? 'text-white' : 'text-primary')
@php($borderClass = $isDark ? 'border-white' : 'border-primary')
@php($cardClass = $isDark ? 'bg-[#165554] text-white' : 'bg-white')
@php($btnClass = $isDark ? 'btn-white text-[#165554]' : 'btn-primary')
@php($displayRating = is_array($rating) ? ($rating['rate'] ?? 0) : $rating)

@if (!empty($teacher))
    <div id="course-instructor" class="course-scrollspy-section mb-16 {{ $isDark ? 'bg-[#165554] text-white' : '' }}">
        <h3 class="font-medium text-32px lg:text-36px {{ $textClass }} mb-6">عن المدرب</h3>
        <div
            class="border {{ $borderClass }} flex flex-col md:flex-row items-start gap-10 px-6 lg:px-8 py-8 lg:py-11 rounded-8px {{ $cardClass }} shadow-sm">
            <div class="w-[160px] h-[160px] rounded-8px overflow-hidden shrink-0 border border-gray-100">
                <img src="{{ $teacher->getAvatar(160) }}" alt="{{ $teacher->full_name }}"
                    class="w-full h-full object-cover">
            </div>
            <div class="flex-grow">
                <h6 class="font-semibold text-24px {{ $textClass }} mb-2">{{ $teacher->full_name }}</h6>
                @if (!empty($teacher->headline) || !empty($teacher->about) || !empty($teacher->bio))
                    <p class="font-normal text-18px {{ $textClass }} mb-6">
                        {{ $teacher->headline ?? Str::limit(strip_tags($teacher->about ?? $teacher->bio), 120) }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-6 mb-11 border-t border-b border-gray-100 py-4">
                    @if (!empty($teacher->created_at))
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--calendar] size-5 shrink-0 {{ $textClass }}"></span>
                            <div class="font-medium text-10px {{ $textClass }} flex flex-col">
                                <span>عضو منذ</span>
                                <span>{{ dateTimeFormat($teacher->created_at, 'Y') }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--video] size-5 shrink-0 {{ $textClass }}"></span>
                        <div class="font-medium text-10px {{ $textClass }} flex flex-col">
                            <span>عدد الدورات</span>
                            <span>{{ $coursesCount }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="icon-[tabler--users] size-5 shrink-0 {{ $textClass }}"></span>
                        <div class="font-medium text-10px {{ $textClass }} flex flex-col">
                            <span>عدد الطلاب</span>
                            <span>{{ number_format($studentsCount) }}</span>
                        </div>
                    </div>
                    @if ($displayRating > 0)
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--star-filled] size-5 shrink-0 text-[#FFAA00]"></span>
                            <div class="font-medium text-10px {{ $textClass }} flex flex-col">
                                <span>التقييم</span>
                                <span>{{ $displayRating }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if (!empty($teacher->username))
                    <a href="{{ route('landing.v1.instructor-details', $teacher->username) }}"
                        class="btn {{ $btnClass }} h-12 lg:w-[40%] rounded-4px font-medium text-14px px-8 inline-flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                        <span class="icon-[tabler--user] size-5"></span>
                        عرض الملف الشخصي
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
