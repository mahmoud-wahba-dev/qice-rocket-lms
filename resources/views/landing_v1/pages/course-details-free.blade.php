@extends('landing_v1.layouts.app')

@section('content')
    @php
        $landingImg = asset('assets/landing_v1/img');
        $courseDetailsImg = asset('assets/landing_v1/img/course_details');

        if (empty($learningOutcomes)) {
            $learningOutcomes = [
                'فهم المفاهيم الأساسية للعمل في البيئة السعودية',
                'التعرف على الأنظمة واللوائح المهنية ذات الصلة',
                'بناء أساس معرفي يمهّد للتخصص في مجالات التطوير المهني',
            ];
        }

        if (empty($curriculumModules)) {
            $curriculumModules = [
                'الوحدة الأولى: أساسيات الفكر الاستشاري المؤسسي',
                'الوحدة الثانية: مفاهيم العمل والثقافة المهنية السعودية',
                'الوحدة الثالثة: مهارات التواصل والتعامل في بيئة العمل',
                'الوحدة الرابعة: تطبيقات عملية ودراسات حالة',
            ];
        }

        if (empty($comments)) {
            $comments = [];
        }

        if (empty($ratesDistribution)) {
            $ratesDistribution = [
                5 => ['percent' => 0],
                4 => ['percent' => 0],
                3 => ['percent' => 0],
                2 => ['percent' => 0],
                1 => ['percent' => 0],
            ];
        }

        $hasUserBought = $hasUserBought ?? false;
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
                            {{ $course->summary ?? Str::limit(strip_tags(html_entity_decode($course->description)), 220) }}
                        </p>

                        <div class="flex items-center gap-3 lg:gap-4 flex-wrap xl:flex-nowrap mb-10">
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/calendar.webp" alt="calendar" class="size-6 shrink-0">
                                <span class="font-semibold text-14px text-primary whitespace-nowrap">7 أيام تفاعلية</span>
                            </div>
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                                <img src="{{ $courseDetailsImg }}/clock.webp" alt="clock" class="size-6 shrink-0">
                                <span class="font-medium text-14px text-primary whitespace-nowrap">35 ساعة تدريبية
                                    معتمدة</span>
                            </div>
                            <div
                                class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap ">
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
                        {{-- include card component her  --}}
                        <x-landing_v1::workshop-card :title="$course->title" :summary="$course->summary ?? $course->description" :categoryTitle="$course->category->title ?? ''"
                            :slug="$course->slug" buttonUrl="#about-course" />
                    </div>
                </div>
            </div>
        </header>

        <section class="my-0 course_details_page relative bg-white">
            <div class="sticky top-[88px] z-40 bg-[#F5EFE5] border-b border-[#B4B4B4]">
                <div class="container">
                    <nav data-scrollspy="#course-content-scrollspy" class="course-scrollspy-nav"
                        style="--scrollspy-offset: 168px">
                        <a href="#about-course" class="course-scrollspy-link">
                            عن الدورة
                        </a>
                        <a href="#course-curriculum" class="course-scrollspy-link">
                            المحتوى
                        </a>
                        <a href="#course-comments" class="course-scrollspy-link">
                            التعليقات
                        </a>
                        <a href="#course-reviews" class="course-scrollspy-link">
                            المراجعات
                        </a>
                    </nav>
                </div>
            </div>

            <div class="container py-8 lg:py-12">


                <div id="course-content-scrollspy">

                    {{-- About --}}
                    <div id="about-course" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-36px text-primary mb-10">عن الدورة</h3>
                        <div class="border border-primary px-6 lg:px-8 py-8 lg:py-11 rounded-10px bg-white shadow-sm">
                            <p class="font-normal  lg:text-24px text-primary mb-7 leading-relaxed">
                                تهدف هذه الدورة إلى تزويد المتدربين بفهم شامل لمفاهيم العمل في المملكة العربية السعودية،
                                من خلال محتوى تدريبي مبسّط يجمع بين الجوانب النظرية والتطبيقية، ويساعد على بناء
                                قاعدة معرفية قوية قبل الانتقال إلى البرامج التدريبية المتقدمة.
                            </p>

                            <h4 class="font-medium text-24px lg:text-32px text-primary mb-4 border-t border-gray-100 pt-6">
                                ماذا ستتعلم؟</h4>
                            <ul class="text-[#7C7F88] space-y-3 list-disc list-inside">
                                @foreach ($learningOutcomes as $outcome)
                                    <li class="font-normal text-24px leading-relaxed text-primary">{{ $outcome }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Curriculum --}}
                    <div id="course-curriculum" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">منهج الدورة</h3>
                        <div class="border border-primary px-6 lg:px-8 py-2 lg:py-4 rounded-10px bg-white shadow-sm">
                            @foreach ($curriculumModules as $module)
                                <div
                                    class="py-5 border-b border-[#EEEEEE] last:border-b-0 font-medium text-18px lg:text-24px text-primary">
                                    {{ $module }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Instructor --}}
                    <x-landing_v1::instructor-profile-block
                        :teacher="$course->teacher"
                        :coursesCount="$teacher_courses_count"
                        :studentsCount="$teacher_students_count"
                        :rating="$teacher_rating"
                        variant="light"
                    />

                    {{-- Comments --}}
                    <div id="course-comments" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">التعليقات</h3>
                        <div class="border border-primary px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm">
                            <div class="space-y-0 divide-y divide-[#EEEEEE]">
                                @foreach ($comments as $comment)
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
                                @endforeach
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
                    </div>

                    {{-- Reviews --}}
                    <div id="course-reviews" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">المراجعات</h3>
                        <div
                            class="border border-primary px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm flex flex-col md:flex-row justify-center items-center gap-8 lg:gap-12">
                            <div
                                class="w-[140px] h-[140px] bg-primary/5 rounded-10px shrink-0 flex flex-col items-center justify-center text-primary border border-primary/10">
                                <span class="font-bold text-48px leading-none mb-1 text-primary">4.8</span>
                                <span class="font-semibold text-10px text-primary/60">24 تقييم</span>
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
                    </div>

                </div>
            </div>
        </section>

        {{-- FAQ band --}}
        <section class="bg-[#F5EFE5] py-16 lg:py-24 my-0">
            <div class="container">
                <h3 class="font-bold text-32px lg:text-48px text-primary mb-13 text-start">إجابات لاستفساراتك</h3>
                <div class="accordion accordion-shadow  border border-primary rounded-10px overflow-hidden">
                    <div class="accordion-item active bg-transparent" id="faq-1">
                        <button
                            class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-5 lg:px-8 font-normal text-18px lg:text-32px text-primary py-6 border-b border-[#B4B4B4] text-start w-full hover:bg-gray-50 transition-colors"
                            aria-controls="faq-1-collapse" aria-expanded="true">
                            <span
                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                            <span
                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                            هل الدورة مناسبة للمبتدئين؟
                        </button>
                        <div id="faq-1-collapse"
                            class="accordion-content w-full overflow-hidden transition-[height] duration-300"
                            aria-labelledby="faq-1" role="region">
                            <div class="px-5 lg:px-8 pb-6 border-t border-[#B4B4B4] pt-3">
                                <p class="text-primary font-normal text-24px leading-relaxed">
                                    نعم، الدورة مصمّمة خصيصاً للمبتدئين ولا تتطلب أي خبرة سابقة في المجال. تبدأ من المفاهيم
                                    الأساسية
                                    وتتدرج بشكل تدريجي لضمان فهم شامل لجميع المحاور.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent" id="faq-2">
                        <button
                            class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-5 lg:px-8 font-semibold text-18px lg:text-22px text-primary py-6 border-t border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                            aria-controls="faq-2-collapse" aria-expanded="false">
                            <span
                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                            <span
                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                            ما هي مميزات الدورة؟
                        </button>
                        <div id="faq-2-collapse"
                            class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300"
                            aria-labelledby="faq-2" role="region">
                            <div class="px-5 lg:px-8 pb-6 border-t border-gray-100 pt-3">
                                <p class="text-primary/80 font-normal text-16px leading-relaxed">
                                    تتضمن الدورة 35 ساعة تدريبية موزّعة على 7 أيام، وتعتمد منهجيات حديثة مثل Hybrid وScrum
                                    وAgile
                                    لضمان تجربة تعلّم تفاعلية وعملية تناسب احتياجات سوق العمل.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent" id="faq-3">
                        <button
                            class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-5 lg:px-8 font-normal text-18px lg:text-32px text-primary py-6 border-t border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                            aria-controls="faq-3-collapse" aria-expanded="false">
                            <span
                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                            <span
                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                            بعد إتمام الدورة ماذا يحصل المتدرب؟
                        </button>
                        <div id="faq-3-collapse"
                            class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300"
                            aria-labelledby="faq-3" role="region">
                            <div class="px-5 lg:px-8 pb-6 border-t border-gray-100 pt-3">
                                <p class="text-primary/80 font-normal text-16px leading-relaxed">
                                    يحصل المتدرب على شهادة إتمام معتمدة من المركز، بالإضافة إلى إمكانية الوصول الدائم لمحتوى
                                    الدورة
                                    من خلال حسابه على المنصة.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent" id="faq-4">
                        <button
                            class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-5 lg:px-8 font-semibold text-18px lg:text-22px text-primary py-6 border-t border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                            aria-controls="faq-4-collapse" aria-expanded="false">
                            <span
                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                            <span
                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                            كم مدة الدورة؟
                        </button>
                        <div id="faq-4-collapse"
                            class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300"
                            aria-labelledby="faq-4" role="region">
                            <div class="px-5 lg:px-8 pb-6 border-t border-gray-100 pt-3">
                                <p class="text-primary/80 font-normal text-16px leading-relaxed">
                                    مدة الدورة 7 أيام تدريبية بإجمالي 35 ساعة تدريبية، موزّعة بين جلسات مباشرة عبر الإنترنت
                                    ومحتوى ذاتي يمكن متابعته وفق جدولك الزمني.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
                var link = e.target.closest(
                    '.course_details_page [data-scrollspy] a[href^="#"], a.js-scroll-to-course-section[href^="#"]'
                );
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
                if (nav) {
                    nav.querySelectorAll('a[href^="#"]').forEach(function(a) {
                        var active = a.getAttribute('href') === link.getAttribute('href');
                        a.classList.toggle('active', active);
                        a.setAttribute('aria-current', active ? 'location' : 'false');
                    });
                }
            });
        });
    </script>
@endpush
