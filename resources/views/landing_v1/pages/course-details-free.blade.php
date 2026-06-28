@extends('landing_v1.layouts.app')

@section('content')
    @php
        $landingImg = asset('assets/landing_v1/img');
        $courseDetailsImg = asset('assets/landing_v1/img/course_details');
        $learningOutcomes = $learningOutcomes ?? [];
        $discoveryTopics = $discoveryTopics ?? [];
        $faqItems = $faqItems ?? [];
        $curriculumModules = $curriculumModules ?? [];
        $comments = $comments ?? [];
        $ratesDistribution = $ratesDistribution ?? [
            5 => ['percent' => 0],
            4 => ['percent' => 0],
            3 => ['percent' => 0],
            2 => ['percent' => 0],
            1 => ['percent' => 0],
        ];
        $hasUserBought = $hasUserBought ?? false;
        $averageRating = $averageRating ?? 0;
        $totalReviewsCount = $totalReviewsCount ?? 0;
        $sessionCount = $course->chapters->sum(fn($chapter) => $chapter->sessions->count());
        $heroDaysLabel = !empty($course->access_days)
            ? $course->access_days . ' أيام تفاعلية'
            : ($course->chapters->count() > 0
                ? $course->chapters->count() . ' وحدات تدريبية'
                : '7 أيام تفاعلية');
        $heroHoursLabel = !empty($course->duration)
            ? convertMinutesToHourAndMinute($course->duration) . ' ' . trans('home.hours') . ' تدريبية معتمدة'
            : ($sessionCount > 0
                ? $sessionCount . ' جلسة تدريبية'
                : '35 ساعة تدريبية معتمدة');
        $courseTeaser = $course->seo_description ?? ($course->summary ?? Str::limit(strip_tags(html_entity_decode($course->description)), 160));
        $courseAboutText = $course->summary ?? Str::limit(strip_tags(html_entity_decode($course->description)), 300);
    @endphp

    <main>

        {{-- Hero --}}
        <header class="bg-[#F5EFE5] text-primary py-10 lg:py-16">
            <div class="container">
                <div class="grid grid-cols-1 lg:grid-cols-12 lg:gap-16 gap-8 items-center">
                    <div class="lg:col-span-7">
                        <p class="font-semibold text-20px text-primary mb-12">دورة مجانية تماماً</p>
                        <h1 class="font-bold  xl:text-64px mb-6 text-primary leading-tight xl:w-[90%]">
                            {{ $course->title }}
                        </h1>
                        <p class="font-normal text-18px  text-primary mb-12 leading-relaxed max-w-2xl">
                            {{ $courseTeaser }}
                        </p>

                        <div class="flex items-center gap-3 lg:gap-4 flex-wrap xl:flex-nowrap mb-10">
                            <div
                                class="flex items-center gap-2 px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/calendar.webp" alt="calendar" class="size-6 shrink-0">
                                <span class="font-semibold text-14px text-primary whitespace-nowrap">{{ $heroDaysLabel }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/clock.webp" alt="clock" class="size-6 shrink-0">
                                <span class="font-medium text-14px text-primary whitespace-nowrap">{{ $heroHoursLabel }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/waiting-room.webp" alt="waiting-room"
                                    class="size-6 shrink-0">
                                <span class="font-medium text-14px text-primary whitespace-nowrap">حضور مَرِن (عن بُعد /
                                    حضوري)</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            @if ($hasUserBought)
                                <a href="{{ $course->getLearningPageUrl() }}"
                                    class="btn w-[70%] btn-primary h-15 rounded-9px font-bold text-18px px-10 whitespace-nowrap hover:shadow-lg transition-all duration-300 inline-flex items-center justify-center gap-3">
                                    ابدأ التعلم
                                    <img src="{{ $courseDetailsImg }}/seat.webp" alt="" class="size-6 shrink-0">
                                </a>
                            @else
                                <form class="w-[70%]" action="/course/{{ $course->slug }}/free" method="get">
                                    <button type="submit"
                                        class="btn w-full btn-primary h-15 rounded-9px  font-bold text-18px px-10  whitespace-nowrap  hover:shadow-lg transition-all duration-300">
                                        سجل مقعدك الآن
                                        <img src="{{ $courseDetailsImg }}/seat.webp" alt="user-plus" class="size-6 shrink-0">
                                    </button>
                                </form>
                            @endif
                            <p class="font-bold text-18px text-primary">الدورة مجانية تماماً</p>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        <x-landing_v1::workshop-card :title="$course->title" :summary="$courseTeaser" :categoryTitle="$course->category->title ?? ''"
                            :slug="$course->slug" buttonUrl="#about-course" />
                    </div>
                </div>
            </div>
        </header>

        <section class="my-0 course_details_page relative bg-white">
            <div class="sticky top-[88px] z-40 bg-[#F5EFE5] border-b border-[#B4B4B4]">
                <div class="container">
                    <nav data-scrollspy="#course-content-scrollspy"
                        class="course-scrollspy-nav course-scrollspy-nav--free" style="--scrollspy-offset: 168px">
                        <a href="#about-course" class="course-scrollspy-link" aria-current="false">
                            نبذة عن الدورة
                        </a>
                        <a href="#course-curriculum" class="course-scrollspy-link" aria-current="false">
                            محتوى الدورة
                        </a>
                        @if (!empty($course->teacher))
                            <a href="#course-instructor" class="course-scrollspy-link" aria-current="false">
                                عن المدرب
                            </a>
                        @endif
                        @if (!empty($discoveryTopics))
                            <a href="#course-discovery" class="course-scrollspy-link" aria-current="false">
                                ماذا سنكتشف معاً؟
                            </a>
                        @endif
                        @if (!empty($faqItems))
                            <a href="#course-faq" class="course-scrollspy-link" aria-current="false">
                                إجابات لاستفساراتك
                            </a>
                        @endif
                    </nav>
                </div>
            </div>

            <div class="container py-8 lg:py-12">

                <div id="course-content-scrollspy">

                    {{-- About --}}
                    <div id="about-course" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-36px text-primary mb-10">نبذة عن الدورة</h3>
                        <div class="border border-primary px-6 lg:px-8 py-8 lg:py-11 rounded-10px bg-white shadow-sm">
                            <p class="font-normal lg:text-24px text-primary leading-relaxed">
                                {{ $courseAboutText }}
                            </p>
                        </div>
                    </div>

                    {{-- Curriculum --}}
                    <div id="course-curriculum" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">محتوى الدورة</h3>
                        <div class="border border-primary px-6 lg:px-8 py-2 lg:py-4 rounded-10px bg-white shadow-sm">
                            @if (!empty($course->description))
                                <div
                                    class="course-description py-5 font-normal text-18px lg:text-24px text-primary leading-relaxed [&_p]:mb-4 [&_ul]:list-disc [&_ul]:list-inside [&_ol]:list-decimal [&_ol]:list-inside [&_a]:text-primary [&_a]:underline">
                                    {!! clean($course->description) !!}
                                </div>
                            @else
                                <div class="py-5 font-medium text-18px lg:text-24px text-primary/70">لا يوجد وصف لهذه
                                    الدورة بعد.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Instructor --}}
                    <x-landing_v1::instructor-profile-block :teacher="$course->teacher" :coursesCount="$teacher_courses_count"
                        :studentsCount="$teacher_students_count" :rating="$teacher_rating" variant="light" />

                    {{-- Comments --}}
                    {{-- <div id="course-comments" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">التعليقات</h3>
                        <div class="border border-primary px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm">
                            <div class="space-y-0 divide-y divide-[#EEEEEE]">
                                @forelse ($comments as $comment)
                                    <div class="flex gap-4 py-6 first:pt-0 last:pb-0">
                                        <div class="size-12 rounded-full bg-[#F5F1E8] center shrink-0 overflow-hidden">
                                            <span class="icon-[tabler--user] size-6 text-primary"></span>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span
                                                    class="font-bold text-16px text-primary">{{ $comment['name'] }}</span>
                                                <span class="text-7a text-14px">·</span>
                                                <span class="font-normal text-14px text-7a">{{ $comment['date'] }}</span>
                                            </div>
                                            <p class="font-normal text-16px text-primary/80 leading-relaxed">
                                                {{ $comment['body'] }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="font-normal text-16px text-primary/70 text-center py-4">لا توجد تعليقات متاحة
                                    </p>
                                @endforelse
                            </div>

                            <div class="mt-8 pt-6 border-t border-[#E7E7E7]">
                                <label class="font-medium text-16px text-primary mb-3 block">أضف تعليقاً</label>
                                <textarea
                                    class="textarea textarea-bordered w-full border-[#CFCFCF] rounded-8px text-primary bg-secondary/30 resize-none"
                                    rows="3" placeholder="شاركنا رأيك في هذه الدورة..." disabled></textarea>
                                <button type="button"
                                    class="btn btn-primary mt-4 h-11 rounded-4px font-medium text-14px px-6" disabled>
                                    نشر التعليق
                                </button>
                            </div>
                        </div>
                    </div> --}}

                    {{-- Reviews --}}
                    {{-- <div id="course-reviews" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">المراجعات</h3>
                        <div
                            class="border border-primary px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm flex flex-col md:flex-row justify-center items-center gap-8 lg:gap-12">
                            <div
                                class="w-[140px] h-[140px] bg-primary/5 rounded-10px shrink-0 flex flex-col items-center justify-center text-primary border border-primary/10">
                                <span
                                    class="font-bold text-48px leading-none mb-1 text-primary">{{ $averageRating }}</span>
                                <span class="font-semibold text-10px text-primary/60">{{ $totalReviewsCount }}
                                    تقييم</span>
                            </div>

                            <div class="flex flex-col gap-3.5 w-full max-w-[320px]">
                                @foreach ($ratesDistribution as $stars => $dist)
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-1 w-[80px] justify-end">
                                            @for ($j = 1; $j <= 5; $j++)
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
                                                        fill="{{ $j <= $stars ? '#FFAA00' : '#E5E7EB' }}" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <div class="h-2 flex-grow bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#FFAA00] rounded-full transition-all duration-500"
                                                style="width: {{ $dist['percent'] }}%"></div>
                                        </div>
                                        <span
                                            class="font-bold text-12px text-primary/70 w-8">{{ $dist['percent'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div> --}}

                </div>
            </div>

            {{-- Discovery topics (مواد التعلم) --}}
            @if (!empty($discoveryTopics))
                <div id="course-discovery"
                    class="course-scrollspy-section mb-0 bg-[#F5EFE5] px-3.5 sm:px-0 py-12 lg:py-16 rounded-10px lg:rounded-none">
                    <div class="container">
                        <div class="text-center mb-10">
                            <h3 class="font-bold text-32px text-primary mb-3">ماذا سنكتشف معاً؟</h3>
                        </div>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5 max-w-5xl mx-auto list-none p-0 m-0">
                            @foreach ($discoveryTopics as $topic)
                                <li
                                    class="group flex items-start gap-4 rounded-10px border border-primary/20 bg-white px-5 py-4 lg:px-6 lg:py-5 shadow-sm transition-colors duration-200 hover:border-primary/50 hover:bg-white">
                                    <span
                                        class="flex shrink-0 size-9 lg:size-10 items-center justify-center rounded-full bg-primary/10 text-primary ring-1 ring-primary/20">
                                        <span class="icon-[tabler--sparkles] size-5 lg:size-[22px]"></span>
                                    </span>
                                    <span
                                        class="font-medium text-16px lg:text-20px text-primary leading-relaxed pt-1.5">
                                        {{ $topic }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </section>

        {{-- FAQ (التعليمات from admin) --}}
        @if (!empty($faqItems))
            <section id="course-faq" class="course-scrollspy-section bg-[#F5EFE5] py-16 lg:py-24 my-0">
                <div class="container">
                    <h3 class="font-bold text-32px lg:text-48px text-primary mb-13 text-start">إجابات لاستفساراتك</h3>
                    <div class="accordion accordion-shadow border border-primary rounded-10px overflow-hidden">
                        @foreach ($faqItems as $index => $faq)
                            <div class="accordion-item {{ $index === 0 ? 'active' : '' }} bg-transparent"
                                id="free-faq-{{ $index + 1 }}">
                                <button
                                    class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-5 lg:px-8 {{ $index % 2 === 0 ? 'font-normal text-18px lg:text-32px' : 'font-semibold text-18px lg:text-22px' }} text-primary py-6 {{ $index === 0 ? 'border-b border-[#B4B4B4]' : 'border-t border-[#E7E7E7]' }} text-start w-full hover:bg-gray-50 transition-colors"
                                    aria-controls="free-faq-{{ $index + 1 }}-collapse"
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                    <span
                                        class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                                    <span
                                        class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                                    {{ $faq['question'] }}
                                </button>
                                <div id="free-faq-{{ $index + 1 }}-collapse"
                                    class="accordion-content {{ $index === 0 ? '' : 'hidden' }} w-full overflow-hidden transition-[height] duration-300"
                                    aria-labelledby="free-faq-{{ $index + 1 }}" role="region">
                                    <div
                                        class="px-5 lg:px-8 pb-6 {{ $index === 0 ? 'border-t border-[#B4B4B4]' : 'border-t border-gray-100' }} pt-3">
                                        <div
                                            class="{{ $index === 0 ? 'text-primary font-normal text-24px' : 'text-primary/80 font-normal text-16px' }} leading-relaxed [&_p]:mb-3 last:[&_p]:mb-0">
                                            {!! clean($faq['answer']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <x-landing_v1::prefooter-cta />

    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.__courseScrollspyReady) return;
            if (typeof window.initCourseScrollspy === 'function') {
                window.initCourseScrollspy();
                return;
            }

            window.__courseScrollspyReady = true;
            var nav = document.querySelector('.course_details_page [data-scrollspy]');
            if (!nav) return;

            function getOffset() {
                var sticky = nav.closest('.sticky') || nav;
                var custom = getComputedStyle(nav).getPropertyValue('--scrollspy-offset').trim();
                if (custom) {
                    var parsed = parseInt(custom, 10);
                    if (!isNaN(parsed)) return parsed;
                }
                return 88 + sticky.getBoundingClientRect().height + 16;
            }

            function scrollY() {
                return window.pageYOffset || document.documentElement.scrollTop || 0;
            }

            document.addEventListener('click', function(e) {
                var link = e.target.closest('.course_details_page [data-scrollspy] a[href^="#"]');
                if (!link) return;
                var id = (link.getAttribute('href') || '').slice(1);
                var el = id ? document.getElementById(id) : null;
                if (!el) return;
                e.preventDefault();
                var top = el.getBoundingClientRect().top + scrollY() - getOffset();
                window.scrollTo({
                    top: Math.max(0, top),
                    behavior: 'smooth'
                });
                nav.querySelectorAll('a[href^="#"]').forEach(function(a) {
                    a.classList.toggle('active', a === link);
                    a.setAttribute('aria-current', a === link ? 'location' : 'false');
                });
            });

            window.addEventListener('scroll', function() {
                if (!nav) return;
                var marker = scrollY() + getOffset();
                var links = nav.querySelectorAll('a[href^="#"]');
                var current = null;
                links.forEach(function(link) {
                    var id = (link.getAttribute('href') || '').slice(1);
                    var el = id ? document.getElementById(id) : null;
                    if (el && el.getBoundingClientRect().top + scrollY() <= marker + 2) {
                        current = link;
                    }
                });
                if (current) {
                    links.forEach(function(a) {
                        a.classList.toggle('active', a === current);
                        a.setAttribute('aria-current', a === current ? 'location' : 'false');
                    });
                }
            }, {
                passive: true
            });
        });
    </script>
@endpush
