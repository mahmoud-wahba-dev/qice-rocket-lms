<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-x-10 lg:gap-y-13">
    @forelse ($courses as $course)
        <x-landing_v1::course-card 
            title="{{ $course->title }}" 
            description="{{ $course->description }}" 
            teacherName="{{ $course->teacher->full_name ?? '' }}" 
            teacherAvatar="{{ !empty($course->teacher) ? $course->teacher->getAvatar() : '' }}" 
            price="{{ ($course->price > 0) ? handlePrice($course->price) : 'مجاناً' }}" 
            image="{{ $course->image_cover ?? $course->thumbnail ?? asset('assets/landing_v1/img/home/course.webp') }}" 
            categoryTitle="{{ $course->category->title ?? '' }}"
            slug="{{ $course->slug }}"
        />
    @empty
        <div class="col-span-full text-center py-20 bg-gray-50 rounded-8px border border-dashed border-gray-300 flex flex-col items-center justify-center">
            <span class="icon-[tabler--info-circle] text-gray-400 size-16 mb-4"></span>
            <h3 class="font-bold text-24px text-primary mb-2">لا توجد دورات متاحة</h3>
            <p class="text-gray-500 text-16px font-medium">لم نجد أي دورات تطابق الفلاتر المحددة حالياً.</p>
        </div>
    @endforelse
</div>
