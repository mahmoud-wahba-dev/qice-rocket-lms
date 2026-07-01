@extends('landing_v1.layouts.app')

@section('content')
    @php
        $landingImg = asset('assets/landing_v1/img');
        $courseDetailsImg = asset('assets/landing_v1/img/course_details');
        $heroVideo = $heroVideo ?? [
            'type' => 'poster',
            'poster' => $course->getImage(),
            'hasVideo' => false,
            'hasControls' => false,
        ];
        $heroVideoCoverClass =
            'absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 min-h-full min-w-[177.78vh] w-[100vw] h-[56.25vw] border-0 pointer-events-auto object-cover';
        $isInCart = $isInCart ?? false;
        $hasUserBought = $hasUserBought ?? false;
        $averageRating = $averageRating ?? 0;
        $totalReviewsCount = $totalReviewsCount ?? 0;
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
        $effectivePrice = $course->getPrice();
        $hasDiscount = $effectivePrice < $course->price;
        $sessionCount = $course->chapters->sum(fn($chapter) => $chapter->sessions->count());
    @endphp

    <main>

        {{-- Hero: full-bleed video (left) + gradient fade into teal + text (right, RTL) --}}
        <header class="relative flex items-center overflow-hidden bg-[#155554] text-white min-h-[520px] lg:min-h-[680px]">
            <div id="course-hero-video" class="absolute inset-y-0 left-0 z-0 w-full sm:w-[72%] lg:w-[58%] overflow-hidden"
                data-video-type="{{ $heroVideo['type'] }}">
                @if ($heroVideo['type'] === 'youtube')
                    <iframe id="course-hero-youtube" class="{{ $heroVideoCoverClass }}" src="{{ $heroVideo['embedUrl'] }}"
                        title="{{ $course->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
                @elseif ($heroVideo['type'] === 'vimeo')
                    <iframe id="course-hero-vimeo" class="{{ $heroVideoCoverClass }}" src="{{ $heroVideo['embedUrl'] }}"
                        title="{{ $course->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
                @elseif ($heroVideo['type'] === 'bunny')
                    <iframe id="course-hero-bunny" class="{{ $heroVideoCoverClass }}" src="{{ $heroVideo['embedUrl'] }}"
                        title="{{ $course->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
                @elseif ($heroVideo['type'] === 'html5')
                    <video id="course-hero-html5" class="{{ $heroVideoCoverClass }}" autoplay muted loop playsinline
                        poster="{{ $heroVideo['poster'] ?? $course->getImage() }}">
                        <source src="{{ $heroVideo['videoUrl'] }}" type="video/mp4">
                    </video>
                @elseif ($heroVideo['type'] === 'raw')
                    <div class="course-hero-embed absolute inset-0">
                        {!! $heroVideo['rawHtml'] !!}
                    </div>
                @else
                    <img src="{{ $heroVideo['poster'] ?? $course->getImage() }}" alt="{{ $course->title }}"
                        class="absolute inset-0 size-full object-cover">
                @endif

                @if (!empty($heroVideo['hasControls']))
                    <div class="absolute bottom-6 left-6 z-20 flex items-center gap-2">
                        <button type="button" id="course-hero-video-toggle"
                            class="flex size-12 items-center justify-center rounded-full border border-white/40 bg-black/40 text-white backdrop-blur-sm transition hover:bg-black/55"
                            aria-label="إيقاف الفيديو" data-playing="true">
                            <span class="icon-[tabler--player-pause] size-6" data-icon-pause></span>
                            <span class="icon-[tabler--player-play] size-6 hidden" data-icon-play></span>
                        </button>
                        <button type="button" id="course-hero-video-mute"
                            class="flex size-12 items-center justify-center rounded-full border border-white/40 bg-black/40 text-white backdrop-blur-sm transition hover:bg-black/55"
                            aria-label="تشغيل الصوت" data-muted="true">
                            <span class="icon-[tabler--volume-off] size-6" data-icon-muted></span>
                            <span class="icon-[tabler--volume] size-6 hidden" data-icon-unmuted></span>
                        </button>
                    </div>
                @endif
            </div>

            <div class="absolute inset-0 z-[1] bg-gradient-to-r from-transparent from-5% via-[#155554]/60 via-40% to-[#155554] to-55% pointer-events-none sm:bg-gradient-to-r sm:from-transparent sm:from-10% sm:via-[#155554]/70 sm:via-45% sm:to-[#155554] sm:to-62%"
                aria-hidden="true"></div>

            <div class="container relative z-10 py-12 lg:py-20 pointer-events-none">
                <div class="lg:max-w-[55%] pointer-events-auto">
                    <p class="font-semibold text-base text-white mb-10">خطوتك الأولى نحو القمة...</p>
                    <h1 class="font-bold text-36px lg:text-46px text-[#F1F5F9] mb-8 leading-tight">
                        {{ $course->title }}
                    </h1>
                    <p class="font-normal text-16px lg:text-18px text-white mb-10 leading-relaxed max-w-2xl">
                        {{ $course->seo_description ?? ($course->summary ?? Str::limit(strip_tags(html_entity_decode($course->description)), 160)) }}
                    </p>

                    <div class="flex items-center gap-3 lg:gap-4 flex-wrap xl:flex-nowrap mb-10">
                        @if (!empty($course->access_days))
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/calendar.webp" alt="calendar" class="size-6 shrink-0">
                                <span
                                    class="font-semibold text-14px text-white whitespace-nowrap">{{ $course->access_days }}
                                    أيام تفاعلية</span>
                            </div>
                        @elseif ($course->chapters->count() > 0)
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/calendar.webp" alt="calendar" class="size-6 shrink-0">
                                <span
                                    class="font-semibold text-14px text-white whitespace-nowrap">{{ $course->chapters->count() }}
                                    وحدات تدريبية</span>
                            </div>
                        @endif
                        @if (!empty($course->duration))
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/clock.webp" alt="clock" class="size-6 shrink-0">
                                <span
                                    class="font-medium text-14px text-white whitespace-nowrap">{{ convertMinutesToHourAndMinute($course->duration) }}
                                    {{ trans('home.hours') }} تدريبية معتمدة</span>
                            </div>
                        @elseif ($sessionCount > 0)
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/clock.webp" alt="clock" class="size-6 shrink-0">
                                <span class="font-medium text-14px text-white whitespace-nowrap">{{ $sessionCount }} جلسة
                                    تدريبية</span>
                            </div>
                        @endif
                        <div
                            class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap ">
                            <img src="{{ $courseDetailsImg }}/waiting-room.webp" alt="waiting-room"
                                class="size-6 shrink-0">
                            <span class="font-medium text-14px text-white whitespace-nowrap">حضور مَرِن (عن بُعد /
                                حضوري)</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">

                        <a href="{{ route('landing.v1.checkout') }}"
                            class="rounded-9px font-black text-13px text-[#155554] px-6 py-3   bg-white text-primary border border-[#00FF88]">
                            بـــ


                            <span
                                class="shadow-[-3.32px_4.42px_19.57px_0px_#FFFFFF] font-bold text-22px text-[#C80A0A] px-1">{{ handlePrice($effectivePrice) }}</span>
                            <span class="font-bold text-18px text-[#C80A0A]">اشتري الآن</span>

                            @if ($hasDiscount)
                                <span>
                                    بدلا من
                                </span>
                                <span
                                    class="font-bold text-base text-[#2F2F2FA8] shadow-[-2.4px_3.21px_14.19px_0px_#FFFFFF] line-through ms-2">
                                    {{ handlePrice($course->price) }}</span>
                            @endif
                        </a>
                        @if ($hasUserBought)
                            <a href="{{ $course->getLearningPageUrl() }}"
                                class="btn h-14 rounded-9px font-bold text-18px px-8 inline-flex items-center gap-3 bg-white text-primary border border-[#00FF88] shadow-[0_0_24px_rgba(255,255,255,0.15)] hover:bg-[#f5f5f5]">
                                ابدأ التعلم
                                <img src="{{ $courseDetailsImg }}/seat.webp" alt="" class="size-6 shrink-0">
                            </a>
                        @else
                            <form action="/cart/store" method="post" class="add-to-cart-form"
                                data-seat-icon="{{ $courseDetailsImg }}/seat.webp">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $course->id }}">
                                <input type="hidden" name="item_name" value="webinar_id">
                                <button type="submit" @disabled($isInCart)
                                    class="btn h-14 rounded-9px font-bold text-18px px-8 inline-flex items-center gap-3 bg-white text-primary border border-[#00FF88] shadow-[0_0_24px_rgba(255,255,255,0.15)] hover:bg-[#f5f5f5] {{ $isInCart ? 'btn-disabled opacity-60 cursor-not-allowed is-added-to-cart' : '' }}">
                                    {{ $isInCart ? 'تمت الإضافة للسلة' : 'سجل مقعدك الآن' }}
                                    <img src="{{ $courseDetailsImg }}/seat.webp" alt="" class="size-6 shrink-0">
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </header>

        <section class="my-0 course_details_page relative bg-[#165554] text-white">
            <div class="sticky top-[88px] z-40 border-b border-white/20 bg-[#0A3333]">
                <div class="container">
                    <nav data-scrollspy="#course-content-scrollspy" class="course-scrollspy-nav course-scrollspy-nav--paid"
                        style="--scrollspy-offset: 168px">
                        <a href="#about-course"
                            class="course-scrollspy-link flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">عن الدورة </a>
                        <a href="#course-curriculum"
                            class="course-scrollspy-link flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">المحتوى</a>
                        @if (!empty($course->teacher))
                        <a href="#course-instructor"
                            class="course-scrollspy-link flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">المدرب</a>
                        @endif
                        @if (!empty($faqItems))
                        <a href="#course-faq"
                            class="course-scrollspy-link flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">استفساراتك</a>
                        @endif
                    </nav>
                </div>
            </div>

            <div class="container py-10 lg:py-16">
                <div id="course-content-scrollspy">

                    {{-- About --}}
                    <div id="about-course" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-36px text-white mb-9"> نبذة عن الدورة </h3>
                        <div class="border border-white rounded-10px bg-transparent px-6 lg:px-10 py-8 lg:py-11">
                            <p class="font-normal text-24px text-white mb-6 leading-relaxed">
                                {{ $course->summary ?? Str::limit(strip_tags(html_entity_decode($course->description)), 300) }}
                            </p>
                            {{-- <h4 class="font-medium text-32px text-white mb-2">
                                ماذا ستتعلم؟</h4>
                            <ul class="space-y-3 list-disc list-inside text-white">
                                @forelse ($learningOutcomes as $outcome)
                                    <li class="font-normal text-24px leading-relaxed">{{ $outcome }}</li>
                                @empty
                                    <li class="font-normal text-24px leading-relaxed text-white/70">لا توجد مخرجات تعلم
                                        مضافة لهذه الدورة بعد.</li>
                                @endforelse
                            </ul> --}}
                        </div>
                    </div>

                    {{-- Curriculum --}}
                    <div id="course-curriculum" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium  text-36px text-white mb-9">محتوى الدورة</h3>
                        <div class="border border-[#EEEEEE] rounded-10px bg-white px-6 lg:px-10 py-2 lg:py-4">
                            @if (!empty($course->description))
                                <div class="course-description course-description--rich py-5 leading-relaxed">
                                    {!! $course->description !!}
                                </div>
                            @else
                                <div class="py-5 font-medium text-24px text-white/70">لا يوجد وصف لهذه الدورة بعد.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Instructor --}}
                    <x-landing_v1::instructor-profile-block :teacher="$course->teacher" :coursesCount="$teacher_courses_count" :studentsCount="$teacher_students_count"
                        :rating="$teacher_rating" variant="dark" />


                </div>
            </div>

            {{-- Discovery topics (مواد التعلم — titles only) --}}
            @if (!empty($discoveryTopics))
                <div id="course-discovery"
                    class="course-scrollspy-section mb-0 bg-[#053535] px-3.5 sm:px-0 py-12 lg:py-16 rounded-10px lg:rounded-none">
                    <div class="container">
                        <div class="text-center mb-10">
                            <h3 class="font-bold text-32px text-white mb-3">ماذا سنكتشف معاً؟</h3>
                        </div>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5 max-w-5xl mx-auto list-none p-0 m-0">
                            @foreach ($discoveryTopics as $index => $topic)
                                <li
                                    class="group flex items-start gap-4 rounded-10px border border-white/15 bg-white/[0.04] px-5 py-4 lg:px-6 lg:py-5 transition-colors duration-200 hover:border-[#00D4AA]/40 hover:bg-white/[0.07]">
                                    <span
                                        class="flex shrink-0 size-9 lg:size-10 items-center justify-center rounded-full bg-[#00D4AA]/15 text-[#00D4AA] ring-1 ring-[#00D4AA]/25">
                                        <span class="icon-[tabler--sparkles] size-5 lg:size-[22px]"></span>
                                    </span>
                                    <span
                                        class="font-medium text-16px lg:text-20px text-[#E2E8F0] leading-relaxed pt-1.5 group-hover:text-white transition-colors">
                                        {{ $topic }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- <div class="container"> --}}
                {{-- Comments --}}
                {{-- <div id="course-comments" class="course-scrollspy-section mb-16">
                    <h3 class="font-medium text-32px lg:text-40px text-white mb-8">التعليقات</h3>
                    <div class="border border-[#CFCFCF] rounded-10px bg-transparent px-6 lg:px-10 py-8 lg:py-11">
                        <div class="divide-y divide-white/20">
                            @forelse ($comments as $comment)
                                <div class="flex gap-4 py-6 first:pt-0 last:pb-0">
                                    <div class="size-12 rounded-full bg-white/10 center shrink-0">
                                        <span class="icon-[tabler--user] size-6 text-white/80"></span>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="font-bold text-16px text-white">{{ $comment['name'] }}</span>
                                            <span class="text-white/50 text-14px">·</span>
                                            <span
                                                class="font-normal text-14px text-white/50">{{ $comment['date'] }}</span>
                                        </div>
                                        <p class="font-normal text-16px text-white/80 leading-relaxed">
                                            {{ $comment['body'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="font-normal text-16px text-white/70 text-center py-4">لا توجد تعليقات متاحة</p>
                            @endforelse
                        </div>
                    </div>
                </div> --}}

                {{-- Reviews --}}
                {{-- <div id="course-reviews" class="course-scrollspy-section pb-16">
                    <h3 class="font-medium text-32px lg:text-40px text-white mb-8">التقييم</h3>
                    <div
                        class="border border-[#CFCFCF] rounded-10px bg-transparent px-6 lg:px-10 py-8 lg:py-11 flex flex-col md:flex-row justify-center items-center gap-8 lg:gap-12">
                        <div
                            class="w-[140px] h-[140px] bg-white/10 rounded-10px shrink-0 flex flex-col items-center justify-center border border-white/20">
                            <span
                                class="font-bold text-48px leading-none mb-1 text-white">{{ $averageRating > 0 ? number_format($averageRating, 1) : '—' }}</span>
                            <span class="font-semibold text-10px text-white/60">{{ $totalReviewsCount }}
                                {{ $totalReviewsCount === 1 ? 'تقييم' : 'تقييمات' }}</span>
                        </div>
                        <div class="flex flex-col gap-3.5 w-full max-w-[320px]">
                            @foreach ($ratesDistribution as $stars => $dist)
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-1 w-[80px] justify-end">
                                        @for ($j = 1; $j <= 5; $j++)
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
                                                    fill="{{ $j <= $stars ? '#FFAA00' : 'rgba(255,255,255,0.2)' }}" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <div class="h-2 flex-grow bg-white/15 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#FFAA00] rounded-full"
                                            style="width: {{ $dist['percent'] }}%"></div>
                                    </div>
                                    <span class="font-bold text-12px text-white/70 w-8">{{ $dist['percent'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div> --}}
            {{-- </div> --}}
        </section>

        {{-- FAQ (التعليمات from admin) --}}
        @if (!empty($faqItems))
        <section id="course-faq" class="course-scrollspy-section bg-[#165554] py-16 lg:py-24 my-0 text-white">
            <div class="container">
                <h3 class="font-bold text-32px lg:text-48px mb-15 text-center">
                    <span class="text-white ">إجابات</span>
                    <span class="text-[#FFD343]">لاستفساراتك</span>

                </h3>
                <div class="max-w-4xl mx-auto">
                    <div class="accordion accordion-shadow">
                        @foreach ($faqItems as $index => $faq)
                            <div class="accordion-item {{ $index === 0 ? 'active' : '' }} bg-transparent border-b border-[#1D293D]"
                                id="paid-faq-{{ $index + 1 }}">
                                <button
                                    class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-0 py-6 text-start w-full hover:bg-white/5 transition-all duration-300 text-white"
                                    aria-controls="paid-faq-{{ $index + 1 }}-collapse"
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                    <span
                                        class="icon-[tabler--plus] accordion-item-active:hidden text-white size-5 block shrink-0 transition-opacity duration-300"></span>
                                    <span
                                        class="icon-[tabler--minus] accordion-item-active:block text-white size-5 hidden shrink-0 transition-opacity duration-300"></span>
                                    <span
                                        class="font-medium text-20px lg:text-32px text-[#E2E8F0] flex-grow">{{ $faq['question'] }}</span>
                                </button>
                                <div id="paid-faq-{{ $index + 1 }}-collapse"
                                    class="accordion-content {{ $index === 0 ? '' : 'hidden' }} w-full overflow-hidden transition-[height] duration-300 ease-in-out"
                                    role="region">
                                    <div class="pb-6">
                                        <div class="font-normal text-16px lg:text-24px text-white leading-relaxed [&_p]:mb-3 last:[&_p]:mb-0">
                                            {!! clean($faq['answer']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


            </div>
        </section>
        @endif

        <section class="bg-[#0A3232] py-20 lg:py-28 my-0 min-h-[450px] center text-white">
            <div class="container">
                {{-- Payment options --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-16 max-w-4xl mx-auto max-sm:gap-14">
                    {{-- Tamara --}}
                    <div class="relative center min-h-[185px] rounded-22px bg-[#166060] px-6 pt-12 pb-6 text-center">
                        <div
                            class="absolute top-0 right-4 -translate-y-8 inline-flex items-center justify-center rounded-8px   w-[248px] h-[80px]">
                            <img src="{{ $courseDetailsImg }}/tamara.webp" alt="tamara"
                                class="size-full object-cover">
                        </div>
                        <p class="font-bold text-28px lg:text-30px text-white">ادفعها ولا تشيل هم</p>
                    </div>

                    {{-- Tabby --}}
                    <div class="relative center rounded-22px bg-[#166060] px-6 pt-12 pb-6 text-center min-h-[185px]">
                        <div
                            class="absolute top-0 right-4 -translate-y-8 inline-flex items-center justify-center rounded-8px   w-[248px] h-[80px] ">
                            <img src="{{ $courseDetailsImg }}/tabby.webp" alt="tabby" class="size-full object-cover">
                        </div>
                        <p class="font-bold text-28px lg:text-30px text-white">وقسّطها على دفعات</p>
                    </div>
                </div>
            </div>
        </section>



    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.initCourseHeroVideo === 'function') {
                window.initCourseHeroVideo();
            }

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
                    });
                }
            }, {
                passive: true
            });
        });
    </script>
@endpush
