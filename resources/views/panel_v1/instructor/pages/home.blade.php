@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
    $slug = $demoSlug ?? 'demo';
    $reviewId = $demoAssignmentId ?? 1;
@endphp

<div class="space-y-6 pb-8">
    {{-- Stats row — loop keeps value/label; icons are static SVGs below --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        @foreach ($stats ?? [] as $index => $stat)
            <div class="rounded-14px bg-primary text-white px-3 py-5 sm:py-6 text-center flex flex-col items-center justify-center min-h-[120px]">
                <span class="mb-3 inline-flex items-center justify-center" aria-hidden="true">
                    @switch($index)
                        @case(0)
                            {{-- إجمالي الأرباح --}}
                            <svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.86763 11.3597L-0.000976562 8.52006L14.6048 0L29.2106 8.52006V18.8658H26.7763V9.94047L24.342 11.3597V19.4878L24.0706 19.8225C22.9309 21.2354 21.4889 22.3749 19.8508 23.1572C18.2127 23.9395 16.4201 24.3447 14.6048 24.343C12.7895 24.3447 10.9969 23.9395 9.35883 23.1572C7.72073 22.3749 6.2788 21.2354 5.13905 19.8225L4.86763 19.4878V11.3597ZM7.30193 12.7801V18.6127C8.21473 19.6499 9.33833 20.4804 10.5977 21.0488C11.857 21.6172 13.2232 21.9104 14.6048 21.9087C15.9865 21.9104 17.3526 21.6172 18.612 21.0488C19.8713 20.4804 20.9949 19.6499 21.9077 18.6127V12.7801L14.6048 17.0401L7.30193 12.7801ZM4.83111 8.52006L14.6048 14.2224L24.3785 8.52006L14.6048 2.8177L4.83111 8.52006Z" fill="#C99C69"/>
                            </svg>
                            @break

                        @case(1)
                            {{-- إجمالي الطلاب — replace SVG when ready --}}
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="#C99C69"/>
                            </svg>
                            @break

                        @case(2)
                            {{-- الدورات النشطة — replace SVG when ready --}}
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2Zm0 18H6V4h2v8l2.5-1.5L13 12V4h5v16Z" fill="#C99C69"/>
                            </svg>
                            @break

                        @case(3)
                            {{-- تكاليف وواجبات — replace SVG when ready --}}
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2Zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1Zm2 14H7v-2h7v2Zm3-4H7v-2h10v2Zm0-4H7V7h10v2Z" fill="#C99C69"/>
                            </svg>
                            @break

                        @case(4)
                            {{-- الاختبارات النشطة — replace SVG when ready --}}
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6Zm2 16H8v-2h8v2Zm0-4H8v-2h8v2Zm-3-5V3.5L18.5 9H13Z" fill="#C99C69"/>
                            </svg>
                            @break
                    @endswitch
                </span>
                <p class="font-semibold text-30px leading-none mb-2">{{ $stat['value'] }}</p>
                <p class="font-semibold text-14px text-white leading-snug">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Middle: courses (start/right in RTL) + sidebar actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
        {{-- دوراتي --}}
        <section class="lg:col-span-8 space-y-4 order-2 lg:order-1">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-bold text-20px sm:text-22px text-primary">دوراتي</h2>
                <a href="{{ route('panel.v1.instructor.courses') }}"
                    class="font-semibold text-14px text-primary hover:opacity-80 transition">عرض الكل</a>
            </div>

            <div class="space-y-3">
                @foreach ($courses ?? [] as $index => $course)
                    <article class="rounded-14px border border-d9 bg-white p-4 flex gap-3 sm:gap-4 items-start sm:items-center">
                        <div class="size-14 sm:size-16 rounded-12px bg-primary shrink-0"></div>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-16px sm:text-18px text-primary mb-0.5 leading-snug">{{ $course['title'] }}</h3>
                            <p class="font-medium text-12px sm:text-13px text-gray mb-3">{{ $course['subtitle'] }}</p>
                            <p class="font-medium text-12px text-primary/80 mb-1.5">{{ $course['progress'] }}% متوسط معدل التقدم</p>
                            <div class="h-1.5 rounded-full bg-[#EFEFEF] overflow-hidden max-w-[220px]">
                                <div class="h-full bg-primary rounded-full" style="width: {{ $course['progress'] }}%"></div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end justify-between gap-3 shrink-0 self-stretch min-h-[72px]">
                            <div class="dropdown relative inline-flex [--auto-close:true] rtl:[--placement:bottom-end]">
                                <button type="button"
                                    class="dropdown-toggle btn btn-square btn-text btn-sm !inline-flex !items-center !justify-center"
                                    aria-label="خيارات الدورة" id="home-course-menu-{{ $index }}">
                                    <span class="icon-[tabler--dots] size-5 text-primary"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48 py-2 rounded-12px border border-d9 bg-white shadow-xl z-20"
                                    role="menu" aria-labelledby="home-course-menu-{{ $index }}">
                                    <li>
                                        <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
                                            class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">عرض التفاصيل</a>
                                    </li>
                                    <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">تعديل</a></li>
                                    <li><a href="#" class="dropdown-item px-4 py-2.5 font-medium text-14px text-[#E11D48]">حذف</a></li>
                                </ul>
                            </div>

                            <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
                                class="inline-flex items-center justify-center rounded-10px bg-primary px-3 sm:px-4 h-9 font-bold text-12px sm:text-13px text-white hover:opacity-95 transition whitespace-nowrap">
                                عرض التفاصيل
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Quick action + lectures --}}
        <aside class="lg:col-span-4 space-y-4 order-1 lg:order-2">
            <div class="rounded-14px border border-d9 bg-white p-4 sm:p-5">
                <h2 class="font-bold text-18px sm:text-20px text-primary mb-4">إجراء سريع</h2>
                <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                    @foreach ($quickActions ?? [] as $action)
                        @php
                            $href = !empty($action['route']) ? route($action['route']) : '#';
                        @endphp
                        <a href="{{ $href }}"
                            class="rounded-12px border border-d9 bg-white px-2 py-3.5 text-center font-semibold text-12px sm:text-13px text-primary hover:bg-fa transition leading-snug">
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="rounded-14px border border-d9 bg-white p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--calendar-event] size-5 text-[#E11D48]"></span>
                    <h2 class="font-bold text-16px sm:text-18px text-primary">المحاضرات المباشرة القادمة</h2>
                </div>
                <div class="space-y-2.5">
                    @foreach ($upcomingLectures ?? [] as $lecture)
                        <div class="rounded-12px bg-[#F5F5F5] px-4 py-3">
                            <p class="font-bold text-13px sm:text-14px text-primary mb-1 leading-snug">{{ $lecture['title'] }}</p>
                            <p class="font-medium text-12px text-gray">{{ $lecture['when'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>

    {{-- Pending grading --}}
    <section>
        <div class="flex items-center gap-2 mb-4">
            <span class="icon-[tabler--pin] size-5 text-[#3B82F6]"></span>
            <h2 class="font-bold text-20px sm:text-22px text-primary">التكاليف بانتظار التصحيح</h2>
        </div>
        <div class="space-y-3">
            @foreach ($pendingGrading ?? [] as $item)
                <article class="rounded-14px border border-d9 bg-white px-4 sm:px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-15px sm:text-16px text-primary mb-1">{{ $item['title'] }}</h3>
                        <p class="font-medium text-13px text-gray">الطالبة: {{ $item['student'] }} • {{ $item['time'] }}</p>
                    </div>
                    <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                        class="inline-flex items-center gap-1.5 font-bold text-14px text-primary hover:opacity-80 transition shrink-0">
                        تصحيح
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                    </a>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
