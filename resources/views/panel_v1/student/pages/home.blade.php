@extends('panel_v1.student.layouts.app')

@section('content')
@php $heroLogo = $panelStudentImg . '/hero-logo.webp'; @endphp
@php $noDataImg = $panelStudentImg . '/no-data.webp'; @endphp

@if (($pendingAssignments ?? collect())->isNotEmpty())
<section class="removing:translate-x-5 removing:opacity-0 transition duration-300 ease-in-out" id="dismiss-alert">
    <div
        class="relative w-[75%] mx-auto flex items-end justify-between bg-[#F2F0ED85]  rounded-12px py-5 px-6 border border-[#874C09] ">

        <div class="flex items-center gap-5">
            <div class="size-13 rounded-12px bg-[#C99C6970] border border-[#C99C691C] p-4 center">
                <svg width="25" height="23" viewBox="0 0 25 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M7.15463 3.06836H3.06587C1.93635 3.06836 1.02148 3.98363 1.02148 5.11274V19.4234C1.02148 20.5525 1.93635 21.4678 3.06587 21.4678H21.4653C22.5948 21.4678 23.5097 20.5525 23.5097 19.4234V5.11274C23.5097 3.98363 22.5948 3.06836 21.4653 3.06836H17.3765"
                        stroke="#0F4C45" stroke-width="2.04445" stroke-linecap="round" />
                    <path
                        d="M9.19824 0.510742H15.3311C15.6133 0.510742 15.8418 0.7402 15.8418 1.02246V4.08887C15.8417 4.37108 15.6133 4.59961 15.3311 4.59961H9.19824C8.91602 4.59961 8.68658 4.37108 8.68652 4.08887V1.02246C8.68652 0.7402 8.91598 0.510742 9.19824 0.510742Z"
                        fill="#0F4C45" stroke="#0F4C45" stroke-width="1.02223" />
                </svg>

            </div>
            <div>
                <p class="font-semibold text-12px text-primary mb-1">
                    {{ ($pendingAssignments ?? collect())->first()->webinar->title ?? '' }}
                </p>
                <h5 class="font-bold text-20px text-primary mb-3">
                    لديك تكليف معلق بانتظار التسليم
                </h5>
                <p>
                    <span class="font-bold text-14px text-[#00C206]"> ⏱ غداً، 11:59 مساءً</span>
                    <span class="font-semibold text-11px text-primary">
                        الموعد النهائي للتسليم:
                    </span>
                </p>
            </div>
        </div>

        <button type="button" class="btn btn-primary font-bold text-13px" aria-haspopup="dialog" aria-expanded="false"
            aria-controls="assignment-submit-modal" data-overlay="#assignment-submit-modal">حل الواجب الآن</button>

        <button type="button" class="ms-auto leading-none absolute end-4 top-4" data-remove-element="#dismiss-alert"
            aria-label="Close Button">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
</section>
@endif

