@extends('panel_v1.student.layouts.app')

@section('content')
@php($heroLogo = $panelStudentImg . '/hero-logo.webp')
@php($noDataImg = $panelStudentImg . '/no-data.webp')

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

<header class="overflow-hidden text-white pt-8 mt-20 ">
    <div class="container ">
        <div class="px-12 bg-primary relative rounded-20px max-xl:px-5 max-xl:py-10 overflow-hidden">
            <div class="px-15 py-16">
                <h1 class="font-bold text-40px  mb-1">طاب مساؤك ، {{ $authUser->full_name ?? "" }}</h1>
                @php($firstEnrolledCourse = ($enrolledCourses ?? collect())->first())
                <p class="font-semibold text-20px mb-15">جاهزة لاستكمال دورة "{{ $firstEnrolledCourse['title'] ?? 'التعلم' }}" اليوم؟</p>

                <div class="grid grid-cols-1 grid-cols-[repeat(auto-fit,minmax(210px,1fr))] gap-10">
                    <div class="bg-[#FFFFFF29] rounded-12px p-7">
                        <div class="mb-3 center gap-2">
                            <span class="font-semibold text-40px">{{ $stats["activeCourses"] ?? 0 }}</span>
                            <svg width="32" height="27" viewBox="0 0 32 27" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5.33333 12.444L0 9.33333L16 0L32 9.33333V20.6667H29.3333V10.8893L26.6667 12.444V21.348L26.3693 21.7147C25.1208 23.2624 23.5412 24.5107 21.7468 25.3677C19.9523 26.2246 17.9886 26.6685 16 26.6667C14.0114 26.6685 12.0477 26.2246 10.2532 25.3677C8.45878 24.5107 6.87921 23.2624 5.63067 21.7147L5.33333 21.348V12.444ZM8 14V20.3893C8.99994 21.5256 10.2308 22.4354 11.6103 23.058C12.9899 23.6806 14.4864 24.0018 16 24C17.5136 24.0018 19.0101 23.6806 20.3897 23.058C21.7692 22.4354 23.0001 21.5256 24 20.3893V14L16 18.6667L8 14ZM5.29333 9.33333L16 15.58L26.7067 9.33333L16 3.08667L5.29333 9.33333Z"
                                    fill="#C99C69" />
                            </svg>

                        </div>


                        <p class="font-semibold text-18px text-center">الدورات النشطة</p>

                    </div>
                    <div class="bg-[#FFFFFF29] rounded-12px p-7">
                        <div class="mb-3 center gap-2">
                            <span class="font-semibold text-40px">{{ $stats["learningHours"] ?? 0 }}</span>
                            <svg width="24" height="26" viewBox="0 0 24 26" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.27441 1.33744L22.3544 1.27344M1.27441 24.6068L22.3544 24.5428"
                                    stroke="#C99C69" stroke-width="2.54795" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M20.3815 1.35352H3.24547L3.35081 3.28552C3.48704 5.77 4.55945 8.11063 6.35214 9.83618L8.00814 11.4295C8.21281 11.6261 8.37565 11.862 8.48689 12.1231C8.59813 12.3842 8.65547 12.6651 8.65547 12.9489C8.65547 13.2326 8.59813 13.5135 8.48689 13.7746C8.37565 14.0357 8.21281 14.2716 8.00814 14.4682L6.35214 16.0602C4.55945 17.7857 3.48704 20.1264 3.35081 22.6109L3.24414 24.5415H20.3815L20.2761 22.6149C20.1395 20.1276 19.0645 17.7847 17.2681 16.0588L15.6121 14.4669C15.4073 14.2702 15.2442 14.0342 15.1329 13.773C15.0215 13.5118 14.9641 13.2308 14.9641 12.9468C14.9641 12.6629 15.0215 12.3819 15.1329 12.1207C15.2442 11.8595 15.4073 11.6235 15.6121 11.4268L17.2681 9.83618C19.0648 8.11004 20.1398 5.76662 20.2761 3.27885L20.3815 1.35352Z"
                                    stroke="#C99C69" stroke-width="2.54795" stroke-linejoin="round" />
                            </svg>


                        </div>


                        <p class="font-semibold text-18px text-center">ساعات التعلم</p>

                    </div>
                    <div class="bg-[#FFFFFF29] rounded-12px p-7">
                        <div class="mb-3 center gap-2">
                            <span class="font-semibold text-40px">{{ $stats["upcomingSessions"] ?? 0 }}</span>
                            <svg width="27" height="30" viewBox="0 0 27 30" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 1.33398V6.66732M18.6667 1.33398V6.66732" stroke="#C99C69"
                                    stroke-width="2.66667" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M22.6663 4H3.99967C2.52692 4 1.33301 5.19391 1.33301 6.66667V25.3333C1.33301 26.8061 2.52692 28 3.99967 28H22.6663C24.1391 28 25.333 26.8061 25.333 25.3333V6.66667C25.333 5.19391 24.1391 4 22.6663 4Z"
                                    stroke="#C99C69" stroke-width="2.66667" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M1.33301 12H25.333M7.99967 17.3333H8.01301M13.333 17.3333H13.3463M18.6663 17.3333H18.6797M7.99967 22.6667H8.01301M13.333 22.6667H13.3463M18.6663 22.6667H18.6797"
                                    stroke="#C99C69" stroke-width="2.66667" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>


                        </div>


                        <p class="font-semibold text-18px text-center">محاضرات قادمة (المباشرة)</p>

                    </div>
                    <div class="bg-[#FFFFFF29] rounded-12px p-7">
                        <div class="mb-3 center gap-2">
                            <span class="font-semibold text-40px">{{ $stats["assignments"] ?? 0 }}</span>
                            <svg width="24" height="31" viewBox="0 0 24 31" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M0 3.2C0 1.4368 1.4368 0 3.2 0H13.904C14.8032 0 15.6512 0.3808 16.248 1.0096L16.2576 1.0208L23.1568 8.544C23.7312 9.1568 24 9.9584 24 10.72V27.2C24 28.9632 22.5632 30.4 20.8 30.4H3.2C1.4368 30.4 0 28.9632 0 27.2V3.2ZM13.9024 3.2H3.2V27.2H20.8V10.7072L13.9264 3.2112L13.9216 3.2096L13.9024 3.2Z"
                                    fill="#C99C69" />
                            </svg>


                        </div>


                        <p class="font-semibold text-18px text-center">تكليفات وواجبات</p>

                    </div>
                    <div class="bg-[#FFFFFF29] rounded-12px p-7">
                        <div class="mb-3 center gap-2">
                            <span class="font-semibold text-40px">{{ $stats["certificates"] ?? 0 }}</span>
                            <svg width="30" height="27" viewBox="0 0 30 27" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.9997 21.334H2.66634C2.31272 21.334 1.97358 21.1935 1.72353 20.9435C1.47348 20.6934 1.33301 20.3543 1.33301 20.0007V2.66732C1.33301 2.3137 1.47348 1.97456 1.72353 1.72451C1.97358 1.47446 2.31272 1.33398 2.66634 1.33398H26.6663C27.02 1.33398 27.3591 1.47446 27.6091 1.72451C27.8592 1.97456 27.9997 2.3137 27.9997 2.66732V20.0007C27.9997 20.3543 27.8592 20.6934 27.6091 20.9435C27.3591 21.1935 27.02 21.334 26.6663 21.334H21.333M6.66634 6.66732H22.6663M6.66634 11.334H10.6663M6.66634 16.0007H9.33301"
                                    stroke="#C99C69" stroke-width="2.66667" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M18.666 19.334C19.7269 19.334 20.7443 18.9126 21.4944 18.1624C22.2446 17.4123 22.666 16.3949 22.666 15.334C22.666 14.2731 22.2446 13.2557 21.4944 12.5056C20.7443 11.7554 19.7269 11.334 18.666 11.334C17.6051 11.334 16.5877 11.7554 15.8376 12.5056C15.0874 13.2557 14.666 14.2731 14.666 15.334C14.666 16.3949 15.0874 17.4123 15.8376 18.1624C16.5877 18.9126 17.6051 19.334 18.666 19.334Z"
                                    stroke="#C99C69" stroke-width="2.66667" />
                                <path
                                    d="M18.6657 23.9998L21.3324 25.3331V18.3145C21.3324 18.3145 20.5724 19.3331 18.6657 19.3331C16.759 19.3331 15.999 18.3331 15.999 18.3331V25.3331L18.6657 23.9998Z"
                                    stroke="#C99C69" stroke-width="2.66667" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>


                        </div>


                        <p class="font-semibold text-18px text-center">الشهادات المكتسبة</p>

                    </div>

                </div>
            </div>

            <div class="absolute top-0 end-0 w-[200px] h-[150px] ">
                <img src="{{ $heroLogo }}" alt="" class="size-full object-contain" loading="lazy" decoding="async">
            </div>
        </div>


    </div>

