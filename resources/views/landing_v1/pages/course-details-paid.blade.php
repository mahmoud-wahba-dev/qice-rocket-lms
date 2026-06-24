@extends('landing_v1.layouts.app')

@section('content')
    @php
        $landingImg = asset('assets/landing_v1/img');
        $courseDetailsImg = asset('assets/landing_v1/img/course_details');
        $heroYoutubeId = 'JXEXdbS5tsI';

        $learningOutcomes = [
            'فهم إطار عمل PMBOK والمعايير الدولية لإدارة المشاريع',
            'إتقان مجالات المعرفة العشرة في إدارة المشاريع',
            'الاستعداد العملي لاجتياز اختبار PMP المعتمد',
        ];

        $curriculumModules = [
            'الوحدة الأولى: أساسيات الفكر الاستشاري المؤسسي',
            'الوحدة الثانية: نطاق المشروع والجدول الزمني',
            'الوحدة الثالثة: التكلفة والجودة وإدارة المخاطر',
            'الوحدة الرابعة: القيادة والتواصل مع أصحاب المصلحة',
        ];

        $discoveryTopics = [
            'نطاق المشروع، الجدول الزمني، والتكلفة',
            'الجودة، المخاطر، وأصحاب المصلحة',
            'المنهجيات الرشيقة (Agile)',
            'القيادة وإدارة الفريق',
            'نماذج اختبارات المحاكاة',
            'استراتيجيات النجاح في الاختبار',
        ];

        $comments = [
            [
                'name' => 'فهد العنزي',
                'date' => '10 يونيو 2025',
                'body' => 'برنامج متميز ساعدني على فهم منهجية PMP بشكل عملي. المدرب يمتلك خبرة واضحة في المجال.',
            ],
            [
                'name' => 'ريم الحربي',
                'date' => '22 مايو 2025',
                'body' => 'محتوى منظم واختبارات المحاكاة كانت قريبة جداً من الاختبار الفعلي. أنصح بها بشدة.',
            ],
            [
                'name' => 'عبدالله القحطاني',
                'date' => '5 أبريل 2025',
                'body' => 'تجربة تدريبية احترافية من التسجيل وحتى الحصول على الشهادة. قيمة ممتازة مقابل السعر.',
            ],
        ];

        $ratesDistribution = [
            5 => ['percent' => 78],
            4 => ['percent' => 14],
            3 => ['percent' => 5],
            2 => ['percent' => 2],
            1 => ['percent' => 1],
        ];

        $faqItems = [
            [
                'question' => 'هل الدورة مناسبة للمبتدئين؟',
                'answer' =>
                    'نعم، الدورة مناسبة للمبتدئين ولأصحاب الخبرة الراغبين في تطوير مهاراتهم والاستعداد لاختبار PMP.',
            ],
            [
                'question' => 'ما هي مميزات الدورة؟',
                'answer' =>
                    'تشمل 35 ساعة تدريبية معتمدة، وتدريباً عملياً على أسئلة الاختبار، واختبارات محاكاة، وتغطية منهجيات Hybrid وScrum وAgile.',
            ],
            [
                'question' => 'بعد إتمام الدورة ماذا يحصل المتدرب؟',
                'answer' =>
                    'يحصل المتدرب على شهادة إتمام الدورة واستيفاء متطلب 35 ساعة تدريبية اللازمة للتقدم لاختبار PMP.',
            ],
            [
                'question' => 'كم مدة الدورة؟',
                'answer' => 'مدة الدورة 7 أيام تدريبية بإجمالي 35 ساعة تدريبية، مع إمكانية الحضور عن بُعد أو حضورياً.',
            ],
        ];
    @endphp

    <main>

        {{-- Hero: full-bleed video (left) + gradient fade into teal + text (right, RTL) --}}
        <header class="relative flex items-center overflow-hidden bg-[#155554] text-white min-h-[520px] lg:min-h-[680px]">
            <div id="course-hero-video" class="absolute inset-y-0 left-0 z-0 w-full sm:w-[72%] lg:w-[58%] overflow-hidden">
                <iframe id="course-hero-youtube"
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 min-h-full min-w-[177.78vh] w-[100vw] h-[56.25vw] border-0 pointer-events-auto"
                    src="https://www.youtube-nocookie.com/embed/{{ $heroYoutubeId }}?enablejsapi=1&autoplay=1&mute=1&loop=1&playlist={{ $heroYoutubeId }}&controls=0&modestbranding=1&rel=0&playsinline=1&origin={{ urlencode(url('/')) }}"
                    title="إدارة المشاريع في 15 دقيقة — معاينة الدورة"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
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
            </div>

            <div class="absolute inset-0 z-[1] bg-gradient-to-r from-transparent from-5% via-[#155554]/60 via-40% to-[#155554] to-55% pointer-events-none sm:bg-gradient-to-r sm:from-transparent sm:from-10% sm:via-[#155554]/70 sm:via-45% sm:to-[#155554] sm:to-62%"
                aria-hidden="true"></div>

            <div class="container relative z-10 py-12 lg:py-20 pointer-events-none">
                <div class="lg:max-w-[55%] pointer-events-auto">
                    <p class="font-semibold text-base text-white mb-10">خطوتك الأولى نحو القمة...</p>
                    <h1 class="font-bold text-36px lg:text-46px text-[#F1F5F9] mb-8 leading-tight">
                        أهلاً بك في رحلة إتقان إدارة المشاريع (PMP)
                    </h1>
                    <p class="font-normal text-16px lg:text-18px text-white mb-10 leading-relaxed max-w-2xl">
                        برنامج تدريبي معتمد يأخذك خطوة بخطوة نحو إتقان منهجية إدارة المشاريع الاحترافية والاستعداد
                        لاختبار PMP الدولي بثقة وكفاءة.
                    </p>

                    <div class="flex items-center gap-3 lg:gap-4 flex-wrap xl:flex-nowrap mb-10">
                        <div
                            class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                            <img src="http://training.qiec.local/assets/landing_v1/img/course_details/calendar.webp"
                                alt="calendar" class="size-6 shrink-0">
                            <span class="font-semibold text-14px text-white whitespace-nowrap">7 أيام تفاعلية</span>
                        </div>
                        <div
                            class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap">
                            <img src="http://training.qiec.local/assets/landing_v1/img/course_details/clock.webp"
                                alt="clock" class="size-6 shrink-0">
                            <span class="font-medium text-14px text-white whitespace-nowrap">35 ساعة تدريبية
                                معتمدة</span>
                        </div>
                        <div
                            class="flex items-center gap-2  px-7 py-2 rounded-full border border-[#155554] backdrop-blur-[9px] flex-nowrap ">
                            <img src="http://training.qiec.local/assets/landing_v1/img/course_details/waiting-room.webp"
                                alt="waiting-room" class="size-6 shrink-0">
                            <span class="font-medium text-14px text-white whitespace-nowrap">حضور مَرِن (عن بُعد /
                                حضوري)</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">

                        <div
                            class="rounded-9px font-black text-13px text-[#155554] px-6 py-3   bg-white text-primary border border-[#00FF88]">
                            بـــ
                            <span
                                class="shadow-[-3.32px_4.42px_19.57px_0px_#FFFFFF] font-bold text-22px text-[#C80A0A] px-1">1,299
                                ر.س</span>
                            <span>
                                بدلا من
                            </span>
                            <span
                                class="font-bold text-base text-[#2F2F2FA8] shadow-[-2.4px_3.21px_14.19px_0px_#FFFFFF] line-through ms-2">
                                1,700 ر.س</span>
                        </div>
                        <button type="button"
                            class="btn h-14 rounded-9px font-bold text-18px px-8 inline-flex items-center gap-3 bg-white text-primary border border-[#00FF88] shadow-[0_0_24px_rgba(255,255,255,0.15)] hover:bg-[#f5f5f5]">
                            سجل مقعدك الآن
                            <img src="{{ $courseDetailsImg }}/seat.webp" alt="" class="size-6 shrink-0">
                        </button>

                    </div>
                </div>
            </div>
        </header>

        <section class="my-0 course_details_page relative bg-[#165554] text-white">
            <div class="sticky top-[88px] z-40 border-b border-white/20 bg-[#0A3333]">
                <div class="container">
                    <nav data-scrollspy="#course-content-scrollspy" class="course-scrollspy-nav"
                        style="--scrollspy-offset: 168px">
                        <a href="#about-course"
                            class="flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">عن الدورة</a>
                        <a href="#course-curriculum"
                            class="flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">المحتوى</a>
                        <a href="#course-comments"
                            class="flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">التعليقات</a>
                        <a href="#course-reviews"
                            class="flex items-center justify-center py-[1.375rem] lg:py-6 px-2 font-medium text-20px lg:text-24px text-center whitespace-nowrap text-white border-b-[3px] border-b-transparent -mb-px transition-[border-color] duration-200 hover:text-white [&.active]:border-b-white"
                            aria-current="false">المراجعات</a>
                    </nav>
                </div>
            </div>

            <div class="container py-10 lg:py-16">
                <div id="course-content-scrollspy">

                    {{-- About --}}
                    <div id="about-course" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-40px text-white mb-8">عن الدورة</h3>
                        <div class="border border-white/25 rounded-10px bg-transparent px-6 lg:px-10 py-8 lg:py-11">
                            <p class="font-normal text-18px lg:text-24px text-white/90 mb-8 leading-relaxed">
                                برنامج متكامل لإعدادك لشهادة PMP المعتمدة عالمياً، يجمع بين المحتوى النظري العميق
                                والتطبيق العملي على حالات واقعية في إدارة المشاريع المؤسسية.
                            </p>
                            <h4 class="font-medium text-22px lg:text-28px text-white mb-4 pt-6 border-t border-white/20">
                                ماذا ستتعلم؟</h4>
                            <ul class="space-y-3 list-disc list-inside text-white/85">
                                @foreach ($learningOutcomes as $outcome)
                                    <li class="font-normal text-18px lg:text-22px leading-relaxed">{{ $outcome }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Curriculum --}}
                    <div id="course-curriculum" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-40px text-white mb-8">منهج الدورة</h3>
                        <div class="border border-white/25 rounded-10px bg-transparent px-6 lg:px-10 py-2 lg:py-4">
                            @foreach ($curriculumModules as $module)
                                <div
                                    class="py-5 border-b border-white/20 last:border-b-0 font-medium text-18px lg:text-24px text-white">
                                    {{ $module }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Instructor --}}
                    <div id="course-instructor" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-40px text-white mb-8">عن المدرب</h3>
                        <div
                            class="border border-white/25 rounded-10px bg-transparent flex flex-col md:flex-row items-start gap-10 px-6 lg:px-10 py-8 lg:py-11">
                            <div class="w-[160px] h-[160px] rounded-8px overflow-hidden shrink-0 border border-white/20">
                                <img src="{{ $landingImg }}/home/instructor.webp" alt="م. أحمد بن صالح آل سعود"
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow">
                                <h6 class="font-semibold text-24px text-white mb-2">م. أحمد بن صالح آل سعود</h6>
                                <p class="font-normal text-16px lg:text-18px text-white/75 mb-6">
                                    خبير استراتيجيات التحول الرقمي ومستشار تطوير الأعمال
                                </p>
                                <div class="flex flex-wrap items-center gap-6 mb-8 border-t border-b border-white/20 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--calendar] size-5 text-white/70"></span>
                                        <div class="font-medium text-10px text-white/80 flex flex-col">
                                            <span>عضو منذ</span>
                                            <span class="text-white">2018</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--video] size-5 text-white/70"></span>
                                        <div class="font-medium text-10px text-white/80 flex flex-col">
                                            <span>عدد الدورات</span>
                                            <span class="text-white">12</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--users] size-5 text-white/70"></span>
                                        <div class="font-medium text-10px text-white/80 flex flex-col">
                                            <span>عدد الطلاب</span>
                                            <span class="text-white">1,240</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="icon-[tabler--star-filled] size-5 text-[#FFAA00]"></span>
                                        <div class="font-medium text-10px text-white/80 flex flex-col">
                                            <span>التقييم</span>
                                            <span class="text-white">4.9</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="#"
                                    class="btn h-12 rounded-4px font-medium text-14px px-8 inline-flex items-center gap-2 bg-white text-primary border-0 hover:bg-[#f0f0f0]">
                                    <span class="icon-[tabler--user] size-5"></span>
                                    عرض الملف الشخصي
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Discovery accordion --}}
                    <div id="course-discovery"
                        class="course-scrollspy-section mb-16 bg-[#08322f] -mx-3.5 sm:mx-0 px-3.5 sm:px-0 py-12 lg:py-16 rounded-10px lg:rounded-none">
                        <div class="text-center mb-10">
                            <h3 class="font-bold text-28px lg:text-40px text-white mb-3">ماذا سنكتشف معاً؟</h3>
                            <p class="font-normal text-16px lg:text-18px text-white/70 max-w-2xl mx-auto">
                                رحلة معرفية شاملة تغطي كافة جوانب الإدارة الاحترافية للمشاريع
                            </p>
                        </div>
                        <div class="border border-white/25 rounded-10px bg-transparent overflow-hidden">
                            <div class="accordion accordion-shadow">
                                @foreach ($discoveryTopics as $index => $topic)
                                    <div class="accordion-item {{ $index === 0 ? 'active' : '' }} bg-transparent"
                                        id="discovery-{{ $index + 1 }}">
                                        <button
                                            class="accordion-toggle shadow-none bg-transparent inline-flex items-center justify-between gap-4 px-6 lg:px-8 font-medium text-18px lg:text-24px text-white py-5 border-b border-white/20 text-start w-full hover:bg-white/5 transition-colors"
                                            aria-controls="discovery-{{ $index + 1 }}-collapse"
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                            <span>{{ $topic }}</span>
                                            <span
                                                class="icon-[tabler--chevron-down] accordion-item-active:rotate-180 size-5 shrink-0 transition-transform"></span>
                                        </button>
                                        <div id="discovery-{{ $index + 1 }}-collapse"
                                            class="accordion-content {{ $index === 0 ? '' : 'hidden' }} w-full overflow-hidden transition-[height] duration-300"
                                            role="region">
                                            <div class="px-6 lg:px-8 pb-5 pt-2">
                                                <p class="text-white/75 font-normal text-16px leading-relaxed">
                                                    محور تدريبي متخصص يغطي {{ $topic }} ضمن إطار PMP المعتمد
                                                    مع تطبيقات عملية ودراسات حالة من بيئة العمل السعودية.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Comments --}}
                    <div id="course-comments" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-40px text-white mb-8">التعليقات</h3>
                        <div class="border border-white/25 rounded-10px bg-transparent px-6 lg:px-10 py-8 lg:py-11">
                            <div class="divide-y divide-white/20">
                                @foreach ($comments as $comment)
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
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Reviews --}}
                    <div id="course-reviews" class="course-scrollspy-section mb-16">
                        <h3 class="font-medium text-32px lg:text-40px text-white mb-8">التقييم</h3>
                        <div
                            class="border border-white/25 rounded-10px bg-transparent px-6 lg:px-10 py-8 lg:py-11 flex flex-col md:flex-row justify-center items-center gap-8 lg:gap-12">
                            <div
                                class="w-[140px] h-[140px] bg-white/10 rounded-10px shrink-0 flex flex-col items-center justify-center border border-white/20">
                                <span class="font-bold text-48px leading-none mb-1 text-white">4.9</span>
                                <span class="font-semibold text-10px text-white/60">32 تقييم</span>
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
                    </div>

                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="bg-[#0a3a36] py-16 lg:py-24 my-0 text-white">
            <div class="container">
                <h3 class="font-bold text-32px lg:text-48px mb-12 text-center">
                    <span class="text-gold">إجابات</span> لاستفساراتك
                </h3>
                <div class="max-w-4xl mx-auto">
                    <div class="accordion accordion-shadow">
                        @foreach ($faqItems as $index => $faq)
                            <div class="accordion-item {{ $index === 0 ? 'active' : '' }} bg-transparent border-b border-white/20"
                                id="paid-faq-{{ $index + 1 }}">
                                <button
                                    class="accordion-toggle shadow-none bg-transparent inline-flex items-center gap-x-4 px-0 py-6 text-start w-full hover:bg-white/5 transition-colors text-white"
                                    aria-controls="paid-faq-{{ $index + 1 }}-collapse"
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                    <span
                                        class="icon-[tabler--plus] accordion-item-active:hidden text-white size-5 block shrink-0"></span>
                                    <span
                                        class="icon-[tabler--minus] accordion-item-active:block text-white size-5 hidden shrink-0"></span>
                                    <span
                                        class="font-medium text-20px lg:text-28px text-white flex-grow">{{ $faq['question'] }}</span>
                                </button>
                                <div id="paid-faq-{{ $index + 1 }}-collapse"
                                    class="accordion-content {{ $index === 0 ? '' : 'hidden' }} w-full overflow-hidden"
                                    role="region">
                                    <div class="pb-6">
                                        <p class="font-normal text-16px lg:text-20px text-white/75 leading-relaxed">
                                            {{ $faq['answer'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Payment options --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-3xl mx-auto mt-16">
                    <div
                        class="p-6 flex flex-col items-center justify-center gap-4 text-center min-h-[140px] bg-black/20 border border-white/15 rounded-12px">
                        <span class="px-5 py-2 rounded-6px font-bold text-18px bg-[#b8f5d8] text-[#1a3d36]">tabby</span>
                        <p class="font-medium text-16px text-white">وقسّطها على دفعات</p>
                    </div>
                    <div
                        class="p-6 flex flex-col items-center justify-center gap-4 text-center min-h-[140px] bg-black/20 border border-white/15 rounded-12px">
                        <span
                            class="px-5 py-2 rounded-6px font-bold text-18px bg-gradient-to-br from-[#f5c4a8] to-[#e8b4f0] text-[#1a1a1a]">tamara</span>
                        <p class="font-medium text-16px text-white">ادفعها ولا تشيل هم</p>
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
