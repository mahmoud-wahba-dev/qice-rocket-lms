@extends('landing_v1.layouts.app')

@section('content')
    @php($landingImg = asset('assets/landing_v1/img'))
    <main class="mt-0">

        <header class="relative  bg-white text-primary overflow-hidden pt-8 h-screen max-xl:mt-20"
            style="background-image: url('{{ $landingImg }}/home/hero-bg.webp'); background-size: cover; background-position: center;">

            <div class="container relative z-10  flex justify-end items-center h-full">

                <div class="mw-fit">
                    <h1 class="font-bold text-40px xl:text-57px  text-primary mb-5 ">تدريـب نوعي متخصص

                        <br>
                        مـوجــه نحــو التطبيـــق
                    </h1>
                    <p class="font-normal text-20px xl:text-26px text-[#7A8886] mb-5">تلبية الاحتياجات التدريبية للمرحلة
                        المقبلة
                        ورؤية
                        2030</p>

                    <div class="flex items-center mb-6 ">
                        <img class="max-w-[450px] object-cover w-full" src="{{ $landingImg }}/home/hero-img3.webp"
                            alt="">
                    </div>
                    <div class="flex items-center flex-wrap gap-4 h-14 font-medium text-20px">
                        <a href="{{ route('landing.v1.register') }}"
                            class="btn btn-primary gap-4 h-12 xl:h-16 font-medium text-16px xl:text-20px ">




                            استعرض الدورات المجانية

                            <svg width="11" height="11" viewBox="0 0 6 11" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.15665 9.57306L0.738281 5.1547L5.15665 0.736328" stroke="white"
                                    stroke-width="1.47279" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </a>

                        <a href="{{ route('landing.v1.about') }}"
                            class="btn btn-text h-12 xl:h-16 font-medium text-16px xl:text-20px">

                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.466 11.0469V13.9925C15.466 14.3831 15.3109 14.7577 15.0347 15.0339C14.7585 15.3101 14.3839 15.4652 13.9933 15.4652H3.68373C3.29312 15.4652 2.91851 15.3101 2.64231 15.0339C2.36611 14.7577 2.21094 14.3831 2.21094 13.9925V11.0469"
                                    stroke="#0F4C45" stroke-width="1.47279" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M5.15625 7.36328L8.83822 11.0453L12.5202 7.36328" stroke="#0F4C45"
                                    stroke-width="1.47279" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M8.83594 11.0457V2.20898" stroke="#0F4C45" stroke-width="1.47279"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            تحميل الخطة التدريبية


                        </a>
                    </div>
                </div>

            </div>
        </header>

        <section>
            <div class="container">
                <h2 class="font-bold text-38px text-primary mb-16 text-center sec-title">من نحن</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                    <div class="bg-secondary border border-card-border rounded-18px p-8 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-4">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20 18.666C20.2667 17.3327 20.9333 16.3993 22 15.3327C23.3333 14.1327 24 12.3993 24 10.666C24 8.54428 23.1571 6.50945 21.6569 5.00916C20.1566 3.50887 18.1217 2.66602 16 2.66602C13.8783 2.66602 11.8434 3.50887 10.3431 5.00916C8.84286 6.50945 8 8.54428 8 10.666C8 11.9993 8.26667 13.5993 10 15.3327C10.9333 16.266 11.7333 17.3327 12 18.666"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12 24H20" stroke="#0F4C45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M13.332 29.334H18.6654" stroke="#0F4C45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>

                        </div>
                        <h4 class="font-semibold text-22px text-primary mb-4">رؤيتنا</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            مركز رائد في تعزيز مفاهيم وتطبيقات الجودة
                            والتميز المؤسسي
                        </p>
                    </div>

                    <div class="bg-secondary border border-card-border rounded-18px p-8 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-4">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M16.0013 29.3327C23.3651 29.3327 29.3346 23.3631 29.3346 15.9993C29.3346 8.63555 23.3651 2.66602 16.0013 2.66602C8.63751 2.66602 2.66797 8.63555 2.66797 15.9993C2.66797 23.3631 8.63751 29.3327 16.0013 29.3327Z"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M16 24C20.4183 24 24 20.4183 24 16C24 11.5817 20.4183 8 16 8C11.5817 8 8 11.5817 8 16C8 20.4183 11.5817 24 16 24Z"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M15.9987 18.6673C17.4715 18.6673 18.6654 17.4734 18.6654 16.0007C18.6654 14.5279 17.4715 13.334 15.9987 13.334C14.5259 13.334 13.332 14.5279 13.332 16.0007C13.332 17.4734 14.5259 18.6673 15.9987 18.6673Z"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </div>
                        <h4 class="font-semibold text-22px text-primary mb-4">رسالتنا</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            تدريب وتأهيل الكوادر ودعم منشآت القطاعين
                            العام والخاص خلال رحلتها نحو الجودة والتميز
                            المؤسسي
                        </p>
                    </div>

                    <div class="bg-secondary border border-card-border rounded-18px p-8 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-4">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M14.668 22.6673L17.3346 25.334C17.5973 25.5966 17.9091 25.805 18.2522 25.9471C18.5954 26.0893 18.9632 26.1624 19.3346 26.1624C19.7061 26.1624 20.0739 26.0893 20.417 25.9471C20.7602 25.805 21.072 25.5966 21.3346 25.334C21.5973 25.0713 21.8056 24.7595 21.9478 24.4164C22.0899 24.0732 22.1631 23.7054 22.1631 23.334C22.1631 22.9626 22.0899 22.5948 21.9478 22.2516C21.8056 21.9084 21.5973 21.5966 21.3346 21.334"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M18.667 18.6673L22.0003 22.0007C22.5307 22.5311 23.2502 22.8291 24.0003 22.8291C24.7504 22.8291 25.4699 22.5311 26.0003 22.0007C26.5307 21.4702 26.8287 20.7508 26.8287 20.0007C26.8287 19.2505 26.5307 18.5311 26.0003 18.0007L20.827 12.8273C20.077 12.0783 19.0603 11.6575 18.0003 11.6575C16.9403 11.6575 15.9236 12.0783 15.1736 12.8273L14.0003 14.0007C13.4699 14.5311 12.7504 14.8291 12.0003 14.8291C11.2502 14.8291 10.5307 14.5311 10.0003 14.0007C9.46987 13.4702 9.17188 12.7508 9.17188 12.0007C9.17188 11.2505 9.46987 10.5311 10.0003 10.0007L13.747 6.254C14.9633 5.04086 16.5495 4.26807 18.2545 4.05797C19.9595 3.84786 21.6859 4.21245 23.1603 5.094L23.787 5.46734C24.3547 5.80999 25.0297 5.92883 25.6803 5.80067L28.0003 5.334"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M28.0013 4L29.3346 18.6667H26.668" stroke="#0F4C45" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M4.0013 4L2.66797 18.6667L11.3346 27.3333C11.8651 27.8638 12.5845 28.1618 13.3346 28.1618C14.0848 28.1618 14.8042 27.8638 15.3346 27.3333C15.8651 26.8029 16.1631 26.0835 16.1631 25.3333C16.1631 24.5832 15.8651 23.8638 15.3346 23.3333"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M4 5.33398H14.6667" stroke="#0F4C45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>


                        </div>
                        <h4 class="font-semibold text-22px text-primary mb-4">قيمنا</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            التميز، الالتزام، العمل بروح الفريق، الشفافية،
                            والعميل أولًا
                        </p>
                    </div>
                </div>
            </div>
        </section>




        <section class="section-gap">
            <div class="container">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6 items-center">





                </div>
            </div>
        </section>



        <section class="!my-0 lg:py-32 py-8 section-gap bg-secondary py-28 pb-36"
            style="background-image: url('{{ $landingImg }}/home/triangles-exp.webp'); background-size: 100%; background-repeat: no-repeat; background-position: bottom;">
            <div class="container">
                <div class="grid grid-cols-1 lg:grid-cols-2 items-center">
                    <div>
                        <h2 class="font-bold text-38px text-primary mb-4">عناصر النجاح الرئيسية</h2>
                        <p class="font-normal text-20px text-7a">يعتمد المركز على مجموعة عناصر مهنية متكاملة تدعم
                            <br>
                            جودة التدريب، الاعتماد، التطوير، والاستدامة
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2  gap-4 xl:gap-x-10 xl:gap-y-6 text-primary ">
                        <div class="border border-card-border px-6 py-13 rounded-20px bg-white ">
                            <div class="size-13 center bg-gold rounded-full mb-4 mx-auto">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20.8346 13.5408C20.8346 18.7492 17.1888 21.3533 12.8555 22.8638C12.6286 22.9406 12.3821 22.937 12.1576 22.8533C7.8138 21.3533 4.16797 18.7492 4.16797 13.5408V6.24917C4.16797 5.9729 4.27772 5.70795 4.47307 5.5126C4.66842 5.31725 4.93337 5.2075 5.20964 5.2075C7.29297 5.2075 9.89713 3.9575 11.7096 2.37417C11.9303 2.18562 12.211 2.08203 12.5013 2.08203C12.7916 2.08203 13.0723 2.18562 13.293 2.37417C15.1159 3.96792 17.7096 5.2075 19.793 5.2075C20.0692 5.2075 20.3342 5.31725 20.5295 5.5126C20.7249 5.70795 20.8346 5.9729 20.8346 6.24917V13.5408Z"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>

                            </div>

                            <p class="font-medium text-20px text-primary text-primary text-center">
                                الجودة والتميز المؤسسي

                            </p>
                        </div>

                        <div class="border border-card-border px-6 py-13 rounded-20px bg-white ">
                            <div class="size-13 center bg-gold rounded-full mb-4 mx-auto">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M16.6654 21.875V19.7917C16.6654 18.6866 16.2264 17.6268 15.445 16.8454C14.6636 16.064 13.6038 15.625 12.4987 15.625H6.2487C5.14363 15.625 4.08382 16.064 3.30242 16.8454C2.52102 17.6268 2.08203 18.6866 2.08203 19.7917V21.875"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M9.3737 11.4583C11.6749 11.4583 13.5404 9.59285 13.5404 7.29167C13.5404 4.99048 11.6749 3.125 9.3737 3.125C7.07251 3.125 5.20703 4.99048 5.20703 7.29167C5.20703 9.59285 7.07251 11.4583 9.3737 11.4583Z"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M16.668 11.4583L18.7513 13.5417L22.918 9.375" stroke="#0F4C45"
                                        stroke-width="1.5625" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>


                            </div>

                            <p class="font-medium text-20px text-primary text-primary text-center">
                                التدريب النوعي والتأهيل
                            </p>
                        </div>

                        <div class="border border-card-border px-6 py-13 rounded-20px bg-white ">
                            <div class="size-13 center bg-gold rounded-full mb-4 mx-auto">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M11.457 17.7077L13.5404 19.791C13.7456 19.9962 13.9892 20.159 14.2572 20.27C14.5253 20.3811 14.8127 20.4382 15.1029 20.4382C15.393 20.4382 15.6804 20.3811 15.9485 20.27C16.2166 20.159 16.4602 19.9962 16.6654 19.791C16.8706 19.5858 17.0333 19.3422 17.1444 19.0741C17.2554 18.806 17.3126 18.5187 17.3126 18.2285C17.3126 17.9383 17.2554 17.651 17.1444 17.3829C17.0333 17.1148 16.8706 16.8712 16.6654 16.666"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M14.5821 14.5839L17.1863 17.188C17.6007 17.6024 18.1627 17.8352 18.7488 17.8352C19.3348 17.8352 19.8969 17.6024 20.3113 17.188C20.7257 16.7736 20.9585 16.2116 20.9585 15.6255C20.9585 15.0395 20.7257 14.4774 20.3113 14.063L16.2696 10.0214C15.6837 9.43615 14.8894 9.10744 14.0613 9.10744C13.2331 9.10744 12.4389 9.43615 11.8529 10.0214L10.9363 10.938C10.5219 11.3524 9.95982 11.5852 9.37377 11.5852C8.78772 11.5852 8.22567 11.3524 7.81127 10.938C7.39687 10.5236 7.16406 9.96158 7.16406 9.37552C7.16406 8.78947 7.39687 8.22743 7.81127 7.81302L10.7384 4.88594C11.6886 3.93817 12.9278 3.33443 14.2599 3.17029C15.5919 3.00614 16.9406 3.29097 18.0925 3.97969L18.5821 4.27136C19.0256 4.53905 19.553 4.6319 20.0613 4.53177L21.8738 4.16719"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M21.8737 3.125L22.9154 14.5833H20.832" stroke="#0F4C45" stroke-width="1.5625"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M3.1237 3.125L2.08203 14.5833L8.85286 21.3542C9.26727 21.7686 9.82931 22.0014 10.4154 22.0014C11.0014 22.0014 11.5635 21.7686 11.9779 21.3542C12.3923 20.9398 12.6251 20.3777 12.6251 19.7917C12.6251 19.2056 12.3923 18.6436 11.9779 18.2292"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M3.125 4.16602H11.4583" stroke="#0F4C45" stroke-width="1.5625"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>


                            </div>

                            <p class="font-medium text-20px text-primary text-primary text-center">
                                الدعم والاستشارات


                            </p>
                        </div>

                        <div class="border border-card-border px-6 py-13 rounded-20px bg-white ">
                            <div class="size-13 center bg-gold rounded-full mb-4 mx-auto">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M16.1209 13.4277L17.699 22.309C17.7167 22.4136 17.702 22.521 17.6569 22.6171C17.6119 22.7131 17.5386 22.793 17.4469 22.8463C17.3551 22.8995 17.2493 22.9235 17.1436 22.9149C17.0379 22.9064 16.9373 22.8658 16.8553 22.7986L13.1261 19.9996C12.9461 19.8651 12.7274 19.7924 12.5027 19.7924C12.2779 19.7924 12.0592 19.8651 11.8792 19.9996L8.1438 22.7975C8.06184 22.8646 7.96137 22.9052 7.85577 22.9137C7.75018 22.9223 7.6445 22.8984 7.55282 22.8453C7.46115 22.7922 7.38784 22.7124 7.34267 22.6166C7.29751 22.5208 7.28264 22.4135 7.30005 22.309L8.87713 13.4277"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M12.5 14.584C15.9518 14.584 18.75 11.7858 18.75 8.33398C18.75 4.8822 15.9518 2.08398 12.5 2.08398C9.04822 2.08398 6.25 4.8822 6.25 8.33398C6.25 11.7858 9.04822 14.584 12.5 14.584Z"
                                        stroke="#0F4C45" stroke-width="1.5625" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>


                            </div>

                            <p class="font-medium text-20px text-primary text-primary text-center">
                                الدراسات

                            </p>
                        </div>

                    </div>
                </div>
            </div>

            <div>
                <!-- <img src="{{ $landingImg }}/home/triangles-exp.webp" alt="Section 2 Image"> -->
            </div>
        </section>

        <section class="my-0 lg:py-32 py-8 bg-primary text-white">
            <div class="container">
                <h2 class="font-bold text-38px  mb-4 text-center">ورش ومحاضرات مجانية لتطوير مهاراتك المهنية
                </h2>
                <p class="font-normal text-base mb-20 text-secondary text-center">
                    مجموعة من الورش التعريفية والمحاضرات المجانية المصممة لرفع الوعي المهني ومساعدة المتدربين على اختيار
                    المسار
                    التدريبي الأنسب
                </p>
                <div id="multi-slide mb-10"
                    data-carousel='{ "loadingClasses": "opacity-0", "slidesQty": { "xs": 1, "sm": 2, "lg": 4 , "xl": 4 }, "isRTL": true ,"dotsItemClasses": "carousel-dot" ,"isInfiniteLoop": true }'
                    class="relative w-full">
                    <div class="carousel ">
                        <div class="carousel-body  h-full opacity-0 overflow-visible gap-4 xl:gap-8">
                            <div class="carousel-slide ">
                                <div class="relative bg-white border border-card-border  py-8 px-6 rounded-10px  ">
                                    <div
                                        class="absolute top-8 left-4 bg-[#E8F5E9] w-20
                                 h-6 rounded-18px center font-medium text-12px text-primary">
                                        مجاني
                                    </div>
                                    <div class="bg-[#F5F1E8] center rounded-full size-14 mb-6">
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.6654 24.5V22.1667C18.6654 20.929 18.1737 19.742 17.2985 18.8668C16.4234 17.9917 15.2364 17.5 13.9987 17.5H6.9987C5.76102 17.5 4.57404 17.9917 3.69887 18.8668C2.8237 19.742 2.33203 20.929 2.33203 22.1667V24.5"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.4987 12.8333C13.076 12.8333 15.1654 10.744 15.1654 8.16667C15.1654 5.58934 13.076 3.5 10.4987 3.5C7.92137 3.5 5.83203 5.58934 5.83203 8.16667C5.83203 10.744 7.92137 12.8333 10.4987 12.8333Z"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M25.668 24.5007V22.1673C25.6672 21.1334 25.3231 20.1289 24.6896 19.3117C24.0561 18.4945 23.1691 17.9108 22.168 17.6523"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M18.668 3.65234C19.6718 3.90936 20.5615 4.49316 21.1969 5.31171C21.8322 6.13025 22.1771 7.13698 22.1771 8.17318C22.1771 9.20938 21.8322 10.2161 21.1969 11.0346C20.5615 11.8532 19.6718 12.437 18.668 12.694"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <h6 class="font-semibold text-19px text-primary mb-3">
                                        مقدمة في مفاهيم العمل السعودي
                                    </h6>
                                    <p class="font-normal text-15px text-7a mb-2">
                                        اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب
                                        هنا
                                    </p>
                                    <p class="font-normal text-15px text-primary mb-6">التطوير المهني</p>
                                    <div>
                                        <a class="btn btn-outline btn-primary whitespace-nowrap h-12 btn-block font-medium text-16px"
                                            href="#">
                                            تفاصيل الورشة
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide ">
                                <div class="relative bg-white border border-card-border  py-8 px-6 rounded-10px  ">
                                    <div
                                        class="absolute top-8 left-4 bg-[#E8F5E9] w-20
                                 h-6 rounded-18px center font-medium text-12px text-primary">
                                        مجاني
                                    </div>
                                    <div class="bg-[#F5F1E8] center rounded-full size-14 mb-6">
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.6654 24.5V22.1667C18.6654 20.929 18.1737 19.742 17.2985 18.8668C16.4234 17.9917 15.2364 17.5 13.9987 17.5H6.9987C5.76102 17.5 4.57404 17.9917 3.69887 18.8668C2.8237 19.742 2.33203 20.929 2.33203 22.1667V24.5"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.4987 12.8333C13.076 12.8333 15.1654 10.744 15.1654 8.16667C15.1654 5.58934 13.076 3.5 10.4987 3.5C7.92137 3.5 5.83203 5.58934 5.83203 8.16667C5.83203 10.744 7.92137 12.8333 10.4987 12.8333Z"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M25.668 24.5007V22.1673C25.6672 21.1334 25.3231 20.1289 24.6896 19.3117C24.0561 18.4945 23.1691 17.9108 22.168 17.6523"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M18.668 3.65234C19.6718 3.90936 20.5615 4.49316 21.1969 5.31171C21.8322 6.13025 22.1771 7.13698 22.1771 8.17318C22.1771 9.20938 21.8322 10.2161 21.1969 11.0346C20.5615 11.8532 19.6718 12.437 18.668 12.694"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <h6 class="font-semibold text-19px text-primary mb-3">
                                        مقدمة في مفاهيم العمل السعودي
                                    </h6>
                                    <p class="font-normal text-15px text-7a mb-2">
                                        اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب
                                        هنا
                                    </p>
                                    <p class="font-normal text-15px text-primary mb-6">التطوير المهني</p>
                                    <div>
                                        <a class="btn btn-outline btn-primary whitespace-nowrap h-12 btn-block font-medium text-16px"
                                            href="#">
                                            تفاصيل الورشة
                                        </a>
                                    </div>
                                </div>
                            </div>


                            <div class="carousel-slide ">
                                <div class="relative bg-white border border-card-border  py-8 px-6 rounded-10px  ">
                                    <div
                                        class="absolute top-8 left-4 bg-[#E8F5E9] w-20
                                 h-6 rounded-18px center font-medium text-12px text-primary">
                                        مجاني
                                    </div>
                                    <div class="bg-[#F5F1E8] center rounded-full size-14 mb-6">
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.6654 24.5V22.1667C18.6654 20.929 18.1737 19.742 17.2985 18.8668C16.4234 17.9917 15.2364 17.5 13.9987 17.5H6.9987C5.76102 17.5 4.57404 17.9917 3.69887 18.8668C2.8237 19.742 2.33203 20.929 2.33203 22.1667V24.5"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.4987 12.8333C13.076 12.8333 15.1654 10.744 15.1654 8.16667C15.1654 5.58934 13.076 3.5 10.4987 3.5C7.92137 3.5 5.83203 5.58934 5.83203 8.16667C5.83203 10.744 7.92137 12.8333 10.4987 12.8333Z"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M25.668 24.5007V22.1673C25.6672 21.1334 25.3231 20.1289 24.6896 19.3117C24.0561 18.4945 23.1691 17.9108 22.168 17.6523"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M18.668 3.65234C19.6718 3.90936 20.5615 4.49316 21.1969 5.31171C21.8322 6.13025 22.1771 7.13698 22.1771 8.17318C22.1771 9.20938 21.8322 10.2161 21.1969 11.0346C20.5615 11.8532 19.6718 12.437 18.668 12.694"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <h6 class="font-semibold text-19px text-primary mb-3">
                                        مقدمة في مفاهيم العمل السعودي
                                    </h6>
                                    <p class="font-normal text-15px text-7a mb-2">
                                        اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب
                                        هنا
                                    </p>
                                    <p class="font-normal text-15px text-primary mb-6">التطوير المهني</p>
                                    <div>
                                        <a class="btn btn-outline btn-primary whitespace-nowrap h-12 btn-block font-medium text-16px"
                                            href="#">
                                            تفاصيل الورشة
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide ">
                                <div class="relative bg-white border border-card-border  py-8 px-6 rounded-10px  ">
                                    <div
                                        class="absolute top-8 left-4 bg-[#E8F5E9] w-20
                                 h-6 rounded-18px center font-medium text-12px text-primary">
                                        مجاني
                                    </div>
                                    <div class="bg-[#F5F1E8] center rounded-full size-14 mb-6">
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.6654 24.5V22.1667C18.6654 20.929 18.1737 19.742 17.2985 18.8668C16.4234 17.9917 15.2364 17.5 13.9987 17.5H6.9987C5.76102 17.5 4.57404 17.9917 3.69887 18.8668C2.8237 19.742 2.33203 20.929 2.33203 22.1667V24.5"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.4987 12.8333C13.076 12.8333 15.1654 10.744 15.1654 8.16667C15.1654 5.58934 13.076 3.5 10.4987 3.5C7.92137 3.5 5.83203 5.58934 5.83203 8.16667C5.83203 10.744 7.92137 12.8333 10.4987 12.8333Z"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M25.668 24.5007V22.1673C25.6672 21.1334 25.3231 20.1289 24.6896 19.3117C24.0561 18.4945 23.1691 17.9108 22.168 17.6523"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M18.668 3.65234C19.6718 3.90936 20.5615 4.49316 21.1969 5.31171C21.8322 6.13025 22.1771 7.13698 22.1771 8.17318C22.1771 9.20938 21.8322 10.2161 21.1969 11.0346C20.5615 11.8532 19.6718 12.437 18.668 12.694"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <h6 class="font-semibold text-19px text-primary mb-3">
                                        مقدمة في مفاهيم العمل السعودي
                                    </h6>
                                    <p class="font-normal text-15px text-7a mb-2">
                                        اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب
                                        هنا
                                    </p>
                                    <p class="font-normal text-15px text-primary mb-6">التطوير المهني</p>
                                    <div>
                                        <a class="btn btn-outline btn-primary whitespace-nowrap h-12 btn-block font-medium text-16px"
                                            href="#">
                                            تفاصيل الورشة
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide ">
                                <div class="relative bg-white border border-card-border  py-8 px-6 rounded-10px  ">
                                    <div
                                        class="absolute top-8 left-4 bg-[#E8F5E9] w-20
                                 h-6 rounded-18px center font-medium text-12px text-primary">
                                        مجاني
                                    </div>
                                    <div class="bg-[#F5F1E8] center rounded-full size-14 mb-6">
                                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.6654 24.5V22.1667C18.6654 20.929 18.1737 19.742 17.2985 18.8668C16.4234 17.9917 15.2364 17.5 13.9987 17.5H6.9987C5.76102 17.5 4.57404 17.9917 3.69887 18.8668C2.8237 19.742 2.33203 20.929 2.33203 22.1667V24.5"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M10.4987 12.8333C13.076 12.8333 15.1654 10.744 15.1654 8.16667C15.1654 5.58934 13.076 3.5 10.4987 3.5C7.92137 3.5 5.83203 5.58934 5.83203 8.16667C5.83203 10.744 7.92137 12.8333 10.4987 12.8333Z"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M25.668 24.5007V22.1673C25.6672 21.1334 25.3231 20.1289 24.6896 19.3117C24.0561 18.4945 23.1691 17.9108 22.168 17.6523"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M18.668 3.65234C19.6718 3.90936 20.5615 4.49316 21.1969 5.31171C21.8322 6.13025 22.1771 7.13698 22.1771 8.17318C22.1771 9.20938 21.8322 10.2161 21.1969 11.0346C20.5615 11.8532 19.6718 12.437 18.668 12.694"
                                                stroke="#0F4C45" stroke-width="1.75" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>

                                    </div>
                                    <h6 class="font-semibold text-19px text-primary mb-3">
                                        مقدمة في مفاهيم العمل السعودي
                                    </h6>
                                    <p class="font-normal text-15px text-7a mb-2">
                                        اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب هنا وصف الكورس اكتب
                                        هنا
                                    </p>
                                    <p class="font-normal text-15px text-primary mb-6">التطوير المهني</p>
                                    <div>
                                        <a class="btn btn-outline btn-primary whitespace-nowrap h-12 btn-block font-medium text-16px"
                                            href="#">
                                            تفاصيل الورشة
                                        </a>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    <button type="button" class="carousel-prev">
                        <span
                            class="size-12 bg-[#F4F4F6] border border-primary flex items-center justify-center rounded-full shadow">
                            <span class="icon-[tabler--chevron-left] size-5 cursor-pointer rtl:rotate-180"></span>
                        </span>
                        <span class="sr-only">Previous</span>
                    </button>
                    <button type="button" class="carousel-next">
                        <span class="sr-only">Next</span>
                        <span
                            class="size-12 bg-[#F4F4F6] border border-primary flex items-center justify-center rounded-full shadow">
                            <span class="icon-[tabler--chevron-right] size-5 cursor-pointer rtl:rotate-180"></span>
                        </span>
                    </button>
                </div>


                <div class="text-center mt-10">
                    <a href=""
                        class="btn btn-outline bg-[#E8F5E9] rounded-10px h-15 font-extrabold px-8 text-primary text-20px">
                        عرض جميع الدورات المجانية
                    </a>
                </div>
            </div>
        </section>


        <section class="my-0 lg:py-32 py-8 ">
            <div class="container">
                <div class="flex justify-between items-end xl:flex-nowrap mb-20">
                    <div>
                        <h2 class="font-bold text-40px text-primary  mb-4 ">قائمة المدربين بالمركز
                        </h2>
                        <p class="font-medium text-20px  text-primary xl:w-[60%] lg:w-[80%] ">
                            نخبة من الخبراء والمستشارين والمدربين المعتمدين يقودون مسارات التدريب في المركز بخبرات متنوعة في
                            الحوكمة، الجودة، الإدارة الصحية، القانون، التدريب، التطوير المؤسسي والتميز. </p>
                    </div>
                    <a href=""
                        class="btn btn-outline bg-[#E8F5E9] rounded-10px h-15 font-extrabold px-10 text-primary text-20px">
                        عرض جميع المدربين </a>
                </div>
                <div id="trainers-carousel"
                    data-carousel='{ "loadingClasses": "opacity-0", "slidesQty": { "xs": 1, "sm": 2, "lg": 3, "xl": 4 }, "isRTL": true, "dotsItemClasses": "carousel-dot", "isInfiniteLoop": true, "isAutoPlay": true, "speed": 3500 }'
                    class="relative w-full">
                    <div class="carousel">
                        <div class="carousel-body h-full opacity-0 overflow-visible gap-4 xl:gap-8">
                            <div class="carousel-slide">
                                <div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
                                    <div class="h-60 overflow-hidden rounded-t-[19px]">
                                        <img class="h-full w-full object-cover"
                                            src="{{ $landingImg }}/home/instructor.webp" alt="د. محمد أبو هيشة">
                                    </div>
                                    <div class="p-6">
                                        <h6 class="font-semibold text-24px text-blue mb-3">د. محمد أبو هيشة</h6>
                                        <ul class="list-inside list-disc font-normal text-14px text-blue space-y-1">
                                            <li>دكتوراه في الإدارة والتخطيط الصحي.</li>
                                            <li>استشاري الإدارة الصحية والمستشفيات.</li>
                                            <li>عمل مديرًا عامًا لعدة إدارات عامة بوزارة الصحة لمدة 30 سنة.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
                                    <div class="h-60 overflow-hidden rounded-t-[19px]">
                                        <img class="h-full w-full object-cover"
                                            src="{{ $landingImg }}/home/instructor.webp" alt="د. عبدالله القحطاني">
                                    </div>
                                    <div class="p-6">
                                        <h6 class="font-semibold text-24px text-blue mb-3">د. عبدالله القحطاني</h6>
                                        <ul class="list-inside list-disc font-normal text-14px text-blue space-y-1">
                                            <li>دكتوراه في إدارة الأعمال.</li>
                                            <li>خبير في الحوكمة والتطوير المؤسسي.</li>
                                            <li>مدرب معتمد في برامج التميز المؤسسي.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
                                    <div class="h-60 overflow-hidden rounded-t-[19px]">
                                        <img class="h-full w-full object-cover"
                                            src="{{ $landingImg }}/home/instructor.webp" alt="د. سارة العتيبي">
                                    </div>
                                    <div class="p-6">
                                        <h6 class="font-semibold text-24px text-blue mb-3">د. سارة العتيبي</h6>
                                        <ul class="list-inside list-disc font-normal text-14px text-blue space-y-1">
                                            <li>دكتوراه في الجودة والاعتماد المؤسسي.</li>
                                            <li>استشارية في نظم الجودة الصحية.</li>
                                            <li>خبرة تتجاوز 20 عامًا في التدريب والتطوير.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
                                    <div class="h-60 overflow-hidden rounded-t-[19px]">
                                        <img class="h-full w-full object-cover"
                                            src="{{ $landingImg }}/home/instructor.webp" alt="د. خالد الشمري">
                                    </div>
                                    <div class="p-6">
                                        <h6 class="font-semibold text-24px text-blue mb-3">د. خالد الشمري</h6>
                                        <ul class="list-inside list-disc font-normal text-14px text-blue space-y-1">
                                            <li>ماجستير في القانون الإداري.</li>
                                            <li>مستشار في الامتثال والحوكمة.</li>
                                            <li>مدرب في برامج القيادة والإدارة الاستراتيجية.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="trainer-card bg-white border border-[#0000001A] rounded-19px overflow-hidden h-full">
                                    <div class="h-60 overflow-hidden rounded-t-[19px]">
                                        <img class="h-full w-full object-cover"
                                            src="{{ $landingImg }}/home/instructor.webp" alt="د. نورة الدوسري">
                                    </div>
                                    <div class="p-6">
                                        <h6 class="font-semibold text-24px text-blue mb-3">د. نورة الدوسري</h6>
                                        <ul class="list-inside list-disc font-normal text-14px text-blue space-y-1">
                                            <li>دكتوراه في التخطيط الاستراتيجي.</li>
                                            <li>خبيرة في تطوير الكفاءات القيادية.</li>
                                            <li>مدربة معتمدة في برامج التطوير المهني.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-pagination flex justify-center gap-3 mt-10"></div>
                </div>


            </div>
        </section>

        <section class="my-0 lg:py-32 py-8 bg-primary text-white">
            <div class="container">
                <div class="flex justify-between items-end flex-wrap mb-20">
                    <div>
                        <h2 class="font-bold text-40px text-secondary  mb-4 ">الدورات المعتمدة والبرامج المدفوعة
                        </h2>
                        <p class="font-normal text-20px  text-secondary ">
                            برامج تدريبية احترافية ومعتمدة في مختلف المجالات
                        </p>
                    </div>
                    <a href=""
                        class="btn btn-outline bg-[#E8F5E9] rounded-10px h-15 font-extrabold px-10 text-primary text-20px">
                        عرض جميع المدربين
                    </a>
                </div>
                <div id="multi-slide mb-10"
                    data-carousel='{ "loadingClasses": "opacity-0", "slidesQty": { "xs": 1, "sm": 2, "lg": 3 , "xl": 4 }, "isRTL": true ,"dotsItemClasses": "carousel-dot" ,"isInfiniteLoop": true }'
                    class="relative w-full">
                    <div class="carousel ">
                        <div class="carousel-body  h-full opacity-0 overflow-visible gap-4 xl:gap-8">
                            <div class="carousel-slide ">
                                <div class="bg-white  rounded-19px ">
                                    <div class="h-60 mb-6 overflow-hidden">
                                        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]"
                                            src="{{ $landingImg }}/home/course.webp" alt="course 1">
                                    </div>

                                    <div class="p-6">
                                        <h6 class="font-semibold text-20px text-primary mb-2">التسويق الرقمي</h6>
                                        <p class="font-normal text-12px text-7a mb-2">Digital Marketing</p>
                                        <p class="font-normal text-13px text-7a mb-3">
                                            دورة تطويرية
                                        </p>
                                        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
                                            <div class="avatar">

                                                <div class="size-10 rounded-full">
                                                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png"
                                                        alt="avatar" />
                                                </div>
                                            </div>

                                            <p class="font-medium text-77 text-base">اسم المعلم</p>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                                                699 ريال
                                            </span>
                                            <a href=""
                                                class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">سجّل
                                                الآن</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide ">
                                <div class="bg-white  rounded-19px ">
                                    <div class="h-60 mb-6 overflow-hidden">
                                        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]"
                                            src="{{ $landingImg }}/home/course.webp" alt="course 1">
                                    </div>

                                    <div class="p-6">
                                        <h6 class="font-semibold text-20px text-primary mb-2">التسويق الرقمي</h6>
                                        <p class="font-normal text-12px text-7a mb-2">Digital Marketing</p>
                                        <p class="font-normal text-13px text-7a mb-3">
                                            دورة تطويرية
                                        </p>
                                        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
                                            <div class="avatar">

                                                <div class="size-10 rounded-full">
                                                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png"
                                                        alt="avatar" />
                                                </div>
                                            </div>

                                            <p class="font-medium text-77 text-base">اسم المعلم</p>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                                                699 ريال
                                            </span>
                                            <a href=""
                                                class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">سجّل
                                                الآن</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide ">
                                <div class="bg-white  rounded-19px ">
                                    <div class="h-60 mb-6 overflow-hidden">
                                        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]"
                                            src="{{ $landingImg }}/home/course.webp" alt="course 1">
                                    </div>

                                    <div class="p-6">
                                        <h6 class="font-semibold text-20px text-primary mb-2">التسويق الرقمي</h6>
                                        <p class="font-normal text-12px text-7a mb-2">Digital Marketing</p>
                                        <p class="font-normal text-13px text-7a mb-3">
                                            دورة تطويرية
                                        </p>
                                        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
                                            <div class="avatar">

                                                <div class="size-10 rounded-full">
                                                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png"
                                                        alt="avatar" />
                                                </div>
                                            </div>

                                            <p class="font-medium text-77 text-base">اسم المعلم</p>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                                                699 ريال
                                            </span>
                                            <a href=""
                                                class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">سجّل
                                                الآن</a>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="carousel-slide ">
                                <div class="bg-white  rounded-19px ">
                                    <div class="h-60 mb-6 overflow-hidden">
                                        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]"
                                            src="{{ $landingImg }}/home/course.webp" alt="course 1">
                                    </div>

                                    <div class="p-6">
                                        <h6 class="font-semibold text-20px text-primary mb-2">التسويق الرقمي</h6>
                                        <p class="font-normal text-12px text-7a mb-2">Digital Marketing</p>
                                        <p class="font-normal text-13px text-7a mb-3">
                                            دورة تطويرية
                                        </p>
                                        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
                                            <div class="avatar">

                                                <div class="size-10 rounded-full">
                                                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png"
                                                        alt="avatar" />
                                                </div>
                                            </div>

                                            <p class="font-medium text-77 text-base">اسم المعلم</p>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                                                699 ريال
                                            </span>
                                            <a href=""
                                                class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">سجّل
                                                الآن</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-slide ">
                                <div class="bg-white  rounded-19px ">
                                    <div class="h-60 mb-6 overflow-hidden">
                                        <img class="h-full w-full object-cover rounded-tr-[19px] rounded-tl-[19px]"
                                            src="{{ $landingImg }}/home/course.webp" alt="course 1">
                                    </div>

                                    <div class="p-6">
                                        <h6 class="font-semibold text-20px text-primary mb-2">التسويق الرقمي</h6>
                                        <p class="font-normal text-12px text-7a mb-2">Digital Marketing</p>
                                        <p class="font-normal text-13px text-7a mb-3">
                                            دورة تطويرية
                                        </p>
                                        <div class="flex items-center gap-1.5 mb-5 border-t border-card-border pt-4 ">
                                            <div class="avatar">

                                                <div class="size-10 rounded-full">
                                                    <img src="https://cdn.flyonui.com/fy-assets/avatar/avatar-1.png"
                                                        alt="avatar" />
                                                </div>
                                            </div>

                                            <p class="font-medium text-77 text-base">اسم المعلم</p>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-20px text-primary whitespace-nowrap gap-4">
                                                699 ريال
                                            </span>
                                            <a href=""
                                                class="btn btn-primary rounded-[3px] h-10 font-medium text-14px ">سجّل
                                                الآن</a>
                                        </div>
                                    </div>
                                </div>
                            </div>







                        </div>
                    </div>

                    <button type="button" class="carousel-prev">
                        <span
                            class="size-12 bg-[#F4F4F6] border border-primary flex items-center justify-center rounded-full shadow">
                            <span class="icon-[tabler--chevron-left] size-5 cursor-pointer rtl:rotate-180"></span>
                        </span>
                        <span class="sr-only">Previous</span>
                    </button>
                    <button type="button" class="carousel-next">
                        <span class="sr-only">Next</span>
                        <span
                            class="size-12 bg-[#F4F4F6] border border-primary flex items-center justify-center rounded-full shadow">
                            <span class="icon-[tabler--chevron-right] size-5 cursor-pointer rtl:rotate-180"></span>
                        </span>
                    </button>
                </div>


            </div>
        </section>

        <section class="bg-secondary py-16 lg:py-32 my-0">
            <div class="container">
                <h2 class="font-bold text-38px text-primary mb-16 text-center sec-title">لماذا مركز الجودة والتميز؟</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-8">
                    <div class="bg-white  rounded-20px p-14 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-6">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20.6357 17.1875L22.6557 28.5555C22.6783 28.6894 22.6595 28.8269 22.6018 28.9498C22.5442 29.0727 22.4503 29.1751 22.3329 29.2432C22.2155 29.3114 22.0801 29.342 21.9447 29.3311C21.8094 29.3202 21.6807 29.2682 21.5757 29.1822L16.8023 25.5995C16.5719 25.4273 16.292 25.3343 16.0043 25.3343C15.7167 25.3343 15.4368 25.4273 15.2063 25.5995L10.425 29.1808C10.3201 29.2667 10.1915 29.3186 10.0563 29.3296C9.92117 29.3405 9.7859 29.3099 9.66855 29.242C9.55121 29.174 9.45737 29.0719 9.39956 28.9493C9.34175 28.8266 9.32272 28.6893 9.345 28.5555L11.3637 17.1875"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M16 18.668C20.4183 18.668 24 15.0862 24 10.668C24 6.24969 20.4183 2.66797 16 2.66797C11.5817 2.66797 8 6.24969 8 10.668C8 15.0862 11.5817 18.668 16 18.668Z"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </div>
                        <h4 class="font-semibold text-24px text-primary mb-4">شهادات مهنية معتمدة</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            من جهات دولية ومحلية موثوقة
                        </p>
                    </div>

                    <div class="bg-white  rounded-20px p-14 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-6">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.668 2.66797V8.0013" stroke="#0F4C45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M21.332 2.66797V8.0013" stroke="#0F4C45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M25.3333 5.33203H6.66667C5.19391 5.33203 4 6.52594 4 7.9987V26.6654C4 28.1381 5.19391 29.332 6.66667 29.332H25.3333C26.8061 29.332 28 28.1381 28 26.6654V7.9987C28 6.52594 26.8061 5.33203 25.3333 5.33203Z"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M4 13.332H28" stroke="#0F4C45" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>


                        </div>
                        <h4 class="font-semibold text-24px text-primary mb-4">برامج حضورية وعن بُعد</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            مرونة في اختيار نمط التدريب المناسب
                        </p>
                    </div>


                    <div class="bg-white  rounded-20px p-14 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-6">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M21.3346 28V25.3333C21.3346 23.9188 20.7727 22.5623 19.7725 21.5621C18.7723 20.5619 17.4158 20 16.0013 20H8.0013C6.58681 20 5.23026 20.5619 4.23007 21.5621C3.22987 22.5623 2.66797 23.9188 2.66797 25.3333V28"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M12.0013 14.6667C14.9468 14.6667 17.3346 12.2789 17.3346 9.33333C17.3346 6.38781 14.9468 4 12.0013 4C9.05578 4 6.66797 6.38781 6.66797 9.33333C6.66797 12.2789 9.05578 14.6667 12.0013 14.6667Z"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M29.332 27.9985V25.3319C29.3311 24.1502 28.9378 23.0022 28.2139 22.0683C27.4899 21.1344 26.4762 20.4673 25.332 20.1719"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M21.332 4.17188C22.4793 4.46561 23.4961 5.13281 24.2222 6.06829C24.9484 7.00377 25.3425 8.15431 25.3425 9.33854C25.3425 10.5228 24.9484 11.6733 24.2222 12.6088C23.4961 13.5443 22.4793 14.2115 21.332 14.5052"
                                    stroke="#0F4C45" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </div>
                        <h4 class="font-semibold text-24px text-primary mb-4"> ورش مجانية للتوعية</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            محاضرات تعريفية لمساعدتك في الاختيار
                        </p>
                    </div>

                    <div class="bg-white  rounded-20px p-14 flex flex-col items-center">
                        <div class="size-16 center bg-gold rounded-full mb-6">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M29.3346 9.33203L18.0013 20.6654L11.3346 13.9987L2.66797 22.6654" stroke="#0F4C45"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21.332 9.33203H29.332V17.332" stroke="#0F4C45" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </div>
                        <h4 class="font-semibold text-24px text-primary mb-4">مسارات مرتبطة بسوق العمل</h4>
                        <p class="font-normal text-base  text-7a text-center">
                            برامج عملية تلبي احتياجات السوق السعودي
                        </p>
                    </div>

                </div>
            </div>
        </section>


        <section class="section-gap">
            <div class="container">
                <div class="flex justify-between items-center mb-16">
                    <h2 class="font-bold text-40px text-primary mb-4">اخر اخبارنا </h2>
                    <a href=""
                        class="btn btn-outline border-none bg-[#E8F5E9] rounded-10px h-15 font-extrabold px-8 text-primary text-20px">
                        عرض جميع المقالات
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div class="h-[396px] bg-e3 rounded-20px p-7 flex flex-col items-center justify-end text-center   relative overflow-hidden "
                        style="background-image: url('{{ $landingImg }}/home/news1.webp'); background-size: cover; background-position: center;">
                        >
                        <p class="font-bold text-22px text-white z-20 relative">استثمر في نفسك: كيف تختار الدورة التدريبية
                            التي تنقل
                            مسيرتك المهنية للجيل القادم؟</p>
                        <div class="layer bg-linear absolute inset-0 rounded-20px"></div>
                    </div>

                    <div class="h-[396px] bg-e3 rounded-20px p-7 flex flex-col items-center justify-end text-center   relative overflow-hidden "
                        style="background-image: url('{{ $landingImg }}/home/news1.webp'); background-size: cover; background-position: center;">
                        >
                        <p class="font-bold text-22px text-white z-20 relative">استثمر في نفسك: كيف تختار الدورة التدريبية
                            التي تنقل
                            مسيرتك المهنية للجيل القادم؟</p>
                        <div class="layer bg-linear absolute inset-0 rounded-20px"></div>
                    </div>

                    <div class="h-[396px] bg-e3 rounded-20px p-7 flex flex-col items-center justify-end text-center   relative overflow-hidden "
                        style="background-image: url('{{ $landingImg }}/home/news1.webp'); background-size: cover; background-position: center;">
                        >
                        <p class="font-bold text-22px text-white z-20 relative">استثمر في نفسك: كيف تختار الدورة التدريبية
                            التي تنقل
                            مسيرتك المهنية للجيل القادم؟</p>
                        <div class="layer bg-linear absolute inset-0 rounded-20px"></div>
                    </div>

                    <div class="h-[396px] bg-e3 rounded-20px p-7 flex flex-col items-center justify-end text-center   relative overflow-hidden "
                        style="background-image: url('{{ $landingImg }}/home/news1.webp'); background-size: cover; background-position: center;">
                        >
                        <p class="font-bold text-22px text-white z-20 relative">استثمر في نفسك: كيف تختار الدورة التدريبية
                            التي تنقل
                            مسيرتك المهنية للجيل القادم؟</p>
                        <div class="layer bg-linear absolute inset-0 rounded-20px"></div>
                    </div>

                </div>

            </div>
        </section>

        <x-landing_v1::prefooter-cta />



        {{-- <section>
            <div class="container">
                <div class="flex justify-between items-center mb-15 flex-wrap ">
                    <div>
                        <h4 class="font-semibold text-36px mb-3 text-primary">انطلق في رحلتك التعليمية القادمة</h4>
                        <p class="font-normal text-20px text-primary">برامج مكثفة مصممة لسد الفجوة المهارية وتعزيز
                            التنافسية
                            في القطاعات الحيوية</p>
                    </div>
                    <div>
                        <a href="{{ route('landing.v1.courses') }}"
                            class="btn btn-text font-semibold text-20px text-primary">
                            عرض كل الكورسات
                            <span class="icon-[tabler--arrow-left]"></span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($courses as $course)
                        <x-landing_v1::course-card title="{{ $course->title }}" description="{{ $course->description }}"
                            teacher-name="{{ $course->teacher->full_name ?? '' }}"
                            teacher-avatar="{{ !empty($course->teacher) ? $course->teacher->getAvatar() : '' }}"
                            price="{{ $course->price > 0 ? $course->price . ' ر.س' : 'مجاناً' }}"
                            image="{{ $course->image_cover ?? asset('assets/landing_v1/img/contact/hero.webp') }}"
                            slug="{{ $course->slug }}" />
                    @endforeach
                </div>
            </div>
        </section> --}}


        {{-- <section class="section-gap bg-blue py-28 pb-36"
            style="background-image: url('{{ $landingImg }}/home/bg-slide1.webp'); background-size: cover; background-repeat: no-repeat; background-position: bottom;">
            <div class="container">
                <div id="featured-courses"
                    data-carousel='{ "loadingClasses": "opacity-0", "slidesQty": { "xs": 1, "lg": 1 }, "isRTL": true, "isInfiniteLoop": true, "dotsItemClasses": "carousel-dot", "isAutoPlay": true, "speed": 3500 }'
                    class="relative w-full">
                    <div class="carousel">
                        <div class="carousel-body opacity-0">
                            @forelse ($courses as $course)
                                <div class="carousel-slide">
                                    <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                                        <div class="lg:col-span-8 text-white">
                                            <div class="lg:w-[80%]">
                                                <p class="font-semibold text-white text-36px md:w-[60%] mb-5">
                                                    <span class="icon-[tabler--star-filled] text-[#F9AA00]"></span>
                                                    <span class="icon-[tabler--star-filled] text-[#F9AA00]"></span>
                                                    ابدأ رحلة التميز مع دوراتنا الأكثر طلباً
                                                </p>
                                                <h3 class="font-bold text-50px text-white mb-8 lg:w-[75%]">
                                                    {{ $course->title }}
                                                </h3>
                                                <p class="font-normal text-20px mb-8 lg:w-[70%] line-clamp-4">
                                                    {{ $course->summary ?? Str::limit(strip_tags(html_entity_decode($course->description)), 200) }}
                                                </p>
                                                <div>
                                                    <a href="{{ route('landing.v1.course-details', $course->slug) }}"
                                                        class="btn btn-gold h-12 px-20 font-medium text-24px"> اشترك الان
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="lg:col-span-4">
                                            <x-landing_v1::course-card title="{{ $course->title }}"
                                                description="{{ $course->description }}"
                                                teacherName="{{ $course->teacher->full_name ?? '' }}"
                                                teacherAvatar="{{ !empty($course->teacher) ? $course->teacher->getAvatar() : '' }}"
                                                price="{{ $course->price > 0 ? $course->price . ' ر.س' : 'مجاناً' }}"
                                                image="{{ $course->image_cover ?? asset('assets/landing_v1/img/contact/hero.webp') }}"
                                                slug="{{ $course->slug }}" />
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="carousel-slide">
                                    <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                                        <div class="lg:col-span-8 text-white">
                                            <div class="lg:w-[80%]">
                                                <p class="font-semibold text-white text-36px md:w-[60%] mb-5">
                                                    <span class="icon-[tabler--star-filled] text-[#F9AA00]"></span>
                                                    <span class="icon-[tabler--star-filled] text-[#F9AA00]"></span>
                                                    ابدأ رحلة التميز مع دوراتنا الأكثر طلباً
                                                </p>
                                                <h3 class="font-bold text-50px text-white mb-8 lg:w-[75%]">
                                                    الاستراتيجيات الحديثة في إدارة المشاريع الهندسية
                                                </h3>
                                                <p class="font-normal text-20px mb-8 lg:w-[70%]">
                                                    دورة مكثفة صُممت خصيصاً لسد الفجوة بين المعرفة الأكاديمية التطبيق
                                                    الميداني
                                                    في كبرى الشركات.
                                                </p>
                                                <div>
                                                    <a href="{{ route('landing.v1.course-details') }}"
                                                        class="btn btn-gold h-12 px-20 font-medium text-24px"> اشترك الان
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="lg:col-span-4">
                                            <x-landing_v1::course-card title="استشارات هندسية"
                                                description="دورة مكثفة في إدارة المشاريع الهندسية والإنشائية."
                                                teacher-name="المهندس الاستشاري" price="150 ر.س"
                                                image="{{ $landingImg }}/home/course.webp" />
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col items-center gap-3">
                        <div class="carousel-pagination flex justify-center gap-3"></div>
                        <div class="flex items-center gap-3">
                            <button type="button" class="carousel-prev start-0 -translate-x-[-40%]">
                                <span class="size-9.5 bg-base-100 flex items-center justify-center rounded-full shadow">
                                    <span class="icon-[tabler--chevron-left] size-5 cursor-pointer rtl:rotate-180"></span>
                                </span>
                                <span class="sr-only">Previous</span>
                            </button>
                            <button type="button" class="carousel-next end-0 translate-x-[-40%]">
                                <span class="sr-only">Next</span>
                                <span class="size-9.5 bg-base-100 flex items-center justify-center rounded-full shadow">
                                    <span class="icon-[tabler--chevron-right] size-5 cursor-pointer rtl:rotate-180"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}
        {{-- <section class="section-gap">
            <div class="container">
                <h2 class="font-semibold text-36px text-primary mb-14">حلول تعليمية مخصصة لكل احتياج</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($instructors as $instructor)
                        <div class="border border-[#00000029] rounded-8px overflow-hidden flex flex-col">
                            <a href="{{ $instructor->getProfileUrl() }}"
                                class="block mb-4 bg-e3 aspect-[4/3] overflow-hidden h-[242px]">
                                <img src="{{ $instructor->getAvatar() }}" alt="{{ $instructor->full_name }}"
                                    class="w-full h-full object-contain">
                            </a>
                            <div class="px-5 py-2 flex-1">
                                <a href="{{ $instructor->getProfileUrl() }}">
                                    <h6
                                        class="font-semibold text-24px text-primary mb-1 hover:text-secondary transition-colors">
                                        {{ $instructor->full_name }}</h6>
                                </a>
                                <p class="font-normal text-base text-primary leading-6">
                                    {{ $instructor->bio }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section> --}}
    </main>
@endsection