</header>

<section>
    <div class="container">
        <nav class="student-dash-tabs tabs tabs-bordered tabs-lg w-[75%] overflow-x-auto mb-12"
            aria-label="أقسام لوحة المتدرب" role="tablist" aria-orientation="horizontal">
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5 "
                id="tabs-large-item-1" data-tab="#tabs-large-1" aria-controls="tabs-large-1" role="tab"
                aria-selected="false">
                دوراتي
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray  active-tab:text-primary pb-5 active"
                id="tabs-large-item-2" data-tab="#tabs-large-2" aria-controls="tabs-large-2" role="tab"
                aria-selected="true">
                المحاضرات المباشرة
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="tabs-large-item-3" data-tab="#tabs-large-3" aria-controls="tabs-large-3" role="tab"
                aria-selected="false">
                تكليفات المحاضرات
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="tabs-large-item-4" data-tab="#tabs-large-4" aria-controls="tabs-large-4" role="tab"
                aria-selected="false">
                الاختبارات
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="tabs-large-item-5" data-tab="#tabs-large-5" aria-controls="tabs-large-5" role="tab"
                aria-selected="false">
                الشهادات
            </button>
            <button type="button"
                class="tab flex-1 justify-center font-semibold text-24px text-gray active-tab:text-primary pb-5"
                id="tabs-large-item-6" data-tab="#tabs-large-6" aria-controls="tabs-large-6" role="tab"
                aria-selected="false">
                التعليقات
            </button>
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
                        <div class="center flex-col text-center py-16">
                            <div class="mb-8">
                                <img src="{{ $noDataImg }}" alt="لم تشترك في أي دورة حتى الآن"
                                    class="max-w-xs w-full mx-auto" width="" height="" loading="lazy" decoding="async">
                            </div>
                            <h2 class="font-bold text-32px text-black mb-4">لم تشترك في أي دورة حتى الآن</h2>
                            <p class="font-medium text-18px text-gray mb-10 max-w-2xl leading-relaxed">
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
                                'calendarYear' => 2026,
                                'calendarMonth' => 7,
                                'calendarSelected' => 22,
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
                    <div class="bg-[#FFF9F9] px-10 py-11 border border-[#EFEFEF] rounded-12px mb-11">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-32px text-primary mb-5">🔴 محاضرة مباشرة</h4>
                                <h3 class="font-semibold text-30px text-black mb-5">{{ $liveSession->title }}</h3>
                                <p class="font-semibold text-24px text-black">الدورة: {{ $liveSession->webinar->title ?? '' }}</p>
                            </div>
                            <a href="{{ route('panel.v1.student.course.watch', ['slug' => $liveSession->webinar->slug ?? 'demo']) }}"
                                class="btn btn-primary font-semibold text-16px mt-4">
                                الانضمام إلى المحاضرة
                            </a>
                        </div>
                        <div class="bg-[#FFD9D9] text-black *:font-semibold text-20px text-black flex justify-between items-center px-4 py-6 rounded-10px mt-8">
                            <p>الموعد: {{ date('Y/m/d H:i', (int) $liveSession->date) }}</p>
                            <span>المدة المتوقعة: {{ $liveSession->duration ?? 0 }} دقيقة</span>
                        </div>
                    </div>
                @empty
                    <div class="border border-d9 px-10 py-11 rounded-12px center flex-col text-center">
                        <p class="font-semibold text-24px text-gray">لا توجد محاضرات مباشرة قادمة</p>
                    </div>
                @endforelse


            </div>
            <div id="tabs-large-3" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-3">
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
                    <div class="mt-16 center flex-col text-center">
                        <div class="mb-8">
                            <img src="{{ $noDataImg }}" alt="لا يوجد لديك واجب بعد"
                                class="max-w-xs w-full mx-auto" width="" height="" loading="lazy" decoding="async">
                        </div>
                        <p class="font-semibold text-32px text-black">لا يوجد لديك واجب بعد</p>
                    </div>
                @endif
            </div>

            <div id="tabs-large-4" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-4">
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
                    <div class="mt-16 center flex-col text-center">
                        <div class="mb-8">
                            <img src="{{ $noDataImg }}" alt="لا توجد لديك اختبار بعد"
                                class="max-w-xs w-full mx-auto" width="" height="" loading="lazy" decoding="async">
                        </div>
                        <p class="font-semibold text-32px text-black">لا توجد لديك اختبار بعد</p>
                    </div>
                @endforelse
            </div>
            <div id="tabs-large-5" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-5">
                    <div class="mt-15 gap-14 grid grid-cols-[repeat(auto-fill,minmax(25%,1fr))]">
                        @forelse ($certificates ?? [] as $certificate)
                        <div class="rounded-13px border border-d9 p-6 flex flex-col gap-5">
                            <div class="bg-primary h-56 rounded-8px center">
                                <span class="icon-[tabler--certificate] size-12 text-white"></span>
                            </div>
                            <div class="flex justify-between">
                                <div>
                                    <h5 class="font-semibold text-28px text-primary mb-4">
                                        {{ $certificate->webinar->title ?? '' }}
                                    </h5>
                                    <p class="fontmedium text-base text-gray">اكتمل في {{ date('Y/m/d', (int) $certificate->created_at) }}</p>
                                </div>
                                <div class="btn btn-text h-fit justify-center">
                                    <a href="{{ url('/panel/certificates/my-achievements/' . $certificate->id . '/show') }}"
                                        class="text-center w-full font-semibold text-14px text-gray">تحميل</a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="center flex-col text-center">
                            <p class="font-semibold text-24px text-gray">لا توجد شهادات بعد</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div id="tabs-large-6" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-6">
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
        </div>


        @include('panel_v1.student.components.assignment-submit-modal')
</section>


@endsection
