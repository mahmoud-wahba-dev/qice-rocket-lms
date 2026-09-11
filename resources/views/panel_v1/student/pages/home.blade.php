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

<header class="overflow-hidden text-white pt-6 mt-8">
    <div class="container">
        {{-- Figma: dark teal header #0F4C45 with gold accent, rounded 16px, subtle pattern --}}
        <div class="bg-[#0F3D36] relative rounded-[16px] overflow-hidden px-6 sm:px-8 lg:px-10 py-8 lg:py-9">
            {{-- subtle dotted pattern top-left like Figma --}}
            <div class="pointer-events-none absolute top-0 start-0 w-40 h-40 opacity-[0.08]" aria-hidden="true">
                <svg viewBox="0 0 100 100" class="w-full h-full" fill="none"><circle cx="20" cy="20" r="1.5" fill="white"/><circle cx="40" cy="20" r="1.5" fill="white"/><circle cx="60" cy="20" r="1.5" fill="white"/><circle cx="20" cy="40" r="1.5" fill="white"/><circle cx="40" cy="40" r="1.5" fill="white"/><circle cx="60" cy="40" r="1.5" fill="white"/></svg>
            </div>
            <div class="relative">
                <p class="font-bold text-[13px] tracking-wide text-white/70 mb-1">مرحباً بك</p>
                <h1 class="font-bold text-[28px] sm:text-[30px] leading-tight mb-1">طالب مساقك ، {{ $authUser->full_name ?? "علا محمد" }}</h1>
                <p class="font-medium text-[13px] text-white/75">جاهزة لاستكمال رحلتك التعليمية اليوم؟</p>
            </div>
            {{-- 5 stat cards — Figma exact: 5 equal cards, gold icons, white 12% bg --}}
            <div class="relative mt-7 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                @php
                    $figmaStats = [
                        ['value' => $stats["activeCourses"] ?? 4, 'label' => 'الدورات المسجلة', 'icon' => 'tabler--book'],
                        ['value' => $stats["learningHours"] ?? 0, 'label' => 'ساعات التعلم', 'icon' => 'tabler--clock'],
                        ['value' => $stats["upcomingSessions"] ?? 0, 'label' => 'محاضرات قادمة', 'icon' => 'tabler--calendar'],
                        ['value' => $stats["assignments"] ?? 0, 'label' => 'تكليفات', 'icon' => 'tabler--clipboard-list'],
                        ['value' => $stats["certificates"] ?? 2, 'label' => 'الشهادات', 'icon' => 'tabler--certificate'],
                    ];
                @endphp
                @foreach ($figmaStats as $stat)
                    <div class="bg-white/[0.09] backdrop-blur rounded-[12px] border border-white/[0.10] px-4 py-4 text-center">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <span class="icon-[{{ $stat['icon'] }}] size-[18px] text-[#C99C69]"></span>
                            <span class="font-bold text-[26px] leading-none text-white">{{ $stat['value'] }}</span>
                        </div>
                        <p class="font-medium text-[11px] leading-tight text-white/80">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
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
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-1" data-v1-tab="#tabs-large-1" aria-controls="tabs-large-1" role="tab" aria-selected="false">دوراتي</button>
            <button type="button" class="{{ $tabBase }} border-[#0F3D36] text-[#0F3D36] active" id="tabs-large-item-2" data-v1-tab="#tabs-large-2" aria-controls="tabs-large-2" role="tab" aria-selected="true">المحاضرات المباشرة</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-3" data-v1-tab="#tabs-large-3" aria-controls="tabs-large-3" role="tab" aria-selected="false">تكليفات المحاضرات</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-4" data-v1-tab="#tabs-large-4" aria-controls="tabs-large-4" role="tab" aria-selected="false">الاختبارات</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-5" data-v1-tab="#tabs-large-5" aria-controls="tabs-large-5" role="tab" aria-selected="false">الشهادات</button>
            <button type="button" class="{{ $tabBase }} border-transparent text-[#9CA3AF] hover:text-[#0F3D36]" id="tabs-large-item-6" data-v1-tab="#tabs-large-6" aria-controls="tabs-large-6" role="tab" aria-selected="false">التعليقات</button>
        </nav>

        <div class="">
            <div id="tabs-large-1" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-1">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-13">
                        @foreach (($enrolledCourses ?? []) as $course)
                            @if (!empty($course['hasWebinar']))
                            <div class="relative border border-d9 px-6 py-8 rounded-12px mb-12">
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

                                <div class="flex items-center gap-13 justify-between flex-wrap mb-12">
                                    <div>
                                        <div class="center gap-3 mb-2">
                                            <span class="font-medium text-base text-gray">النوع</span>
                                        </div>
                                        <p class="font-medium text-base text-primary text-center">دورة مسجلة</p>
                                    </div>
                                    <div>
                                        <div class="center gap-3 mb-2">
                                            <span class="font-medium text-base text-gray">محاضرات</span>
                                        </div>
                                        <p class="font-medium text-base text-primary text-center">{{ $course['sessionsCount'] }}</p>
                                    </div>
                                    <div>
                                        <div class="center gap-3 mb-2">
                                            <span class="font-medium text-base text-gray">تاريخ التسجيل</span>
                                        </div>
                                        <p class="font-medium text-base text-primary text-center">{{ $course['saleDate'] }}</p>
                                    </div>
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
                                            class="dropdown-toggle btn btn-square " aria-haspopup="menu"
                                            aria-expanded="false" aria-label="Dropdown">
                                            <span class="icon-[tabler--dots] size-6"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu"
                                            aria-orientation="vertical" aria-label="Dropdown">
                                            <li><a class="dropdown-item font-medium text-14px text-primary"
                                                    href="{{ route('panel.v1.student.course.watch', ['slug' => $course['slug']]) }}">
                                                    صفحة التعلم
                                                </a></li>
                                            <li><a class="dropdown-item font-medium text-14px text-primary"
                                                    href="{{ url('/panel/courses/' . $course['webinarId'] . '/sale/' . $course['saleId'] . '/invoice') }}">
                                                    تفاصيل الدفع (فاتورة)
                                                </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        {{-- Empty state when student has no courses — set $hasCourses = true when courses exist --}}
                        @php($hasCourses = ($enrolledCourses ?? collect())->isNotEmpty())
                        @unless ($hasCourses)
                        <div class="col-span-12 lg:col-span-8 center flex-col text-center py-16 rounded-12px border border-dashed border-d9 bg-fa/50 px-6">
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
                    <div class="col-span-12 lg:col-span-4">
                        <div class="border border-d9 rounded-12px mb-8 ">

                            <h5 class="px-10 py-8 font-medium text-28px text-black mb-8 text-center">اختر تاريخا لاضافة
                                حدث جديد</h5>
                            <div>
                                @include('panel_v1.student.components.calendar-widget', [
                                'calendarYear' => $calendarYear ?? now()->year,
                                'calendarMonth' => $calendarMonth ?? now()->month,
                                'calendarSelected' => $calendarSelected ?? now()->day,
                                ])
                            </div>


                        </div>

                        <div class="px-10 py-8 border border-d9 rounded-12px ">
                            <h6 class="font-semibold text-24px text-black mb-3">
                                لا توجد احداث حالية
                            </h6>
                            <p class="font-medium text-base text-gray">اضف احداث لتظهر</p>
                        </div>
                    </div>
                </div>


            </div>
            <div id="tabs-large-2" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-2">
                @forelse ($liveSessions ?? [] as $liveSession)
                    <div class="rounded-[16px] border border-[#FFE4E6] bg-[#FFF1F2] overflow-hidden mb-5">
                        <div class="px-6 sm:px-7 py-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="inline-flex items-center gap-2 font-bold text-[13px] text-[#DC2626] mb-2"><span class="size-2 rounded-full bg-[#DC2626] animate-pulse"></span> محاضرة مباشرة</p>
                                    <h3 class="font-bold text-[18px] text-[#0F3D36] leading-snug mb-1">{{ $liveSession->title }}</h3>
                                    <p class="font-medium text-[13px] text-[#6B7280]">الدورة: {{ $liveSession->webinar->title ?? '' }}</p>
                                </div>
                                <a href="{{ route('panel.v1.student.course.watch', ['slug' => $liveSession->webinar->slug ?? 'demo']) }}" class="btn btn-primary rounded-[10px] h-10 px-6 font-bold text-[13px] shrink-0">الانضمام الآن</a>
                            </div>
                        </div>
                        <div class="mx-4 mb-4 rounded-[10px] bg-[#FECDD3]/60 border border-[#FECDD3] px-4 py-3 flex flex-wrap items-center justify-between gap-3 font-medium text-[12px] text-[#881337]">
                            <span>📅 الموعد: {{ date('Y/m/d H:i', (int) $liveSession->date) }}</span>
                            <span>⏱ المدة: {{ $liveSession->duration ?? 0 }} دقيقة</span>
                        </div>
                    </div>
                @empty
                    <div class="py-10 center flex-col text-center border border-dashed border-[#E5E7EB] rounded-[16px] bg-[#F9FAFB]">
                        <div class="size-16 rounded-full bg-white border border-[#E5E7EB] center mb-3">
                            <span class="icon-[tabler--video] size-7 text-[#9CA3AF]"></span>
                        </div>
                        <p class="font-bold text-[15px] text-[#0F3D36]">لا توجد محاضرات مباشرة قادمة</p>
                        <p class="font-medium text-[12px] text-[#9CA3AF] mt-1">سيظهر هنا جدول محاضراتك المباشرة</p>
                    </div>
                @endforelse
            </div>
            <div id="tabs-large-3" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-3">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('panel.v1.student.assignments') }}" class="font-bold text-14px text-primary hover:underline">عرض كل التكليفات ←</a>
                </div>
                @php($hasAssignments = ($pendingAssignments ?? collect())->isNotEmpty() || ($submittedHistories ?? collect())->isNotEmpty())
                @if ($hasAssignments)
                    <div>
                        <div class="bg-[#F9F5F5] rounded-4px px-7 py-3 mb-10 ">
                            <p class="font-semibold text-24px text-primary">التكليفات المعلقة</p>
                        </div>
                        <div class="grid grid-cols-[repeat(auto-fill,minmax(25%,1fr))] gap-8 mb-15">
                            @forelse ($pendingAssignments ?? [] as $pendingAssignment)
                                <div class="border border-d9 p-8 rounded-8px">
                                    <div class="flex items-center gap-3 mb-7">
                                        <div>
                                            <h4 class="font-bold text-20px text-primary">
                                                تكليف دورة {{ $pendingAssignment->webinar->title ?? '' }}
                                            </h4>
                                            <p class="font-semibold text-12px text-primary">
                                                الدرجة: {{ $pendingAssignment->grade ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('panel.v1.student.course.assignment', ['slug' => $pendingAssignment->webinar->slug ?? 'demo']) }}"
                                            class="btn btn-primary btn-block">عرض التكليف</a>
                                    </div>
                                </div>
                            @empty
                                <p class="font-medium text-16px text-gray">لا توجد تكليفات معلقة.</p>
                            @endforelse
                        </div>
                        <div class="bg-[#F9F5F5] rounded-4px px-7 py-3 mb-10 ">
                            <p class="font-semibold text-24px text-primary">جميع التكليفات المسلمة</p>
                        </div>

                        @forelse ($submittedHistories ?? [] as $history)
                        <div class="border border-d9 rounded-10px px-7 py-6 flex items-center justify-between mb-8 gap-16">
                            <div>
                                <span class="font-medium text-14px text-gray mb-1.5">العنوان والدورة</span>
                                <p class="font-bold text-18px text-primary">تكليف: {{ $history->assignment->webinar->title ?? '' }}</p>
                            </div>
                            <div class="flex items-center gap-8">
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">الدرجة</p>
                                    <p class="text-center text-black">{{ $history->grade ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">حالة</p>
                                    <p class="text-center text-black">{{ ($history->status ?? '') === 'passed' ? 'ناجح' : ((($history->status ?? '') === 'not_passed') ? 'راسب' : 'بانتظار التقييم') }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                            <p class="font-medium text-16px text-gray">لا توجد تكليفات مسلمة بعد.</p>
                        @endforelse

                    </div>
                @else
                    {{-- Figma empty: illustration + centered text --}}
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
</section>


@endsection
