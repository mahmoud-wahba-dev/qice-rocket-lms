@extends('landing_v1.layouts.app')

@section('content')
@php($landingImg = asset('assets/landing_v1/img/instructors'))
<main>

    <header class="min-h-[80vh] bg-primary text-white flex items-center"
        style="background-image: url('{{ asset('assets/landing_v1/img/home/triangles-exp.webp') }}'); background-size: cover; background-repeat: no-repeat; background-position: bottom;">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-12 lg:gap-16 gap-4 py-10">
                <div class="lg:col-span-8">
                    <div class=" w-full ">
                        <div class="breadcrumbs mb-1.5">
                            <ul>
                                <li>
                                    <a href="/" class="font-medium text-20px text-white">
                                        الرئيسية
                                    </a>
                                </li>
                                <li class="breadcrumbs-separator">
                                    <span class="icon-[tabler--chevron-right] text-white rtl:rotate-180"></span>
                                </li>
                                <li>
                                    <a href="{{ route('landing.v1.courses') }}"
                                        class="font-medium text-20px text-white">
                                        الدورات
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <h1 class="font-bold text-48px lg:text-64px mb-5 text-white leading-tight">{{ $course->title }}</h1>
                        <p class="font-medium text-base mb-7 text-white/80 leading-relaxed">{{ strip_tags(html_entity_decode($course->description)) }}</p>

                        @if(!empty($course->teacher))
                        <div class="flex gap-4 mb-13 items-center">
                            <div class="size-14 bg-white rounded-full overflow-hidden shrink-0 border-2 border-white/20">
                                <img class="w-full h-full object-cover" src="{{ $course->teacher->getAvatar() }}" alt="{{ $course->teacher->full_name }}">
                            </div>
                            <div>
                                <p class="font-bold text-20px text-white">{{ $course->teacher->full_name }}</p>
                                <p class="font-medium text-14px text-white/70">{{ $course->teacher->headline }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center gap-6 lg:gap-10 flex-wrap">
                            @if($course->files->where('status', 'active')->count() > 0)
                            <div class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full border border-white/10">
                                <svg width="24" height="24" viewBox="0 0 28 28" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20.9996 9.80234H16.7996V1.40234H11.1996V9.80234H6.99963L13.9996 16.8023L20.9996 9.80234ZM27.0728 18.9471C26.7788 18.6335 24.8174 16.5349 24.2574 15.9875C23.8653 15.611 23.3423 15.4012 22.7986 15.4023H20.3388L24.6284 19.5939H19.6668C19.5993 19.5921 19.5325 19.6084 19.4734 19.6412C19.4143 19.6739 19.3651 19.7219 19.3308 19.7801L18.1884 22.4023H9.81083L8.66843 19.7801C8.63311 19.7229 8.58365 19.6757 8.5248 19.6431C8.46596 19.6105 8.39971 19.5935 8.33243 19.5939H3.37083L7.65903 15.4023H5.20063C4.64483 15.4023 4.11423 15.6249 3.74183 15.9875C3.18183 16.5363 1.22043 18.6349 0.926431 18.9471C0.241832 19.6765 -0.134768 20.2575 0.0444315 20.9757L0.829831 25.2793C1.00903 25.9989 1.79723 26.5897 2.58263 26.5897H25.4194C26.2048 26.5897 26.993 25.9989 27.1722 25.2793L27.9576 20.9757C28.134 20.2575 27.7588 19.6765 27.0728 18.9471Z"
                                        fill="white" />
                                </svg>
                                <span class="font-medium text-16px text-white">محتوى قابل للتنزيل</span>
                            </div>
                            @endif

                            @if(!empty($course->duration))
                            <div class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full border border-white/10">
                                <span class="icon-[tabler--clock] size-6 text-white shrink-0"></span>
                                <span class="font-medium text-16px text-white">{{ convertMinutesToHourAndMinute($course->duration) }} {{ trans('home.hours') }}</span>
                            </div>
                            @endif

                            <div class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full border border-white/10">
                                <span class="icon-[tabler--credit-card] size-6 text-white shrink-0"></span>
                                <span class="font-medium text-16px text-white">{{ ($course->price > 0) ? handlePrice($course->price) : 'مجانية' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4">
                    <div class="border border-[#0000002B] rounded-10px bg-white text-primary shadow-xl overflow-hidden mt-6 lg:mt-0">
                        <div class="relative h-[220px] overflow-hidden">
                            <img src="{{ !empty($course->image_cover) ? asset($course->image_cover) : asset('assets/landing_v1/img/contact/hero.webp') }}" alt="{{ $course->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                        </div>
                        <div class="p-7">
                            <p class="font-bold text-36px text-primary mb-7">
                                {{ ($course->price > 0) ? handlePrice($course->price) : 'مجانية' }}
                            </p>

                            {{-- اشترك الان: adds to cart then redirects to checkout --}}
                            <button type="button"
                                id="subscribe-now-btn"
                                data-course-id="{{ $course->id }}"
                                data-checkout-url="{{ route('landing.v1.checkout') }}"
                                class="btn btn-primary btn-block h-15 rounded-5px font-semibold text-20px mb-3 shadow-md hover:shadow-lg transition-all duration-300">
                                <span class="icon-[tabler--lock] size-5 me-1"></span>
                                اشترك الان
                            </button>

                            <form action="/cart/store" method="post" class="w-full add-to-cart-form">
                                @csrf
                                <input type="hidden" name="item_name" value="webinar_id">
                                <input type="hidden" name="item_id" value="{{ $course->id }}">
                                <button type="submit" class="btn btn-outline-primary btn-block h-15 rounded-5px font-semibold text-20px hover:bg-primary/5 transition-all duration-300">
                                    <span class="btn-label">اضف للسلة</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="section-gap course_details_page relative py-20">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-8">
                    <nav data-scrollspy="#course-content-scrollspy" class="sticky top-[88px] bg-white z-40 flex gap-6 border-b border-[#B4B4B4] mb-12 overflow-x-auto whitespace-nowrap scrollbar-none">
                        <a href="#about-course"
                            class="font-medium text-20px lg:text-24px text-[#B4B4B4] pb-5 border-b-2 border-transparent hover:border-primary hover:text-primary transition scrollspy-active:border-primary scrollspy-active:text-primary active">
                            عن الدورة
                        </a>
                        <a href="#course-content"
                            class="font-medium text-20px lg:text-24px text-[#B4B4B4] pb-5 border-b-2 border-transparent hover:border-primary hover:text-primary transition scrollspy-active:border-primary scrollspy-active:text-primary">
                            المحتوى
                        </a>
                        <a href="#course-instructor"
                            class="font-medium text-20px lg:text-24px text-[#B4B4B4] pb-5 border-b-2 border-transparent hover:border-primary hover:text-primary transition scrollspy-active:border-primary scrollspy-active:text-primary">
                            عن المدرب
                        </a>
                        <a href="#course-faq"
                            class="font-medium text-20px lg:text-24px text-[#B4B4B4] pb-5 border-b-2 border-transparent hover:border-primary hover:text-primary transition scrollspy-active:border-primary scrollspy-active:text-primary">
                            الاسئلة الشائعة
                        </a>
                        <a href="#course-rating"
                            class="font-medium text-20px lg:text-24px text-[#B4B4B4] pb-5 border-b-2 border-transparent hover:border-primary hover:text-primary transition scrollspy-active:border-primary scrollspy-active:text-primary">
                            التقييم
                        </a>
                    </nav>

                    <div id="course-content-scrollspy">

                        <div id="about-course" class="mb-16 scroll-mt-10">
                            <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">عن الدورة</h3>
                            <div class="border border-[#CFCFCF] px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm">
                                <div class="font-normal text-18px lg:text-22px text-primary mb-9 leading-relaxed">
                                    {!! $course->description !!}
                                </div>

                                @if($learningMaterials->count() > 0)
                                <h4 class="font-bold text-24px lg:text-28px text-primary mb-4 border-t border-gray-100 pt-6">ماذا ستتعلم؟</h4>
                                <ul class="text-[#7C7F88] space-y-3 list-disc list-inside">
                                    @foreach($learningMaterials as $material)
                                    <li class="font-normal text-18px leading-relaxed">{{ $material->value }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>

                        <div id="course-content" class="mb-16 scroll-mt-10">
                            <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">منهج الدورة</h3>
                            <div class="border border-[#CFCFCF] px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm">
                                <div class="accordion accordion-shadow">
                                    @forelse($course->chapters as $key => $chapter)
                                    <div class="accordion-item {{ $key === 0 ? 'active' : '' }}" id="curriculum-{{ $chapter->id }}">
                                        <button
                                            class="accordion-toggle inline-flex items-center gap-x-4 px-5 font-semibold text-20px lg:text-24px text-primary py-6 border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                                            aria-controls="curriculum-{{ $chapter->id }}-collapse" aria-expanded="{{ $key === 0 ? 'true' : 'false' }}">
                                            <span
                                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                                            <span
                                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                                            {{ $chapter->title }}
                                        </button>
                                        <div id="curriculum-{{ $chapter->id }}-collapse"
                                            class="accordion-content {{ $key === 0 ? '' : 'hidden' }} w-full overflow-hidden transition-[height] duration-300"
                                            aria-labelledby="curriculum-{{ $chapter->id }}" role="region">
                                            <div class="px-5 pb-6 pt-2 border-t border-gray-100">
                                                <ul class="text-base-content/80 font-normal space-y-3.5">
                                                    @foreach($chapter->sessions as $session)
                                                    <li class="flex items-center gap-3 text-16px text-primary/90 font-medium">
                                                        <span class="icon-[tabler--video] text-primary size-5 shrink-0"></span>
                                                        <span class="flex-grow">{{ $session->title }}</span>
                                                        <span class="text-12px bg-primary/10 text-primary px-2.5 py-0.5 rounded-full">{{ trans('public.session') }} - {{ $session->duration }} {{ trans('public.minutes') }}</span>
                                                    </li>
                                                    @endforeach

                                                    @foreach($chapter->files as $file)
                                                    <li class="flex items-center gap-3 text-16px text-primary/90 font-medium">
                                                        <span class="icon-[tabler--file-text] text-primary size-5 shrink-0"></span>
                                                        <span class="flex-grow">{{ $file->title }}</span>
                                                        <span class="text-12px bg-amber-500/10 text-amber-600 px-2.5 py-0.5 rounded-full">{{ trans('public.file') }}</span>
                                                    </li>
                                                    @endforeach

                                                    @foreach($chapter->textLessons as $textLesson)
                                                    <li class="flex items-center gap-3 text-16px text-primary/90 font-medium">
                                                        <span class="icon-[tabler--book-open] text-primary size-5 shrink-0"></span>
                                                        <span class="flex-grow">{{ $textLesson->title }}</span>
                                                        <span class="text-12px bg-emerald-500/10 text-emerald-600 px-2.5 py-0.5 rounded-full">{{ trans('public.text_lesson') }}</span>
                                                    </li>
                                                    @endforeach

                                                    @foreach($chapter->assignments as $assignment)
                                                    <li class="flex items-center gap-3 text-16px text-primary/90 font-medium">
                                                        <span class="icon-[tabler--clipboard-list] text-primary size-5 shrink-0"></span>
                                                        <span class="flex-grow">{{ $assignment->title }}</span>
                                                        <span class="text-12px bg-indigo-500/10 text-indigo-600 px-2.5 py-0.5 rounded-full">{{ trans('public.assignment') }}</span>
                                                    </li>
                                                    @endforeach

                                                    @foreach($chapter->quizzes as $quiz)
                                                    <li class="flex items-center gap-3 text-16px text-primary/90 font-medium">
                                                        <span class="icon-[tabler--help-circle] text-primary size-5 shrink-0"></span>
                                                        <span class="flex-grow">{{ $quiz->title }}</span>
                                                        <span class="text-12px bg-rose-500/10 text-rose-600 px-2.5 py-0.5 rounded-full">{{ trans('public.quiz') }}</span>
                                                    </li>
                                                    @endforeach

                                                    @if($chapter->sessions->isEmpty() && $chapter->files->isEmpty() && $chapter->textLessons->isEmpty() && $chapter->assignments->isEmpty() && $chapter->quizzes->isEmpty())
                                                    <li class="text-gray-400 text-center py-4 font-medium">لا توجد دروس أو ملفات متوفرة في هذا القسم حالياً.</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-10 text-gray-500 font-medium">
                                        منهج الدورة قيد التطوير وسيتم رفعه قريباً.
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @if(!empty($course->teacher))
                        <div id="course-instructor" class="mb-16 scroll-mt-10">
                            <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">عن المدرب</h3>
                            <div class="border border-[#CFCFCF] flex flex-col md:flex-row items-center gap-10 px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm">
                                <div class="bg-e3 w-[160px] h-[160px] rounded-full overflow-hidden shrink-0 border-4 border-gray-100 flex items-center justify-center">
                                    <img src="{{ $course->teacher->getAvatar() }}" alt="{{ $course->teacher->full_name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-grow">
                                    <h6 class="font-bold text-24px text-primary mb-2">{{ $course->teacher->full_name }}</h6>
                                    <p class="font-normal text-base text-primary/80 leading-relaxed mb-6">
                                        {{ $course->teacher->bio }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-6 mb-7 border-t border-b border-gray-100 py-4">
                                        <!-- Member since -->
                                        <div class="flex items-center gap-2 text-[#A6AAB5]">
                                            <span class="icon-[tabler--calendar] size-5 shrink-0 text-primary"></span>
                                            <div class="font-medium text-12px flex flex-col">
                                                <span>عضو منذ</span>
                                                <span class="font-bold text-primary">{{ dateTimeFormat($course->teacher->created_at, 'Y') }}</span>
                                            </div>
                                        </div>

                                        <!-- Total Courses -->
                                        <div class="flex items-center gap-2 text-[#A6AAB5]">
                                            <span class="icon-[tabler--video] size-5 shrink-0 text-primary"></span>
                                            <div class="font-medium text-12px flex flex-col">
                                                <span>عدد الدورات</span>
                                                <span class="font-bold text-primary">{{ $teacher_courses_count }}</span>
                                            </div>
                                        </div>

                                        <!-- Total Students -->
                                        <div class="flex items-center gap-2 text-[#A6AAB5]">
                                            <span class="icon-[tabler--users] size-5 shrink-0 text-primary"></span>
                                            <div class="font-medium text-12px flex flex-col">
                                                <span>عدد الطلاب</span>
                                                <span class="font-bold text-primary">{{ $teacher_students_count }}</span>
                                            </div>
                                        </div>

                                        <!-- Review Rates -->
                                        <div class="flex items-center gap-2 text-[#A6AAB5]">
                                            <span class="icon-[tabler--star-filled] size-5 shrink-0 text-[#FFAA00]"></span>
                                            <div class="font-medium text-12px flex flex-col">
                                                <span>التقييم</span>
                                                <span class="font-bold text-primary">5.0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <a href="{{ $course->teacher->getProfileUrl() }}" class="btn btn-primary h-12 rounded-4px font-medium text-14px px-8 inline-flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                                            <span class="icon-[tabler--user] size-5"></span>
                                            عرض الملف الشخصي
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div id="course-faq" class="mb-16 scroll-mt-10">
                            <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">الاسئلة الشائعة</h3>
                            <div class="border border-[#CFCFCF] px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm">
                                <div class="accordion accordion-shadow">
                                    <div class="accordion-item active" id="faq-1">
                                        <button
                                            class="accordion-toggle inline-flex items-center gap-x-4 px-5 font-semibold text-20px lg:text-24px text-primary py-6 border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                                            aria-controls="faq-1-collapse" aria-expanded="true">
                                            <span
                                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                                            <span
                                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                                            1. هل يمكنني الوصول إلى محتوى الدورة بعد الانتهاء منها؟
                                        </button>
                                        <div id="faq-1-collapse"
                                            class="accordion-content w-full overflow-hidden transition-[height] duration-300"
                                            aria-labelledby="faq-1" role="region">
                                            <div class="px-5 pb-5 border-t border-gray-100 pt-3">
                                                <p class="text-base-content/85 font-medium text-16px leading-relaxed">
                                                    نعم، يمكنك الوصول إلى محتوى الدورة في أي وقت بعد الاشتراك وتظل متاحة في حسابك مدى الحياة.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item" id="faq-2">
                                        <button
                                            class="accordion-toggle inline-flex items-center gap-x-4 px-5 font-semibold text-20px lg:text-24px text-primary py-6 border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                                            aria-controls="faq-2-collapse" aria-expanded="false">
                                            <span
                                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                                            <span
                                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                                            2. هل توجد برامج مخصصة للشركات والجهات الحكومية؟
                                        </button>
                                        <div id="faq-2-collapse"
                                            class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300"
                                            aria-labelledby="faq-2" role="region">
                                            <div class="px-5 pb-5 border-t border-gray-100 pt-3">
                                                <p class="text-base-content/85 font-medium text-16px leading-relaxed">
                                                    نعم، نقدم برامج مخصصة تتناسب مع احتياجات الشركات والجهات الحكومية. يمكنك التواصل معنا لمزيد من التفاصيل.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item" id="faq-3">
                                        <button
                                            class="accordion-toggle inline-flex items-center gap-x-4 px-5 font-semibold text-20px lg:text-24px text-primary py-6 border-[#E7E7E7] text-start w-full hover:bg-gray-50 transition-colors"
                                            aria-controls="faq-3-collapse" aria-expanded="false">
                                            <span
                                                class="icon-[tabler--plus] accordion-item-active:hidden text-base-content size-5 block shrink-0"></span>
                                            <span
                                                class="icon-[tabler--minus] accordion-item-active:block text-base-content size-5 hidden shrink-0"></span>
                                            3. هل يمكنني التواصل مع المدرب مباشرة؟
                                        </button>
                                        <div id="faq-3-collapse"
                                            class="accordion-content hidden w-full overflow-hidden transition-[height] duration-300"
                                            aria-labelledby="faq-3" role="region">
                                            <div class="px-5 pb-5 border-t border-gray-100 pt-3">
                                                <p class="text-base-content/85 font-medium text-16px leading-relaxed">
                                                    نعم، يمكنك التواصل مع المدرب من خلال منصة الدورة وطرح الأسئلة في قسم النقاشات المخصص لذلك.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="course-rating" class="mb-16 scroll-mt-10">
                            <h3 class="font-medium text-32px lg:text-36px text-primary mb-6">التقييم</h3>
                            <div class="border border-[#CFCFCF] px-6 lg:px-8 py-8 lg:py-11 rounded-8px bg-white shadow-sm flex flex-col md:flex-row justify-center items-center gap-8 lg:gap-12">

                                <!-- Big box (Right in RTL) -->
                                <div class="w-[140px] h-[140px] bg-primary/5 rounded-8px shrink-0 flex flex-col items-center justify-center text-primary border border-primary/10">
                                    <span class="font-bold text-48px leading-none mb-1 text-primary">{{ $course->getRate() }}</span>
                                    <span class="font-semibold text-13px text-primary/60">{{ $course->reviews->where('status', 'active')->count() }} تقييم</span>
                                </div>

                                <!-- Rows of Stars and Bars -->
                                <div class="flex flex-col gap-3.5 w-full max-w-[320px]">
                                    @foreach($ratesDistribution as $stars => $dist)
                                    <div class="flex items-center gap-4">
                                        <!-- Stars (Middle) -->
                                        <div class="flex items-center gap-1 w-[80px] justify-end">
                                            @for($j = 1; $j <= 5; $j++)
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="{{ $j <= $stars ? '#FFAA00' : '#E5E7EB' }}"/>
                                            </svg>
                                            @endfor
                                        </div>
                                        <!-- Bar (Left in RTL) -->
                                        <div class="h-2 flex-grow bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#FFAA00] rounded-full transition-all duration-500" style="width: {{ $dist['percent'] }}%"></div>
                                        </div>
                                        <span class="font-bold text-12px text-primary/70 w-8">{{ round($dist['percent']) }}%</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('subscribe-now-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        const courseId    = btn.dataset.courseId;
        const checkoutUrl = btn.dataset.checkoutUrl;

        // Loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="icon-[tabler--loader-2] size-5 me-1 animate-spin inline-block"></span> جاري التحميل...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const formData  = new FormData();
            formData.append('_token',    csrfToken);
            formData.append('item_name', 'webinar_id');
            formData.append('item_id',   courseId);

            const response = await fetch('/cart/store', {
                method:  'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body:    formData,
            });

            const data = await response.json();

            if (response.ok && (data.status === 'success' || data.code === 200)) {
                // Successfully added — go straight to checkout
                window.location.href = checkoutUrl;
            } else {
                const errMsg      = data?.toast_alert?.msg || data?.msg || '';
                const alreadyInCart = errMsg.toLowerCase().includes('already')
                    || errMsg.includes('موجود')
                    || errMsg.includes('مضاف');

                if (alreadyInCart) {
                    // Already in cart — still go to checkout
                    window.location.href = checkoutUrl;
                } else {
                    // Real error — show toast and reset button
                    window.showCartToast && showCartToast(
                        data?.toast_alert?.title || 'خطأ',
                        errMsg || 'حدث خطأ، يرجى المحاولة مجدداً',
                        'error'
                    );
                    btn.disabled = false;
                    btn.innerHTML = '<span class="icon-[tabler--lock] size-5 me-1"></span> اشترك الان';
                }
            }
        } catch (_) {
            window.showCartToast && showCartToast('خطأ', 'فشل الاتصال بالخادم', 'error');
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--lock] size-5 me-1"></span> اشترك الان';
        }
    });
});
</script>
@endpush
