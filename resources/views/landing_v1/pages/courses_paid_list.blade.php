<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
    @forelse ($courses as $course)
        <x-landing_v1::course-card
            :title="$course->title"
            :description="$course->description"
            :teacherName="$course->teacher->full_name ?? ''"
            :teacherAvatar="!empty($course->teacher) ? $course->teacher->getAvatar() : null"
            :price="($course->price > 0) ? handlePrice($course->price) : 'مجاناً'"
            :image="$course->image_cover ?? $course->thumbnail ?? asset('assets/landing_v1/img/home/course.webp')"
            :categoryTitle="$course->category->title ?? ''"
            :slug="$course->slug"
        />
    @empty
        <p class="col-span-full text-center font-medium text-primary/70 py-12">
            @if (!empty($activeCategory))
                لا توجد دورات في هذا التصنيف حالياً.
            @else
                لا توجد دورات معتمدة متاحة حالياً.
            @endif
        </p>
    @endforelse
</div>

@if ($courses instanceof \Illuminate\Pagination\AbstractPaginator && $courses->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $courses->withQueryString()->links() }}
    </div>
@endif