<header class="overflow-hidden text-white pt-6 mt-8 ">
    <div class="container ">
        @php
            $hour = (int) now()->format('H');
            if ($hour < 12) {
                $heroGreeting = 'صباح الخير';
            } elseif ($hour < 17) {
                $heroGreeting = 'طاب يومك';
            } else {
                $heroGreeting = 'طاب مساؤك';
            }

            $continueCourse = collect($enrolledCourses ?? [])
                ->first(fn ($course) => !empty($course['hasWebinar']) && ($course['progress'] ?? 0) < 100)
                ?? collect($enrolledCourses ?? [])->first(fn ($course) => !empty($course['hasWebinar']));
            $continueCourseTitle = $continueCourse['title'] ?? null;

            $heroStats = [
                ['value' => $stats['activeCourses'] ?? 0, 'label' => 'الدورات النشطة', 'icon' => 'tabler--school'],
                ['value' => $stats['learningHours'] ?? 0, 'label' => 'ساعات التعلم', 'icon' => 'tabler--hourglass'],
                ['value' => $stats['upcomingSessions'] ?? 0, 'label' => 'محاضرات قادمة (المباشرة)', 'icon' => 'tabler--calendar'],
                ['value' => $stats['assignments'] ?? 0, 'label' => 'تكليفات وواجبات', 'icon' => 'tabler--file-text'],
                ['value' => $stats['certificates'] ?? 0, 'label' => 'الشهادات المكتسبة', 'icon' => 'tabler--certificate'],
            ];
        @endphp

        <div class="bg-[#0F4C45] h-[70vh] relative rounded-[16px] overflow-hidden px-5 sm:px-8 lg:px-10 flex items-center">
            {{-- Fingerprint / ripple pattern (visual left) --}}
            <img src="{{ $heroLogo }}" alt="" aria-hidden="true"
                class="pointer-events-none absolute -top-8 left-0 w-[240px] sm:w-[300px] lg:w-[360px] h-auto opacity-[0.28] mix-blend-screen select-none">

            <div class="relative z-[1] w-full">
                <div class="mb-7">
                    <h1 class="font-extrabold text-[26px] sm:text-[32px] lg:text-[36px] leading-tight mb-2">
                        {{ $heroGreeting }} ، {{ $authUser->full_name ?? 'علا محمد' }}
                    </h1>
                    <p class="font-medium text-[14px] sm:text-[16px] text-white/90 leading-relaxed max-w-3xl">
                        @if ($continueCourseTitle)
                            جاهزة لاستكمال دورة '{{ $continueCourseTitle }}' اليوم؟
                        @else
                            جاهزة لاستكمال رحلتك التعليمية اليوم؟
                        @endif
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-3.5">
                    @foreach ($heroStats as $stat)
                        <div class="bg-white/[0.10] rounded-[12px] px-3 py-4 sm:px-4 sm:py-5 text-center min-h-[92px] flex flex-col items-center justify-center gap-1.5">
                            <div class="flex items-center justify-center gap-2">
                                <span class="icon-[{{ $stat['icon'] }}] size-5 text-[#C9A46C] shrink-0"></span>
                                <span class="font-extrabold text-[24px] sm:text-[28px] leading-none text-white">{{ $stat['value'] }}</span>
                            </div>
                            <p class="font-medium text-[11px] sm:text-[12px] leading-snug text-white/90">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>

