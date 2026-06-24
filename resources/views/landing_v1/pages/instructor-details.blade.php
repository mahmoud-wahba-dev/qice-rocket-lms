@extends('landing_v1.layouts.app')

@section('content')
    <main>
        <x-landing_v1::sec-header
            :title="$instructor->full_name"
            :subtitle="$instructor->headline ?? ''"
        />

        <section class="mb-8 lg:mb-14 mt-0">
            <div class="container">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mb-16">
                    <div class="lg:col-span-4">
                        <div class="bg-white border border-[#0000001A] rounded-19px overflow-hidden">
                            <div class="h-72 overflow-hidden">
                                <img class="h-full w-full object-cover" src="{{ $instructor->getAvatar() }}"
                                    alt="{{ $instructor->full_name }}">
                            </div>
                            <div class="p-6">
                                <h1 class="font-semibold text-28px text-primary mb-2">{{ $instructor->full_name }}</h1>
                                @if (!empty($instructor->headline))
                                    <p class="font-medium text-16px text-primary/80 mb-4">{{ $instructor->headline }}</p>
                                @endif
                                <div class="flex flex-wrap gap-4 text-14px text-primary/70 border-t border-card-border pt-4">
                                    <span>{{ $instructor->courses_count }} دورة</span>
                                    <span>{{ number_format($instructor->students_count) }} متدرب</span>
                                    @php($instructorRating = is_array($instructor->rating ?? null) ? ($instructor->rating['rate'] ?? 0) : ($instructor->rating ?? 0))
                                    @if (!empty($instructorRating) && $instructorRating > 0)
                                        <span>{{ $instructorRating }} تقييم</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-8">
                        @if (!empty($instructor->about) || !empty($instructor->bio))
                            <div class="bg-white border border-[#0000001A] rounded-19px p-8">
                                <h2 class="font-bold text-24px text-primary mb-4">نبذة عن المدرب</h2>
                                <div class="font-normal text-16px text-primary/80 leading-relaxed">
                                    {!! nl2br(e($instructor->about ?? $instructor->bio)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <h2 class="font-bold text-32px text-primary mb-8">دورات المدرب</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 xl:gap-8">
                    @forelse ($courses as $course)
                        @if ($course->price > 0)
                            <x-landing_v1::course-card
                                :title="$course->title"
                                :description="$course->description"
                                :teacherName="$course->teacher->full_name ?? ''"
                                :teacherAvatar="!empty($course->teacher) ? $course->teacher->getAvatar() : null"
                                :price="handlePrice($course->price)"
                                :image="$course->image_cover ?? $course->thumbnail ?? asset('assets/landing_v1/img/home/course.webp')"
                                :categoryTitle="$course->category->title ?? ''"
                                :slug="$course->slug"
                            />
                        @else
                            <x-landing_v1::workshop-card
                                :title="$course->title"
                                :summary="$course->summary ?? $course->description"
                                :categoryTitle="$course->category->title ?? ''"
                                :slug="$course->slug"
                            />
                        @endif
                    @empty
                        <p class="col-span-full text-center font-medium text-primary/70 py-12">لا توجد دورات لهذا المدرب حالياً.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <x-landing_v1::prefooter-cta />
    </main>
@endsection
