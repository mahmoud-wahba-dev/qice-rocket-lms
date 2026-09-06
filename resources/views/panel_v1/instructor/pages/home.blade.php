@extends('panel_v1.instructor.layouts.app')

@section('content')
@php
$slug = $demoSlug ?? 'demo';
$reviewId = $demoAssignmentId ?? 1;
@endphp

<div class="space-y-6 pb-8">
    {{-- Stats row — loop keeps value/label; icons are static SVGs --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        @foreach ($stats ?? [] as $index => $stat)
        <div
            class="rounded-14px bg-primary text-white px-3 py-5 sm:py-6 text-center flex flex-col items-center justify-center min-h-[120px]">
            <span class="mb-3 inline-flex items-center justify-center" aria-hidden="true">
                @switch($index)
                @case(0)
                {{-- إجمالي الأرباح --}}
                <svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M4.86763 11.3597L-0.000976562 8.52006L14.6048 0L29.2106 8.52006V18.8658H26.7763V9.94047L24.342 11.3597V19.4878L24.0706 19.8225C22.9309 21.2354 21.4889 22.3749 19.8508 23.1572C18.2127 23.9395 16.4201 24.3447 14.6048 24.343C12.7895 24.3447 10.9969 23.9395 9.35883 23.1572C7.72073 22.3749 6.2788 21.2354 5.13905 19.8225L4.86763 19.4878V11.3597ZM7.30193 12.7801V18.6127C8.21473 19.6499 9.33833 20.4804 10.5977 21.0488C11.857 21.6172 13.2232 21.9104 14.6048 21.9087C15.9865 21.9104 17.3526 21.6172 18.612 21.0488C19.8713 20.4804 20.9949 19.6499 21.9077 18.6127V12.7801L14.6048 17.0401L7.30193 12.7801ZM4.83111 8.52006L14.6048 14.2224L24.3785 8.52006L14.6048 2.8177L4.83111 8.52006Z"
                        fill="#C99C69" />
                </svg>
                @break

                @case(1)
                {{-- إجمالي الطلاب — replace SVG when ready --}}
                <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M26.5465 6.61062L15.2965 2.86062C15.104 2.79646 14.896 2.79646 14.7035 2.86062L3.45352 6.61062C3.26684 6.67285 3.10448 6.79223 2.98943 6.95187C2.87439 7.11151 2.81249 7.3033 2.8125 7.50007V16.8751C2.8125 17.1237 2.91127 17.3622 3.08709 17.538C3.2629 17.7138 3.50136 17.8126 3.75 17.8126C3.99864 17.8126 4.2371 17.7138 4.41291 17.538C4.58873 17.3622 4.6875 17.1237 4.6875 16.8751V8.80085L8.62383 10.1122C7.578 11.8018 7.24543 13.8374 7.69914 15.772C8.15286 17.7066 9.35577 19.3821 11.0437 20.4305C8.93437 21.2579 7.11094 22.7544 5.77734 24.8005C5.70799 24.9035 5.65981 25.0194 5.63562 25.1412C5.61142 25.2631 5.6117 25.3886 5.63642 25.5103C5.66113 25.6321 5.70981 25.7477 5.77961 25.8505C5.84941 25.9533 5.93895 26.0412 6.04301 26.109C6.14707 26.1769 6.26359 26.2234 6.38579 26.2459C6.50799 26.2683 6.63343 26.2663 6.75482 26.2398C6.87622 26.2133 6.99114 26.163 7.09291 26.0918C7.19468 26.0205 7.28128 25.9297 7.34766 25.8247C9.11367 23.1153 11.9027 21.5626 15 21.5626C18.0973 21.5626 20.8863 23.1153 22.6523 25.8247C22.7898 26.029 23.0023 26.1711 23.2436 26.2201C23.485 26.2691 23.736 26.2211 23.9423 26.0866C24.1486 25.952 24.2937 25.7417 24.3462 25.501C24.3986 25.2603 24.3543 25.0087 24.2227 24.8005C22.8891 22.7544 21.0586 21.2579 18.9562 20.4305C20.6426 19.3821 21.8444 17.7077 22.298 15.7745C22.7517 13.8413 22.4201 11.8072 21.3762 10.118L26.5465 8.39538C26.7332 8.33319 26.8956 8.21381 27.0107 8.05418C27.1258 7.89454 27.1877 7.70273 27.1877 7.50593C27.1877 7.30913 27.1258 7.11732 27.0107 6.95768C26.8956 6.79804 26.7332 6.67867 26.5465 6.61648V6.61062ZM20.625 14.0626C20.6253 14.9519 20.4147 15.8285 20.0105 16.6207C19.6063 17.4128 19.0201 18.0978 18.2999 18.6195C17.5797 19.1412 16.7461 19.4847 15.8675 19.6219C14.9888 19.759 14.0902 19.6859 13.2453 19.4085C12.4004 19.1311 11.6333 18.6573 11.0069 18.026C10.3806 17.3947 9.91285 16.6239 9.64208 15.7769C9.37131 14.9298 9.30524 14.0306 9.44928 13.1531C9.59332 12.2755 9.94336 11.4446 10.4707 10.7286L14.7035 12.1348C14.896 12.199 15.104 12.199 15.2965 12.1348L19.5293 10.7286C20.2415 11.6942 20.6255 12.8627 20.625 14.0626ZM15 10.2622L6.71484 7.50007L15 4.73796L23.2852 7.50007L15 10.2622Z"
                        fill="#C99C69" />
                </svg>

                @break

                @case(2)
                {{-- الدورات النشطة — replace SVG when ready --}}
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2Zm0 18H6V4h2v8l2.5-1.5L13 12V4h5v16Z"
                        fill="#C99C69" />
                </svg>
                @break

                @case(3)
                {{-- تكاليف وواجبات — replace SVG when ready --}}
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2Zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1Zm2 14H7v-2h7v2Zm3-4H7v-2h10v2Zm0-4H7V7h10v2Z"
                        fill="#C99C69" />
                </svg>
                @break

                @case(4)
                {{-- الاختبارات النشطة — replace SVG when ready --}}
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6Zm2 16H8v-2h8v2Zm0-4H8v-2h8v2Zm-3-5V3.5L18.5 9H13Z"
                        fill="#C99C69" />
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
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 mt-8">
        {{-- دوراتي --}}
        <section class="lg:col-span-8 space-y-4 mb-0 order-2 lg:order-1 mt-0 bg-white p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold text-20px  text-black">دوراتي</h2>
                <a href="{{ route('panel.v1.instructor.courses') }}"
                    class="font-medium text-14px text-primary underline hover:opacity-80 transition">عرض الكل</a>
            </div>

            <div class="space-y-3">
                @foreach ($courses ?? [] as $index => $course)
                <article
                    class="rounded-14px border border-d9 bg-f9 p-4 flex gap-3 sm:gap-4 items-start sm:items-center">
                    <div class="size-14 sm:size-16 rounded-12px bg-primary shrink-0"></div>

                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-21px text-black mb-0.5 leading-snug">{{
                            $course['title'] }}</h3>
                        <p class="font-medium text-14px text-gray mb-3">{{ $course['subtitle'] }}</p>
                        <p class="font-semibold text-12px text-black mb-1.5">{{ $course['progress'] }}% متوسط معدل
                            التقدم</p>
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
                                        class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">عرض
                                        التفاصيل</a>
                                </li>
                                <li><a href="#"
                                        class="dropdown-item px-4 py-2.5 font-medium text-14px text-primary">تعديل</a>
                                </li>
                                <li><a href="#"
                                        class="dropdown-item px-4 py-2.5 font-medium text-14px text-[#E11D48]">حذف</a>
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('panel.v1.instructor.courses.performance', ['slug' => $slug]) }}"
                            class="inline-flex items-center justify-center rounded-10px bg-primary px-3 sm:px-4 h-9 font-semibold text-10px  text-white hover:opacity-95 transition whitespace-nowrap">
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
                        class="rounded-12px border border-d9 bg-[#F8FAFC] px-2 py-3.5 text-center font-semibold text-12px sm:text-13px text-primary hover:bg-fa transition leading-snug">
                        {{ $action['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="rounded-14px border border-d9 bg-white p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--calendar-event] size-5 text-[#E11D48]"></span>
                    <h2 class="font-bold text-20px  text-[#0F172A]">المحاضرات المباشرة القادمة</h2>
                </div>
                <div class="space-y-2.5">
                    @foreach ($upcomingLectures ?? [] as $lecture)
                    <div class="rounded-12px bg-f9 border border-[#C99C691C] px-4 py-3">
                        <p class="font-bold text-20px  text-primary mb-2 leading-snug">{{ $lecture['title']
                            }}</p>
                        <p class="font-semibold text-16px text-primary">{{ $lecture['when'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>

    {{-- Pending grading --}}
    <section class="bg-white border border-d9 p-7 rounded-10px">
        <div class="flex items-center gap-2 mb-4">
            <span class="icon-[tabler--pin] size-5 text-[#3B82F6]"></span>
            <h2 class="font-bold text-20px  text-primary">التكاليف بانتظار التصحيح</h2>
        </div>
        <div class="space-y-3">
            @foreach ($pendingGrading ?? [] as $item)
            <article
                class="rounded-14px border border-d9 bg-f9 px-4 sm:px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="font-bold text-20px  text-[#0F172A] mb-1">{{ $item['title'] }}</h3>
                    <p class="font-normal text-18px text-[#64748B]">الطالبة: {{ $item['student'] }} • {{ $item['time'] }}</p>
                </div>
                <a href="{{ route('panel.v1.instructor.assignments.review', ['id' => $reviewId]) }}"
                    class="inline-flex items-center gap-1.5 font-bold text-18px text-[#0D9488] hover:opacity-80 transition shrink-0">
                    تصحيح
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                </a>
            </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