<section class="mt-8">
    <div class="container">
        {{-- Figma tabs: centered, 15px, gray inactive, primary active with 2px underline --}}
        <nav class="flex items-center gap-1 sm:gap-2 overflow-x-auto border-b border-[#E5E7EB] mb-8 -mx-2 px-2"
            aria-label="أقسام لوحة المتدرب" role="tablist">
            @php $tabBase = 'tab whitespace-nowrap font-semibold text-[14px] sm:text-[15px] pb-3.5 pt-2 px-3 sm:px-4 border-b-2 transition-colors flex-1 sm:flex-none justify-center text-center'; @endphp
            <button type="button" class="{{ $tabBase }} border-[#0F3D36] text-[#0F3D36] active" id="tabs-large-item-1" data-v1-tab="#tabs-large-1" aria-controls="tabs-large-1" role="tab" aria-selected="true">دوراتي</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-2" data-v1-tab="#tabs-large-2" aria-controls="tabs-large-2" role="tab" aria-selected="false">المحاضرات المباشرة</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-3" data-v1-tab="#tabs-large-3" aria-controls="tabs-large-3" role="tab" aria-selected="false">تكليفات المحاضرات</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-4" data-v1-tab="#tabs-large-4" aria-controls="tabs-large-4" role="tab" aria-selected="false">الاختبارات</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-5" data-v1-tab="#tabs-large-5" aria-controls="tabs-large-5" role="tab" aria-selected="false">الشهادات</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-6" data-v1-tab="#tabs-large-6" aria-controls="tabs-large-6" role="tab" aria-selected="false">التعليقات</button>
        </nav>

        <div class="student-home-tab-panels">
            <div id="tabs-large-1" role="tabpanel" aria-labelledby="tabs-large-item-1">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-13 items-start">
                    <div class="lg:col-span-8 min-w-0">
                        @foreach (($enrolledCourses ?? []) as $course)
                            @if (!empty($course['hasWebinar']))
                            <div class="relative border border-d9 px-6 py-8 rounded-12px mb-12 w-full">
                                <div class="flex items-center gap-5 mb-9">
                                    <div class="w-[67px] h-[62px] rounded-12px overflow-hidden center bg-primary/10">
                                        @if (!empty($course['thumbnail']))
                                            <img src="{{ $course['thumbnail'] }}"
                                                class="rounded-12px" alt="course image" width="67" height="62" loading="lazy"
                                                decoding="async">
                                        @else
                                            <span class="font-bold text-24px text-primary">{{ mb_substr($course['title'] ?? '?', 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="font-semibold text-24px text-primary mb-4">{{ $course['title'] }}</h6>
                                        <p class="font-medium text-base text-gray">{{ $course['category'] }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-4 gap-y-6 mb-12">
                                    @php
                                        $courseMeta = [
                                            [
                                                'label' => 'النوع',
                                                'value' => $course['typeLabel'] ?? 'دورة مسجلة',
                                                'iconClass' => 'icon-[tabler--bookmark]',
                                            ],
                                            [
                                                'label' => 'ساعات النشاط',
                                                'value' => $course['activityLabel'] ?? '0:00',
                                                'iconClass' => 'icon-[tabler--stopwatch]',
                                            ],
                                            [
                                                'label' => 'مدة',
                                                'value' => $course['durationLabel'] ?? '0:00',
                                                'iconClass' => 'icon-[tabler--clock]',
                                            ],
                                            [
                                                'label' => 'محاضرات',
                                                'value' => (string) ($course['sessionsCount'] ?? 0),
                                                'iconClass' => 'icon-[tabler--chalkboard]',
                                            ],
                                            [
                                                'label' => 'تكليفات',
                                                'value' => (string) ($course['assignmentsCount'] ?? 0),
                                                'iconClass' => 'icon-[tabler--clipboard-list]',
                                            ],
                                            [
                                                'label' => 'تاريخ التسجيل',
                                                'value' => $course['saleDateLabel'] ?? ($course['saleDate'] ?? ''),
                                                'iconClass' => 'icon-[tabler--calendar]',
                                            ],
                                        ];
                                    @endphp
                                    @foreach ($courseMeta as $meta)
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 mb-1.5">
                                                <span class="{{ $meta['iconClass'] }} size-4 text-[#5B6B6A] shrink-0" aria-hidden="true"></span>
                                                <span class="font-medium text-[12px] sm:text-[13px] text-[#5B6B6A] leading-none">{{ $meta['label'] }}</span>
                                            </div>
                                            <p class="font-bold text-[13px] sm:text-[14px] text-[#0F4C45] leading-snug">{{ $meta['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="bg-fa px-4 py-6 flex items-center justify-between rounded-10px">
                                    <div class="grow">
                                        <p class="font-semibold text-12px text-black mb-3">{{ $course['progress'] }}% متوسط معدل اتقدم</p>
                                        <div class="progress w-[70%] h-3 bg-[#F0EFEF]" role="progressbar"
                                            aria-label="Primary Progressbar" aria-valuenow="{{ $course['progress'] }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <div class="progress-bar progress-primary" style="width: {{ $course['progress'] }}%"></div>
                                        </div>
                                    </div>

                                    <a href="{{ route('panel.v1.student.course.watch', ['slug' => $course['slug']]) }}"
                                        class="btn btn-primary font-semibold text-12px">استكمل الان</a>
                                </div>
                                <div class="absolute top-6 end-6">
                                    <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                        <button type="button"
                                            class="dropdown-toggle size-10 rounded-[10px] bg-[#F3F4F6] hover:bg-[#E5E7EB] center border-0 cursor-pointer transition"
                                            aria-haspopup="menu" aria-expanded="false" aria-label="خيارات الدورة">
                                            <span class="icon-[tabler--dots] size-5 text-primary"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-[15.5rem] p-2 rounded-[12px] border border-d9 shadow-[0_12px_40px_rgba(15,76,69,0.10)] bg-white"
                                            role="menu" aria-orientation="vertical" aria-label="خيارات الدورة">
                                            <li>
                                                <a class="dropdown-item flex items-center gap-3 rounded-[8px] px-3 py-2.5 font-semibold text-[14px] text-primary hover:bg-[#F3F4F6] transition"
                                                    href="{{ route('panel.v1.student.course.watch', ['slug' => $course['slug']]) }}">
                                                    <span class="icon-[tabler--player-play] size-4 text-[#C9A46C] shrink-0" aria-hidden="true"></span>
                                                    <span>صفحة التعلم</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item flex items-center gap-3 rounded-[8px] px-3 py-2.5 font-semibold text-[14px] text-primary hover:bg-[#F3F4F6] transition"
                                                    href="{{ route('landing.v1.course-details', ['slug' => $course['slug']]) }}"
                                                    target="_blank" rel="noopener noreferrer">
                                                    <span class="icon-[tabler--info-circle] size-4 text-[#C9A46C] shrink-0" aria-hidden="true"></span>
                                                    <span>صفحة تفاصيل الدورة</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item flex items-center gap-3 rounded-[8px] px-3 py-2.5 font-semibold text-[14px] text-primary hover:bg-[#F3F4F6] transition"
                                                    href="{{ url('/panel/courses/' . $course['webinarId'] . '/sale/' . $course['saleId'] . '/invoice') }}"
                                                    target="_blank" rel="noopener noreferrer">
                                                    <span class="icon-[tabler--receipt] size-4 text-[#C9A46C] shrink-0" aria-hidden="true"></span>
                                                    <span>
                                                        تفاصيل الدفع
                                                        <span class="font-medium text-gray">(فاتورة)</span>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item flex items-center gap-3 rounded-[8px] px-3 py-2.5 font-semibold text-[14px] text-primary hover:bg-[#F3F4F6] transition"
                                                    href="{{ route('landing.v1.course-details', ['slug' => $course['slug']]) }}?tab=reviews"
                                                    target="_blank" rel="noopener noreferrer">
                                                    <span class="icon-[tabler--star] size-4 text-[#C9A46C] shrink-0" aria-hidden="true"></span>
                                                    <span>تقييم الدورة</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @php
                            $hasCourses = collect($enrolledCourses ?? [])->contains(function ($c) {
                                return !empty($c['hasWebinar']);
                            });
                        @endphp
                        @unless ($hasCourses)
                        <div class="center flex-col text-center py-16 rounded-12px border border-dashed border-d9 bg-fa/50 px-6">
                            <div class="mb-8">
                                <img src="{{ $noDataImg }}" alt="لم تشترك في أي دورة حتى الآن"
                                    class="max-w-[260px] w-full h-auto mx-auto" loading="lazy" decoding="async" width="320" height="240"
                                    onerror="this.style.display='none'; document.getElementById('no-data-fallback-icon')?.classList.remove('hidden')">
                                <div id="no-data-fallback-icon" class="hidden center flex-col gap-3">
                                    <span class="icon-[tabler--books] size-16 text-primary/30"></span>
                                    <span class="font-medium text-14px text-primary/50">لا توجد بيانات</span>
                                </div>
                            </div>
                            <h2 class="font-bold text-32px text-black mb-4">لم تشترك في أي دورة حتى الآن</h2>
                            <p class="font-medium text-18px text-gray mb-10 max-w-2xl leading-relaxed mx-auto">
                                ابدأ رحلتك التعليمية اليوم وتصفح مئات الدورات والورش المعتمدة المتاحة على المنصة.
                            </p>
                            <a href="{{ route('landing.v1.courses-paid') }}"
                                class="btn btn-primary rounded-8px px-10 h-13 font-bold text-16px">
                                🚀 استكشف الدورات المتاحة
                            </a>
                        </div>
                        @endunless
                    </div>
                    <div class="lg:col-span-4">
                        <div class="border border-d9 rounded-12px mb-8 ">

                            <h5 class="px-10 py-8 font-medium text-28px text-black mb-8 text-center">اختر تاريخا لاضافة
                                حدث جديد</h5>
                            <div>
                                @include('panel_v1.student.components.calendar-widget', [
                                    'calendarYear' => $calendarYear ?? now()->year,
                                    'calendarMonth' => $calendarMonth ?? now()->month,
                                    'calendarSelected' => $calendarSelected ?? now()->day,
                                    'calendarEventDates' => $calendarEventDates ?? [],
                                ])
                            </div>


                        </div>

                        <div class="px-10 py-8 border border-d9 rounded-12px" data-student-calendar-events>
                            @if (($calendarEvents ?? collect())->isNotEmpty())
                                <h6 class="font-semibold text-24px text-black mb-4">أحداثك القادمة</h6>
                                <ul class="space-y-3">
                                    @foreach ($calendarEvents as $calendarEvent)
                                        <li class="flex items-start justify-between gap-3 rounded-10px border border-d9 bg-fa/40 px-4 py-3">
                                            <div class="min-w-0">
                                                <p class="font-bold text-14px text-primary leading-snug">{{ $calendarEvent->title }}</p>
                                                <p class="font-medium text-12px text-gray mt-1">
                                                    {{ $calendarEvent->event_date->format('Y/m/d') }}
                                                </p>
                                                @if (!empty($calendarEvent->notes))
                                                    <p class="font-medium text-12px text-gray/80 mt-1 line-clamp-2">{{ $calendarEvent->notes }}</p>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('panel.v1.student.calendar-events.delete', $calendarEvent->id) }}" class="shrink-0">
                                                @csrf
                                                <button type="submit" class="size-8 rounded-full center text-gray hover:text-red-600 hover:bg-red-50 transition" aria-label="حذف الحدث" title="حذف">
                                                    <span class="icon-[tabler--trash] size-4"></span>
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <h6 class="font-semibold text-24px text-black mb-3">
                                    لا توجد احداث حالية
                                </h6>
                                <p class="font-medium text-base text-gray">اضف احداث لتظهر</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabs-large-2" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-2">
                @forelse ($liveSessions ?? [] as $liveSession)
                    @php
                        $status = $liveSession['status'] ?? 'upcoming';
                        $isLive = $status === 'live';
                        $isCompleted = $status === 'completed';
                    @endphp

                    <article @class([
                        'rounded-[16px] border overflow-hidden mb-5',
                        'border-[#FECACA] bg-[#FFF1F2]' => $isLive,
                        'border-d9 bg-white' => !$isLive,
                    ])>
                        <div class="px-6 sm:px-8 pt-6 pb-5 flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="inline-flex items-center gap-2 font-bold text-[14px] text-primary mb-3">
                                    @if ($isLive)
                                        <span class="size-2.5 rounded-full bg-[#EF4444] shrink-0" aria-hidden="true"></span>
                                        محاضرة مباشرة
                                    @elseif ($isCompleted)
                                        <span class="size-5 rounded-[6px] bg-[#16A34A] center shrink-0" aria-hidden="true">
                                            <span class="icon-[tabler--check] size-3.5 text-white"></span>
                                        </span>
                                        محاضرة مكتملة
                                    @else
                                        <span class="size-2.5 rounded-full bg-primary shrink-0" aria-hidden="true"></span>
                                        محاضرة قادمة
                                    @endif
                                </p>

                                <h3 class="font-bold text-[18px] sm:text-[20px] text-primary leading-snug mb-2">
                                    عنوان المحاضرة : {{ $liveSession['title'] }}
                                </h3>
                                @if (!empty($liveSession['instructorName']))
                                    <p class="font-medium text-[14px] text-gray">
                                        المحاضر: {{ $liveSession['instructorName'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3 shrink-0">
                                @if ($isCompleted)
                                    @if (!empty($liveSession['pdfUrl']))
                                        <a href="{{ $liveSession['pdfUrl'] }}" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-ghost rounded-[10px] h-11 px-5 font-bold text-[13px] text-primary border border-primary/30 hover:bg-fa">
                                            تحميل ملفات المحاضرة (PDF)
                                        </a>
                                    @else
                                        <a href="{{ $liveSession['watchUrl'] }}"
                                            class="btn btn-ghost rounded-[10px] h-11 px-5 font-bold text-[13px] text-primary border border-primary/30 hover:bg-fa">
                                            تحميل ملفات المحاضرة (PDF)
                                        </a>
                                    @endif
                                    <a href="{{ $liveSession['watchUrl'] }}"
                                        class="btn btn-primary rounded-[10px] h-11 px-6 font-bold text-[13px]">
                                        اعادة المحاضرة
                                    </a>
                                @else
                                    <a href="{{ $liveSession['watchUrl'] }}"
                                        class="btn btn-primary rounded-[10px] h-11 px-6 font-bold text-[13px]">
                                        الانضمام إلى المحاضرة
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div @class([
                            'mx-6 sm:mx-8 mb-6 rounded-[10px] px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 font-medium text-[13px]',
                            'bg-[#FECDD3]/70 text-[#7F1D1D]' => $isLive,
                            'bg-[#F3F4F6] text-primary' => !$isLive,
                        ])>
                            <span>{{ $liveSession['scheduleText'] }}</span>
                            <span>{{ $liveSession['durationText'] }}</span>
                        </div>
                    </article>
                @empty
                    <div class="py-10 center flex-col text-center border border-dashed border-[#E5E7EB] rounded-[16px] bg-[#F9FAFB]">
                        <div class="size-16 rounded-full bg-white border border-[#E5E7EB] center mb-3">
                            <span class="icon-[tabler--video] size-7 text-[#9CA3AF]"></span>
                        </div>
                        <p class="font-bold text-[15px] text-[#0F3D36]">لا توجد محاضرات مباشرة</p>
                        <p class="font-medium text-[12px] text-[#9CA3AF] mt-1">سيظهر هنا جدول محاضراتك المباشرة والمكتملة</p>
                    </div>
                @endforelse
            </div>
            <div id="tabs-large-3" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-3">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('panel.v1.student.assignments') }}" class="font-bold text-14px text-primary hover:underline">عرض كل التكليفات ←</a>
                </div>

                @php
                    $hasAssignments = ($pendingAssignments ?? collect())->isNotEmpty()
                        || ($submittedHistories ?? collect())->isNotEmpty();
                @endphp

                @if ($hasAssignments)
                    {{-- Pending cards --}}
                    <div class="bg-[#F3F4F6] rounded-[12px] px-6 py-4 mb-6">
                        <p class="font-bold text-[20px] sm:text-[22px] text-primary">التكليفات المعلقة</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-5 mb-12">
                        @forelse ($pendingAssignments ?? [] as $pendingAssignment)
                            @php
                                $attemptsLabel = empty($pendingAssignment->attempts) || (int) $pendingAssignment->attempts < 1
                                    ? 'غير محدود'
                                    : ((int) $pendingAssignment->attempts . ' محاولة');
                                $gradeLabel = ($pendingAssignment->grade ?? null) !== null
                                    ? ((int) $pendingAssignment->grade . ' درجة')
                                    : '—';
                                $assignmentTitle = $pendingAssignment->translate('ar')?->title
                                    ?: ($pendingAssignment->title ?: 'تكليف');
                            @endphp
                            <div class="border border-d9 rounded-[16px] bg-white p-6 flex flex-col">
                                <div class="size-11 rounded-[10px] bg-primary/10 center mb-5">
                                    <span class="icon-[tabler--clipboard-list] size-6 text-primary" aria-hidden="true"></span>
                                </div>

                                <h4 class="font-bold text-[18px] sm:text-[20px] text-primary leading-snug mb-2">
                                    {{ $assignmentTitle }}
                                </h4>
                                <p class="font-medium text-[13px] sm:text-[14px] text-gray mb-5 leading-relaxed">
                                    {{ $pendingAssignment->webinar->title ?? '' }}
                                </p>

                                <div class="flex flex-wrap items-center gap-2 mb-6">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FEF6E7] px-3 py-1.5 font-semibold text-[12px] text-primary">
                                        <span class="icon-[tabler--trophy] size-3.5 text-[#C9A46C]" aria-hidden="true"></span>
                                        {{ $gradeLabel }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F3F4F6] px-3 py-1.5 font-semibold text-[12px] text-primary">
                                        <span class="icon-[tabler--hourglass] size-3.5 text-[#C9A46C]" aria-hidden="true"></span>
                                        {{ $attemptsLabel }}
                                    </span>
                                </div>

                                <a href="{{ route('panel.v1.student.course.assignment', ['slug' => $pendingAssignment->webinar->slug ?? 'demo']) }}"
                                    class="btn btn-primary rounded-[10px] h-12 font-bold text-[14px] w-full mt-auto">
                                    عرض التكليف
                                </a>
                            </div>
                        @empty
                            <div class="md:col-span-2 xl:col-span-3 border border-dashed border-d9 rounded-[16px] bg-fa/40 px-6 py-10 text-center">
                                <p class="font-semibold text-[15px] text-gray">لا توجد تكليفات معلقة حالياً</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Submitted list --}}
                    <div class="bg-[#F3F4F6] rounded-[12px] px-6 py-4 mb-6">
                        <p class="font-bold text-[20px] sm:text-[22px] text-primary">جميع التكليفات المسلمة</p>
                    </div>

                    @forelse ($submittedHistories ?? [] as $history)
                        @php
                            $assignment = $history->assignment;
                            $historyTitle = $assignment?->translate('ar')?->title
                                ?: ($assignment->title ?? 'تكليف');
                            $deadlineTs = null;
                            try {
                                $deadlineTs = $assignment?->getDeadlineTimestamp($authUser ?? auth()->user());
                            } catch (\Throwable $e) {
                                $deadlineTs = null;
                            }
                            $messages = $history->messages ?? collect();
                            $firstMsg = $messages->sortBy('created_at')->first();
                            $lastMsg = $messages->sortByDesc('created_at')->first();
                            $attemptsUsed = $messages->count();
                            $statusLabel = match ($history->status ?? '') {
                                'passed' => 'ناجح',
                                'not_passed' => 'راسب',
                                'pending' => 'بانتظار التقييم',
                                default => '—',
                            };
                        @endphp

                        <div class="border border-d9 rounded-[14px] bg-white mb-4 overflow-x-auto">
                            <div class="min-w-max w-full flex items-start gap-6 px-5 sm:px-7 py-5">
                                <div class="shrink-0 min-w-[220px] max-w-[260px]">
                                    <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">العنوان والدورة</p>
                                    <p class="font-bold text-[15px] text-primary leading-snug mb-1">{{ $historyTitle }}</p>
                                    <p class="font-medium text-[12px] text-gray">{{ $assignment->webinar->title ?? '' }}</p>
                                </div>

                                <div class="flex-1 flex items-start justify-between gap-4 min-w-[640px]">
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">الموعد النهائي</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">
                                            {{ !empty($deadlineTs) ? date('Y/m/d', (int) $deadlineTs) : '—' }}
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">التسليم الأول</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">
                                            {{ !empty($firstMsg?->created_at) ? date('Y/m/d', (int) $firstMsg->created_at) : (!empty($history->created_at) ? date('Y/m/d', (int) $history->created_at) : '—') }}
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">التسليم الأخير</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">
                                            {{ !empty($lastMsg?->created_at) ? date('Y/m/d', (int) $lastMsg->created_at) : (!empty($history->created_at) ? date('Y/m/d', (int) $history->created_at) : '—') }}
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">المحاولات</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">
                                            {{ $attemptsUsed > 0 ? $attemptsUsed : '—' }}
                                            @if (!empty($assignment?->attempts))
                                                <span class="text-gray">/ {{ $assignment->attempts }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">الدرجة</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">{{ $history->grade ?? '—' }}</p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">درجة النجاح</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">{{ $assignment->pass_grade ?? '—' }}</p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">حالة</p>
                                        <p class="font-semibold text-[13px] text-primary whitespace-nowrap">{{ $statusLabel }}</p>
                                    </div>
                                    <div class="shrink-0">
                                        <p class="font-medium text-[12px] text-gray mb-2 whitespace-nowrap">اجراءات</p>
                                        <a href="{{ route('panel.v1.student.course.assignment', ['slug' => $assignment->webinar->slug ?? 'demo']) }}"
                                            class="inline-flex items-center justify-center size-9 rounded-full border border-d9 text-primary hover:bg-fa transition whitespace-nowrap"
                                            aria-label="عرض التكليف" title="عرض">
                                            <span class="icon-[tabler--eye] size-4" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="border border-dashed border-d9 rounded-[16px] bg-fa/40 px-6 py-10 text-center">
                            <p class="font-semibold text-[15px] text-gray">لا توجد تكليفات مسلمة بعد</p>
                        </div>
                    @endforelse
                @else
                    <div class="py-14 center flex-col text-center">
                        <div class="w-[260px] h-[180px] rounded-[16px] bg-[#F3F4F6] border border-[#E5E7EB] center mb-6 overflow-hidden">
                            <div class="text-center">
                                <div class="mx-auto size-20 rounded-full bg-white border border-[#E5E7EB] center mb-3">
                                    <span class="icon-[tabler--clipboard-list] size-10 text-[#9CA3AF]"></span>
                                </div>
                                <div class="flex justify-center gap-1.5">
                                    <span class="size-8 rounded-[8px] bg-[#E5E7EB]"></span>
                                    <span class="size-8 rounded-[8px] bg-[#E5E7EB]"></span>
                                    <span class="size-8 rounded-[8px] bg-[#C99C69]/20"></span>
                                </div>
                            </div>
                        </div>
                        <p class="font-bold text-[18px] text-[#0F3D36]">لا يوجد لديك واجب بعد</p>
                        <p class="font-medium text-[13px] text-[#9CA3AF] mt-1">سيظهر هنا أي تكليف يضيفه مدربك.</p>
                    </div>
                @endif
            </div>

            <div id="tabs-large-4" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-4">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('panel.v1.student.quizzes') }}" class="font-bold text-14px text-primary hover:underline">عرض كل الاختبارات ←</a>
                </div>
                @forelse ($quizResults ?? [] as $quizResult)
                    <div class="border border-d9 rounded-10px px-7 py-6 flex items-center justify-between mb-8 gap-16">
                        <div>
                            <p class="font-bold text-18px text-primary">{{ $quizResult->quiz->title ?? '' }}</p>
                            <p class="font-medium text-12px text-primary">{{ $quizResult->quiz->webinar->title ?? '' }}</p>
                        </div>
                        <div class="flex items-center gap-8">
                            <div>
                                <p class="font-medium text-16px text-gray mb-12">الدرجة</p>
                                <p class="text-center text-black">{{ $quizResult->user_grade ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="font-medium text-16px text-gray mb-12">الحالة</p>
                                <p class="text-center text-black">{{ ($quizResult->status ?? '') === 'passed' ? 'ناجح' : ((($quizResult->status ?? '') === 'waiting') ? 'بانتظار التصحيح' : 'راسب') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-14 center flex-col text-center">
                        <div class="w-[260px] h-[180px] rounded-[16px] bg-[#F3F4F6] border border-[#E5E7EB] center mb-6 overflow-hidden">
                            <div class="text-center">
                                <div class="mx-auto size-20 rounded-full bg-white border border-[#E5E7EB] center mb-3">
                                    <span class="icon-[tabler--checklist] size-10 text-[#9CA3AF]"></span>
                                </div>
                                <div class="flex justify-center gap-1.5">
                                    <span class="size-8 rounded-[8px] bg-[#E5E7EB]"></span>
                                    <span class="size-8 rounded-[8px] bg-[#E5E7EB]"></span>
                                    <span class="size-8 rounded-[8px] bg-[#C99C69]/20"></span>
                                </div>
                            </div>
                        </div>
                        <p class="font-bold text-[18px] text-[#0F3D36]">لا يوجد لديك اختبار بعد</p>
                        <p class="font-medium text-[13px] text-[#9CA3AF] mt-1">ستظهر هنا اختبارات دوراتك.</p>
                    </div>
                @endforelse
            </div>
            <div id="tabs-large-5" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-5">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('panel.v1.student.certificates') }}" class="font-bold text-14px text-primary hover:underline">عرض كل الشهادات ←</a>
                </div>
                @if (($certificates ?? collect())->isEmpty())
                    <div class="py-14 center flex-col text-center border border-dashed border-[#E5E7EB] rounded-[16px] bg-[#F9FAFB]">
                        <div class="w-[200px] h-[140px] rounded-[12px] bg-white border border-[#E5E7EB] center mb-4">
                            <span class="icon-[tabler--certificate] size-10 text-[#9CA3AF]"></span>
                        </div>
                        <p class="font-bold text-[16px] text-[#0F3D36]">لا توجد شهادات بعد</p>
                        <p class="font-medium text-[13px] text-[#9CA3AF] mt-1">أكمل دوراتك للحصول على الشهادة</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach ($certificates as $certificate)
                            <div class="rounded-[16px] border border-[#E5E7EB] bg-white overflow-hidden">
                                <div class="bg-[#0F3D36] h-[160px] center relative">
                                    <span class="icon-[tabler--certificate] size-12 text-white/90"></span>
                                    <span class="absolute top-3 end-3 bg-white/15 text-white text-[11px] font-bold px-2.5 py-1 rounded-full">معتمدة</span>
                                </div>
                                <div class="p-5">
                                    <h5 class="font-bold text-[16px] text-[#0F3D36] leading-snug mb-1">{{ $certificate->webinar->title ?? 'شهادة إتمام' }}</h5>
                                    <p class="font-medium text-[12px] text-[#9CA3AF] mb-4">اكتمل في {{ date('Y/m/d', (int) $certificate->created_at) }}</p>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('panel.v1.student.certificates.download', ['id' => $certificate->id]) }}" class="flex-1 btn btn-primary rounded-[10px] h-9 font-bold text-[13px] center">تحميل الشهادة</a>
                                        @if (!empty($certificate->webinar))
                                            <a href="{{ route('panel.v1.student.course.watch', ['slug' => $certificate->webinar->slug]) }}" class="btn btn-ghost rounded-[10px] h-9 px-4 font-bold text-[13px] text-[#0F3D36] border border-[#E5E7EB]">عرض</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div id="tabs-large-6" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-6">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('panel.v1.student.comments') }}" class="font-bold text-14px text-primary hover:underline">عرض كل التعليقات ←</a>
                </div>
                @forelse ($comments ?? [] as $comment)
                    <div class="border border-d9 rounded-10px px-7 py-5 mb-4">
                        <p class="font-bold text-16px text-primary mb-1">{{ $comment->webinar->title ?? '' }}</p>
                        <p class="font-medium text-14px text-gray">{{ \Illuminate\Support\Str::limit(strip_tags($comment->comment ?? ''), 200) }}</p>
                        <p class="font-medium text-12px text-gray mt-2">{{ date('Y/m/d', (int) $comment->created_at) }}</p>
                    </div>
                @empty
                    <p class="text-base-content/80 text-lg">لا توجد تعليقات بعد.</p>
                @endforelse
            </div>
        </div>

        @include('panel_v1.student.components.assignment-submit-modal')
        @include('panel_v1.student.components.calendar-event-modal')
    </div>
</section>


@endsection
