@extends('panel_v1.student.layouts.app')

@section('content')
@php($heroLogo = $panelStudentImg . '/hero-logo.webp')
@php($noDataImg = $panelStudentImg . '/no-data.webp')

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
                    دورة الجودة الصحية CPHQ
                </p>
                <h5 class="font-bold text-20px text-primary mb-3">
                    واجب المحاضرة الخامسة: إعداد تقرير تحسين الجودة
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

<header class="overflow-hidden text-white pt-8 mt-20 ">
    <div class="container ">
        <div class="px-12 bg-primary relative rounded-20px max-xl:px-5 max-xl:py-10 overflow-hidden">
            <div class="px-15 py-16">
                <h1 class="font-bold text-40px  mb-1">طاب مساؤك ، علا محمد</h1>
                <p class="font-semibold text-20px mb-15">جاهزة لاستكمال دورة "قياس النجاح والجودة" اليوم؟</p>

                <div class="grid grid-cols-1 grid-cols-[repeat(auto-fit,minmax(210px,1fr))] gap-10">
                    <div class="bg-[#FFFFFF29] rounded-12px p-7">
                        <div class="mb-3 center gap-2">
                            <span class="font-semibold text-40px">4</span>
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
                            <span class="font-semibold text-40px">4</span>
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
                            <span class="font-semibold text-40px">4</span>
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
                            <span class="font-semibold text-40px">4</span>
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
                            <span class="font-semibold text-40px">4</span>
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
                    <div class="lg:col-span-8">
                        <div class="relative border border-d9 px-6 py-8 rounded-12px mb-12">
                            <div class="flex items-center gap-5 mb-9">
                                <div class="w-[67px] h-[62px] rounded-12px overflow-hidden center">
                                    <img src="https://www.shutterstock.com/image-photo/free-course-text-button-on-260nw-2387499657.jpg"
                                        class="rounded-12px" alt="course image" width="67" height="62" loading="lazy"
                                        decoding="async">
                                </div>
                                <div>
                                    <h6 class="font-semibold text-24px text-primary mb-4">قياس النجاح والجودة</h6>
                                    <p class="font-medium text-base text-gray">الادارة والتنفيذ</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-13 justify-between flex-wrap mb-12">
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M4.11293 0C4.24752 0 4.37661 0.0534686 4.47178 0.148643C4.56696 0.243818 4.62043 0.372903 4.62043 0.5075V1.45653H10.0703V0.514025C10.0703 0.379428 10.1237 0.250343 10.2189 0.155168C10.3141 0.0599936 10.4432 0.006525 10.5778 0.006525C10.7123 0.006525 10.8414 0.0599936 10.9366 0.155168C11.0318 0.250343 11.0852 0.379428 11.0852 0.514025V1.45653H13.05C13.4344 1.45653 13.8031 1.60919 14.075 1.88096C14.347 2.15273 14.4998 2.52136 14.5 2.9058V13.0507C14.4998 13.4352 14.347 13.8038 14.075 14.0756C13.8031 14.3473 13.4344 14.5 13.05 14.5H1.45C1.06556 14.5 0.696858 14.3473 0.424951 14.0756C0.153044 13.8038 0.000192219 13.4352 0 13.0507L0 2.9058C0.000192219 2.52136 0.153044 2.15273 0.424951 1.88096C0.696858 1.60919 1.06556 1.45653 1.45 1.45653H3.60543V0.506775C3.60562 0.372303 3.65917 0.243405 3.75432 0.148387C3.84948 0.0533689 3.97845 -1.37216e-07 4.11293 0ZM1.015 5.61295V13.0507C1.015 13.1079 1.02625 13.1644 1.04811 13.2172C1.06997 13.27 1.10202 13.3179 1.14241 13.3583C1.1828 13.3987 1.23076 13.4308 1.28353 13.4526C1.33631 13.4745 1.39288 13.4857 1.45 13.4857H13.05C13.1071 13.4857 13.1637 13.4745 13.2165 13.4526C13.2692 13.4308 13.3172 13.3987 13.3576 13.3583C13.398 13.3179 13.43 13.27 13.4519 13.2172C13.4737 13.1644 13.485 13.1079 13.485 13.0507V5.6231L1.015 5.61295ZM4.83358 10.5988V11.8066H3.625V10.5988H4.83358ZM7.85393 10.5988V11.8066H6.64608V10.5988H7.85393ZM10.875 10.5988V11.8066H9.66643V10.5988H10.875ZM4.83358 7.71545V8.9233H3.625V7.71545H4.83358ZM7.85393 7.71545V8.9233H6.64608V7.71545H7.85393ZM10.875 7.71545V8.9233H9.66643V7.71545H10.875ZM3.60543 2.4708H1.45C1.39288 2.4708 1.33631 2.48205 1.28353 2.50391C1.23076 2.52577 1.1828 2.55782 1.14241 2.59821C1.10202 2.6386 1.06997 2.68656 1.04811 2.73933C1.02625 2.79211 1.015 2.84868 1.015 2.9058V4.59868L13.485 4.60883V2.9058C13.485 2.84868 13.4737 2.79211 13.4519 2.73933C13.43 2.68656 13.398 2.6386 13.3576 2.59821C13.3172 2.55782 13.2692 2.52577 13.2165 2.50391C13.1637 2.48205 13.1071 2.4708 13.05 2.4708H11.0852V3.14433C11.0852 3.27892 11.0318 3.40801 10.9366 3.50318C10.8414 3.59836 10.7123 3.65183 10.5778 3.65183C10.4432 3.65183 10.3141 3.59836 10.2189 3.50318C10.1237 3.40801 10.0703 3.27892 10.0703 3.14433V2.4708H4.62043V3.1378C4.62043 3.2724 4.56696 3.40148 4.47178 3.49666C4.37661 3.59183 4.24752 3.6453 4.11293 3.6453C3.97833 3.6453 3.84924 3.59183 3.75407 3.49666C3.65889 3.40148 3.60543 3.2724 3.60543 3.1378V2.4708Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            النوع
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        دورة مسجلة
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9.20833 7.08333H10.2708V10.0938L12.2967 11.2908L11.7654 12.155L9.20833 10.625V7.08333ZM9.91667 4.95833C10.4054 4.95833 10.8871 5.02917 11.3333 5.16375V1.9125L9.20833 2.73417V5.00792C9.44208 4.95833 9.67583 4.95833 9.91667 4.95833ZM14.875 9.91667C14.875 11.2317 14.3526 12.4929 13.4227 13.4227C12.4929 14.3526 11.2317 14.875 9.91667 14.875C7.79167 14.875 5.95 13.515 5.25583 11.6167L4.25 11.2625L0.4675 12.7287L0.354167 12.75C0.260236 12.75 0.170152 12.7127 0.103733 12.6463C0.0373139 12.5798 0 12.4898 0 12.3958V1.68583C0 1.52292 0.10625 1.39542 0.255 1.34583L4.25 0L8.5 1.4875L12.2825 0.02125L12.3958 0C12.4898 0 12.5798 0.0373139 12.6463 0.103733C12.7127 0.170152 12.75 0.260236 12.75 0.354167V5.84375C14.0321 6.72917 14.875 8.23083 14.875 9.91667ZM4.95833 9.91667C4.95833 7.94042 6.11292 6.23333 7.79167 5.43292V2.74125L4.95833 1.74958V10.0087V9.91667ZM9.91667 6.375C8.97736 6.375 8.07652 6.74814 7.41233 7.41233C6.74814 8.07652 6.375 8.97736 6.375 9.91667C6.375 10.856 6.74814 11.7568 7.41233 12.421C8.07652 13.0852 8.97736 13.4583 9.91667 13.4583C10.856 13.4583 11.7568 13.0852 12.421 12.421C13.0852 11.7568 13.4583 10.856 13.4583 9.91667C13.4583 8.97736 13.0852 8.07652 12.421 7.41233C11.7568 6.74814 10.856 6.375 9.91667 6.375ZM1.41667 2.45083V10.8446L3.54167 10.0229V1.73542L1.41667 2.45083Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            ساعات النشاط
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        0:01
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M7.08333 12.75C8.58623 12.75 10.0276 12.153 11.0903 11.0903C12.153 10.0276 12.75 8.58623 12.75 7.08333C12.75 5.58044 12.153 4.1391 11.0903 3.0764C10.0276 2.01369 8.58623 1.41667 7.08333 1.41667C5.58044 1.41667 4.1391 2.01369 3.0764 3.0764C2.01369 4.1391 1.41667 5.58044 1.41667 7.08333C1.41667 8.58623 2.01369 10.0276 3.0764 11.0903C4.1391 12.153 5.58044 12.75 7.08333 12.75ZM7.08333 0C8.01353 0 8.93462 0.183216 9.79401 0.539187C10.6534 0.895158 11.4343 1.41691 12.092 2.07466C12.7498 2.73241 13.2715 3.51327 13.6275 4.37266C13.9835 5.23205 14.1667 6.15314 14.1667 7.08333C14.1667 8.96195 13.4204 10.7636 12.092 12.092C10.7636 13.4204 8.96195 14.1667 7.08333 14.1667C3.16625 14.1667 0 10.9792 0 7.08333C0 5.20472 0.746278 3.40304 2.07466 2.07466C3.40304 0.746278 5.20472 0 7.08333 0ZM7.4375 3.54167V7.26042L10.625 9.15167L10.0938 10.0229L6.375 7.79167V3.54167H7.4375Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            مدة
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        30:00
                                    </p>
                                </div>


                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M8.66862 10.4476C11.4984 10.4415 13.4045 10.365 14.5166 10.3009C15.2519 10.2584 15.8455 9.73746 15.9305 9.0061C16.0201 8.23792 16.1061 7.08333 16.1061 5.48958C16.1061 3.89583 16.0201 2.74125 15.9305 1.97271C15.8455 1.24171 15.2519 0.720729 14.517 0.678229C13.3588 0.611292 11.3401 0.53125 8.31445 0.53125C8.07173 0.53125 7.83562 0.531722 7.60612 0.532667M4.24154 11.8646V15.7604M1.93945 3.1875C1.93945 3.79805 2.18199 4.38359 2.61372 4.81532C3.04544 5.24704 3.63099 5.48958 4.24154 5.48958C4.85209 5.48958 5.43763 5.24704 5.86936 4.81532C6.30108 4.38359 6.54362 3.79805 6.54362 3.1875C6.54362 2.57695 6.30108 1.99141 5.86936 1.55968C5.43763 1.12796 4.85209 0.885417 4.24154 0.885417C3.63099 0.885417 3.04544 1.12796 2.61372 1.55968C2.18199 1.99141 1.93945 2.57695 1.93945 3.1875Z"
                                                stroke="#0F4C45" stroke-width="1.0625" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M9.92184 5.60991C10.1432 5.62266 10.344 5.74414 10.3915 5.96054C10.4425 6.19287 10.4715 6.55377 10.3791 7.05952C10.3284 7.33612 10.0925 7.53481 9.81382 7.57306L6.89761 7.96937L6.26011 14.8119C6.21761 15.2666 5.88824 15.6417 5.4349 15.6937C5.04656 15.7384 4.65599 15.7609 4.26509 15.761C3.81778 15.761 3.41403 15.7313 3.08996 15.6955C2.61609 15.6427 2.27538 15.244 2.24953 14.7676L2.03065 10.7595C1.72592 10.7438 1.42151 10.7222 1.11761 10.6947C0.952737 10.6797 0.799937 10.6019 0.690879 10.4774C0.58182 10.3528 0.524866 10.1911 0.531819 10.0257C0.573257 8.69045 0.74184 7.47 0.88209 6.65577C0.987632 6.04377 1.50613 5.60141 2.12663 5.57131C4.59092 5.4516 7.38246 5.46435 9.92184 5.61027V5.60991Z"
                                                stroke="#0F4C45" stroke-width="1.0625" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            محاضرات
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        12
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="13" height="15" viewBox="0 0 13 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M1.41667 14.1667C1.02708 14.1667 0.693695 14.0281 0.4165 13.7509C0.139306 13.4737 0.000472222 13.1401 0 12.75V2.83333C0 2.44375 0.138833 2.11036 0.4165 1.83317C0.694167 1.55597 1.02756 1.41714 1.41667 1.41667H4.39167C4.54514 0.991667 4.80203 0.649306 5.16233 0.389583C5.52264 0.129861 5.92686 0 6.375 0C6.82314 0 7.2276 0.129861 7.58838 0.389583C7.94915 0.649306 8.20581 0.991667 8.35833 1.41667H11.3333C11.7229 1.41667 12.0565 1.5555 12.3342 1.83317C12.6119 2.11083 12.7505 2.44422 12.75 2.83333V12.75C12.75 13.1396 12.6114 13.4732 12.3342 13.7509C12.057 14.0285 11.7234 14.1671 11.3333 14.1667H1.41667ZM1.41667 12.75H11.3333V2.83333H1.41667V12.75ZM2.83333 11.3333H7.79167V9.91667H2.83333V11.3333ZM2.83333 8.5H9.91667V7.08333H2.83333V8.5ZM2.83333 5.66667H9.91667V4.25H2.83333V5.66667ZM6.75608 2.15192C6.85619 2.05133 6.90625 1.92431 6.90625 1.77083C6.90625 1.61736 6.85596 1.49057 6.75538 1.39046C6.65479 1.29035 6.528 1.24006 6.375 1.23958C6.222 1.23911 6.09521 1.2894 5.99463 1.39046C5.89404 1.49151 5.84375 1.61831 5.84375 1.77083C5.84375 1.92336 5.89404 2.05039 5.99463 2.15192C6.09521 2.25344 6.222 2.3035 6.375 2.30208C6.528 2.30067 6.65503 2.25108 6.75608 2.15192Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            تكليفات
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        0
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="12" height="15" viewBox="0 0 12 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10.5 0H1.5C0.675 0 0 0.675 0 1.5V13.5C0 14.325 0.675 15 1.5 15H10.5C11.325 15 12 14.325 12 13.5V1.5C12 0.675 11.325 0 10.5 0ZM3.75 1.5H5.25V5.25L4.5 4.6875L3.75 5.25V1.5ZM10.5 13.5H1.5V1.5H2.25V8.25L4.5 6.5625L6.75 8.25V1.5H10.5V13.5Z"
                                                fill="#0F4C45" />
                                        </svg>

                                        <span class="font-medium text-base text-gray">
                                            تاريخ التسجيل
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        22 يوليو 2026
                                    </p>
                                </div>

                            </div>
                            <div class="bg-fa px-4 py-6 flex items-center justify-between rounded-10px">
                                <div class="grow">
                                    <p class="font-semibold text-12px text-black mb-3">72% متوسط معدل اتقدم</p>
                                    <div class="progress w-[70%] h-3 bg-[#F0EFEF]" role="progressbar"
                                        aria-label="Primary Progressbar" aria-valuenow="75" aria-valuemin="0"
                                        aria-valuemax="100">
                                        <div class="progress-bar progress-primary w-2/4"></div>
                                    </div>
                                </div>

                                <a href="{{ route('panel.v1.student.course.watch', ['slug' => 'demo']) }}"
                                    class="btn btn-primary font-semibold text-12px">استكمل الان</a>
                            </div>

                            <div class="absolute top-6 end-6">
                                <div class="dropdown relative inline-flex rtl:[--placement:bottom-end]">
                                    <button id="dropdown-menu-icon" type="button"
                                        class="dropdown-toggle btn btn-square " aria-haspopup="menu"
                                        aria-expanded="false" aria-label="Dropdown">
                                        <span class="icon-[tabler--dots] size-6"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu"
                                        aria-orientation="vertical" aria-labelledby="dropdown-menu-icon">
                                        <li><a class="dropdown-item font-medium text-14px text-primary"
                                                href="{{ route('panel.v1.student.course.watch', ['slug' => 'demo']) }}">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>

                                                صفحة التعلم


                                            </a></li>
                                        <li><a class="dropdown-item font-medium text-14px text-primary" href="#">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>

                                                صفحة تفاصيل الدورة


                                            </a></li>

                                        <li><a class="dropdown-item font-medium text-14px text-primary" href="#">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>
                                                تفاصيل الدفع (فاتورة)


                                            </a></li>

                                        <li><a class="dropdown-item font-medium text-14px text-primary" href="#">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>

                                                تقيم الدورة
                                            </a></li>

                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="relative border border-d9 px-6 py-8 rounded-12px mb-12">
                            <div class="flex items-center gap-5 mb-9">
                                <div class="w-[67px] h-[62px] rounded-12px overflow-hidden center">
                                    <img src="https://www.shutterstock.com/image-photo/free-course-text-button-on-260nw-2387499657.jpg"
                                        class="rounded-12px" alt="course image" width="67" height="62" loading="lazy"
                                        decoding="async">
                                </div>
                                <div>
                                    <h6 class="font-semibold text-24px text-primary mb-4">قياس النجاح والجودة</h6>
                                    <p class="font-medium text-base text-gray">الادارة والتنفيذ</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-13 justify-between flex-wrap mb-12">
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M4.11293 0C4.24752 0 4.37661 0.0534686 4.47178 0.148643C4.56696 0.243818 4.62043 0.372903 4.62043 0.5075V1.45653H10.0703V0.514025C10.0703 0.379428 10.1237 0.250343 10.2189 0.155168C10.3141 0.0599936 10.4432 0.006525 10.5778 0.006525C10.7123 0.006525 10.8414 0.0599936 10.9366 0.155168C11.0318 0.250343 11.0852 0.379428 11.0852 0.514025V1.45653H13.05C13.4344 1.45653 13.8031 1.60919 14.075 1.88096C14.347 2.15273 14.4998 2.52136 14.5 2.9058V13.0507C14.4998 13.4352 14.347 13.8038 14.075 14.0756C13.8031 14.3473 13.4344 14.5 13.05 14.5H1.45C1.06556 14.5 0.696858 14.3473 0.424951 14.0756C0.153044 13.8038 0.000192219 13.4352 0 13.0507L0 2.9058C0.000192219 2.52136 0.153044 2.15273 0.424951 1.88096C0.696858 1.60919 1.06556 1.45653 1.45 1.45653H3.60543V0.506775C3.60562 0.372303 3.65917 0.243405 3.75432 0.148387C3.84948 0.0533689 3.97845 -1.37216e-07 4.11293 0ZM1.015 5.61295V13.0507C1.015 13.1079 1.02625 13.1644 1.04811 13.2172C1.06997 13.27 1.10202 13.3179 1.14241 13.3583C1.1828 13.3987 1.23076 13.4308 1.28353 13.4526C1.33631 13.4745 1.39288 13.4857 1.45 13.4857H13.05C13.1071 13.4857 13.1637 13.4745 13.2165 13.4526C13.2692 13.4308 13.3172 13.3987 13.3576 13.3583C13.398 13.3179 13.43 13.27 13.4519 13.2172C13.4737 13.1644 13.485 13.1079 13.485 13.0507V5.6231L1.015 5.61295ZM4.83358 10.5988V11.8066H3.625V10.5988H4.83358ZM7.85393 10.5988V11.8066H6.64608V10.5988H7.85393ZM10.875 10.5988V11.8066H9.66643V10.5988H10.875ZM4.83358 7.71545V8.9233H3.625V7.71545H4.83358ZM7.85393 7.71545V8.9233H6.64608V7.71545H7.85393ZM10.875 7.71545V8.9233H9.66643V7.71545H10.875ZM3.60543 2.4708H1.45C1.39288 2.4708 1.33631 2.48205 1.28353 2.50391C1.23076 2.52577 1.1828 2.55782 1.14241 2.59821C1.10202 2.6386 1.06997 2.68656 1.04811 2.73933C1.02625 2.79211 1.015 2.84868 1.015 2.9058V4.59868L13.485 4.60883V2.9058C13.485 2.84868 13.4737 2.79211 13.4519 2.73933C13.43 2.68656 13.398 2.6386 13.3576 2.59821C13.3172 2.55782 13.2692 2.52577 13.2165 2.50391C13.1637 2.48205 13.1071 2.4708 13.05 2.4708H11.0852V3.14433C11.0852 3.27892 11.0318 3.40801 10.9366 3.50318C10.8414 3.59836 10.7123 3.65183 10.5778 3.65183C10.4432 3.65183 10.3141 3.59836 10.2189 3.50318C10.1237 3.40801 10.0703 3.27892 10.0703 3.14433V2.4708H4.62043V3.1378C4.62043 3.2724 4.56696 3.40148 4.47178 3.49666C4.37661 3.59183 4.24752 3.6453 4.11293 3.6453C3.97833 3.6453 3.84924 3.59183 3.75407 3.49666C3.65889 3.40148 3.60543 3.2724 3.60543 3.1378V2.4708Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            النوع
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        دورة مسجلة
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9.20833 7.08333H10.2708V10.0938L12.2967 11.2908L11.7654 12.155L9.20833 10.625V7.08333ZM9.91667 4.95833C10.4054 4.95833 10.8871 5.02917 11.3333 5.16375V1.9125L9.20833 2.73417V5.00792C9.44208 4.95833 9.67583 4.95833 9.91667 4.95833ZM14.875 9.91667C14.875 11.2317 14.3526 12.4929 13.4227 13.4227C12.4929 14.3526 11.2317 14.875 9.91667 14.875C7.79167 14.875 5.95 13.515 5.25583 11.6167L4.25 11.2625L0.4675 12.7287L0.354167 12.75C0.260236 12.75 0.170152 12.7127 0.103733 12.6463C0.0373139 12.5798 0 12.4898 0 12.3958V1.68583C0 1.52292 0.10625 1.39542 0.255 1.34583L4.25 0L8.5 1.4875L12.2825 0.02125L12.3958 0C12.4898 0 12.5798 0.0373139 12.6463 0.103733C12.7127 0.170152 12.75 0.260236 12.75 0.354167V5.84375C14.0321 6.72917 14.875 8.23083 14.875 9.91667ZM4.95833 9.91667C4.95833 7.94042 6.11292 6.23333 7.79167 5.43292V2.74125L4.95833 1.74958V10.0087V9.91667ZM9.91667 6.375C8.97736 6.375 8.07652 6.74814 7.41233 7.41233C6.74814 8.07652 6.375 8.97736 6.375 9.91667C6.375 10.856 6.74814 11.7568 7.41233 12.421C8.07652 13.0852 8.97736 13.4583 9.91667 13.4583C10.856 13.4583 11.7568 13.0852 12.421 12.421C13.0852 11.7568 13.4583 10.856 13.4583 9.91667C13.4583 8.97736 13.0852 8.07652 12.421 7.41233C11.7568 6.74814 10.856 6.375 9.91667 6.375ZM1.41667 2.45083V10.8446L3.54167 10.0229V1.73542L1.41667 2.45083Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            ساعات النشاط
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        0:01
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M7.08333 12.75C8.58623 12.75 10.0276 12.153 11.0903 11.0903C12.153 10.0276 12.75 8.58623 12.75 7.08333C12.75 5.58044 12.153 4.1391 11.0903 3.0764C10.0276 2.01369 8.58623 1.41667 7.08333 1.41667C5.58044 1.41667 4.1391 2.01369 3.0764 3.0764C2.01369 4.1391 1.41667 5.58044 1.41667 7.08333C1.41667 8.58623 2.01369 10.0276 3.0764 11.0903C4.1391 12.153 5.58044 12.75 7.08333 12.75ZM7.08333 0C8.01353 0 8.93462 0.183216 9.79401 0.539187C10.6534 0.895158 11.4343 1.41691 12.092 2.07466C12.7498 2.73241 13.2715 3.51327 13.6275 4.37266C13.9835 5.23205 14.1667 6.15314 14.1667 7.08333C14.1667 8.96195 13.4204 10.7636 12.092 12.092C10.7636 13.4204 8.96195 14.1667 7.08333 14.1667C3.16625 14.1667 0 10.9792 0 7.08333C0 5.20472 0.746278 3.40304 2.07466 2.07466C3.40304 0.746278 5.20472 0 7.08333 0ZM7.4375 3.54167V7.26042L10.625 9.15167L10.0938 10.0229L6.375 7.79167V3.54167H7.4375Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            مدة
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        30:00
                                    </p>
                                </div>


                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M8.66862 10.4476C11.4984 10.4415 13.4045 10.365 14.5166 10.3009C15.2519 10.2584 15.8455 9.73746 15.9305 9.0061C16.0201 8.23792 16.1061 7.08333 16.1061 5.48958C16.1061 3.89583 16.0201 2.74125 15.9305 1.97271C15.8455 1.24171 15.2519 0.720729 14.517 0.678229C13.3588 0.611292 11.3401 0.53125 8.31445 0.53125C8.07173 0.53125 7.83562 0.531722 7.60612 0.532667M4.24154 11.8646V15.7604M1.93945 3.1875C1.93945 3.79805 2.18199 4.38359 2.61372 4.81532C3.04544 5.24704 3.63099 5.48958 4.24154 5.48958C4.85209 5.48958 5.43763 5.24704 5.86936 4.81532C6.30108 4.38359 6.54362 3.79805 6.54362 3.1875C6.54362 2.57695 6.30108 1.99141 5.86936 1.55968C5.43763 1.12796 4.85209 0.885417 4.24154 0.885417C3.63099 0.885417 3.04544 1.12796 2.61372 1.55968C2.18199 1.99141 1.93945 2.57695 1.93945 3.1875Z"
                                                stroke="#0F4C45" stroke-width="1.0625" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M9.92184 5.60991C10.1432 5.62266 10.344 5.74414 10.3915 5.96054C10.4425 6.19287 10.4715 6.55377 10.3791 7.05952C10.3284 7.33612 10.0925 7.53481 9.81382 7.57306L6.89761 7.96937L6.26011 14.8119C6.21761 15.2666 5.88824 15.6417 5.4349 15.6937C5.04656 15.7384 4.65599 15.7609 4.26509 15.761C3.81778 15.761 3.41403 15.7313 3.08996 15.6955C2.61609 15.6427 2.27538 15.244 2.24953 14.7676L2.03065 10.7595C1.72592 10.7438 1.42151 10.7222 1.11761 10.6947C0.952737 10.6797 0.799937 10.6019 0.690879 10.4774C0.58182 10.3528 0.524866 10.1911 0.531819 10.0257C0.573257 8.69045 0.74184 7.47 0.88209 6.65577C0.987632 6.04377 1.50613 5.60141 2.12663 5.57131C4.59092 5.4516 7.38246 5.46435 9.92184 5.61027V5.60991Z"
                                                stroke="#0F4C45" stroke-width="1.0625" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            محاضرات
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        12
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="13" height="15" viewBox="0 0 13 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M1.41667 14.1667C1.02708 14.1667 0.693695 14.0281 0.4165 13.7509C0.139306 13.4737 0.000472222 13.1401 0 12.75V2.83333C0 2.44375 0.138833 2.11036 0.4165 1.83317C0.694167 1.55597 1.02756 1.41714 1.41667 1.41667H4.39167C4.54514 0.991667 4.80203 0.649306 5.16233 0.389583C5.52264 0.129861 5.92686 0 6.375 0C6.82314 0 7.2276 0.129861 7.58838 0.389583C7.94915 0.649306 8.20581 0.991667 8.35833 1.41667H11.3333C11.7229 1.41667 12.0565 1.5555 12.3342 1.83317C12.6119 2.11083 12.7505 2.44422 12.75 2.83333V12.75C12.75 13.1396 12.6114 13.4732 12.3342 13.7509C12.057 14.0285 11.7234 14.1671 11.3333 14.1667H1.41667ZM1.41667 12.75H11.3333V2.83333H1.41667V12.75ZM2.83333 11.3333H7.79167V9.91667H2.83333V11.3333ZM2.83333 8.5H9.91667V7.08333H2.83333V8.5ZM2.83333 5.66667H9.91667V4.25H2.83333V5.66667ZM6.75608 2.15192C6.85619 2.05133 6.90625 1.92431 6.90625 1.77083C6.90625 1.61736 6.85596 1.49057 6.75538 1.39046C6.65479 1.29035 6.528 1.24006 6.375 1.23958C6.222 1.23911 6.09521 1.2894 5.99463 1.39046C5.89404 1.49151 5.84375 1.61831 5.84375 1.77083C5.84375 1.92336 5.89404 2.05039 5.99463 2.15192C6.09521 2.25344 6.222 2.3035 6.375 2.30208C6.528 2.30067 6.65503 2.25108 6.75608 2.15192Z"
                                                fill="#0F4C45" />
                                        </svg>


                                        <span class="font-medium text-base text-gray">
                                            تكليفات
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        0
                                    </p>
                                </div>
                                <div>
                                    <div class="center gap-3 mb-2">
                                        <svg width="12" height="15" viewBox="0 0 12 15" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10.5 0H1.5C0.675 0 0 0.675 0 1.5V13.5C0 14.325 0.675 15 1.5 15H10.5C11.325 15 12 14.325 12 13.5V1.5C12 0.675 11.325 0 10.5 0ZM3.75 1.5H5.25V5.25L4.5 4.6875L3.75 5.25V1.5ZM10.5 13.5H1.5V1.5H2.25V8.25L4.5 6.5625L6.75 8.25V1.5H10.5V13.5Z"
                                                fill="#0F4C45" />
                                        </svg>

                                        <span class="font-medium text-base text-gray">
                                            تاريخ التسجيل
                                        </span>
                                    </div>
                                    <p class="font-medium text-base text-primary text-center">
                                        22 يوليو 2026
                                    </p>
                                </div>

                            </div>
                            <div class="bg-fa px-4 py-6 flex items-center justify-between rounded-10px">
                                <div class="grow">
                                    <p class="font-semibold text-12px text-black mb-3">72% متوسط معدل اتقدم</p>
                                    <div class="progress w-[70%] h-3 bg-[#F0EFEF]" role="progressbar"
                                        aria-label="Primary Progressbar" aria-valuenow="75" aria-valuemin="0"
                                        aria-valuemax="100">
                                        <div class="progress-bar progress-primary w-2/4"></div>
                                    </div>
                                </div>

                                <a href="{{ route('panel.v1.student.course.watch', ['slug' => 'demo']) }}"
                                    class="btn btn-primary font-semibold text-12px">استكمل الان</a>
                            </div>
                            <div class="absolute top-6 end-6">
                                <div class="dropdown relative inline-flex rtl:[--placement:bottom-end]">
                                    <button id="dropdown-menu-icon" type="button"
                                        class="dropdown-toggle btn btn-square " aria-haspopup="menu"
                                        aria-expanded="false" aria-label="Dropdown">
                                        <span class="icon-[tabler--dots] size-6"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu"
                                        aria-orientation="vertical" aria-labelledby="dropdown-menu-icon">
                                        <li><a class="dropdown-item font-medium text-14px text-primary"
                                                href="{{ route('panel.v1.student.course.watch', ['slug' => 'demo']) }}">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>

                                                صفحة التعلم


                                            </a></li>
                                        <li><a class="dropdown-item font-medium text-14px text-primary" href="#">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>

                                                صفحة تفاصيل الدورة


                                            </a></li>

                                        <li><a class="dropdown-item font-medium text-14px text-primary" href="#">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>
                                                تفاصيل الدفع (فاتورة)


                                            </a></li>

                                        <li><a class="dropdown-item font-medium text-14px text-primary" href="#">

                                                <svg width="11" height="2" viewBox="0 0 11 2" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <line x1="10" y1="1" x2="1" y2="0.999999" stroke="#C99C69"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>

                                                تقيم الدورة
                                            </a></li>

                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Empty state when student has no courses — set $hasCourses = true when courses exist --}}
                        @php($hasCourses = false)
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
                <div class="bg-[#FFF9F9] px-10 py-11 border border-[#EFEFEF] rounded-12px mb-11">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-32px text-primary mb-5">🔴 محاضرة مباشرة</h4>
                            <h3 class="font-semibold text-30px text-black mb-5">عنوان المحاضرة : مناقشة أساليب ربط
                                واجهات</h3>
                            <p class="font-semibold text-24px text-black">المحاضر: د. إبراهيم المالكي</p>
                        </div>

                        <button class="btn btn-primary font-semibold text-16px mt-4">
                            الانضمام إلى المحاضرة
                        </button>
                    </div>

                    <div
                        class="bg-[#FFD9D9] text-black *:font-semibold text-20px text-black flex justify-between items-center px-4 py-6 rounded-10px mt-8">
                        <p>
                            الموعد: اليوم | بدأت منذ 15 دقيقة (الساعة 7:00 مساءً)
                        </p>
                        <span>المدة المتوقعة: 90 دقيقة</span>

                    </div>
                </div>

                <div class="border border-d9 px-10 py-11 rounded-12px ">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-semibold text-32px text-primary mb-7">✅ محاضرة مكتملة</span>
                            <h2 class="font-semibold text-30px text-black mb-5">عنوان المحاضرة : مقدمة في بناء الأنظمة
                                المدمجة واستخدام الأيقونات
                            </h2>
                            <p class="font-semibold text-24px text-black mb-9">المحاضر: د. محمد أبو هيبة</p>
                        </div>
                        <div class="flex gap-4 items-center">
                            <button class="btn btn-outline font-semibold text-16px text-primary h-13">تحميل ملفات
                                المحاضرة (PDF)</button>
                            <button class="btn btn-primary font-semibold text-16px text-white h-13"> اعادة
                                المحاضرة</button>


                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-fa rounded-10px px-4 py-6">
                        <span class="font-semibold text-24px text-black">تاريخ الانعقاد: 20 يوليو 2026 (تمت في الساعة
                            5:00 مساءً)</span>
                        <span class="font-semibold text-base text-black">
                            مدة المحاضرة: ساعة و20 دقيقة
                        </span>

                    </div>
                </div>


            </div>
            <div id="tabs-large-3" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-3">
                @php($hasAssignments = true)
                @if ($hasAssignments)
                    <div>
                        <div class="bg-[#F9F5F5] rounded-4px px-7 py-3 mb-10 ">
                            <p class="font-semibold text-24px text-primary">التكليفات المعلقة</p>
                        </div>
                        <div class="grid grid-cols-[repeat(auto-fill,minmax(25%,1fr))] gap-8 mb-15">

                            <div class="border border-d9 p-8 rounded-8px">
                                <div class="flex items-center gap-3 mb-7">
                                    <div class="size-13 center p-4 bg-[#C99C6921] rounded-12px">
                                        <svg width="25" height="23" viewBox="0 0 25 23" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M7.15463 3.06836H3.06587C1.93635 3.06836 1.02148 3.98363 1.02148 5.11274V19.4234C1.02148 20.5525 1.93635 21.4678 3.06587 21.4678H21.4653C22.5948 21.4678 23.5097 20.5525 23.5097 19.4234V5.11274C23.5097 3.98363 22.5948 3.06836 21.4653 3.06836H17.3765"
                                                stroke="#0F4C45" stroke-width="2.04445" stroke-linecap="round" />
                                            <path
                                                d="M9.19824 0.510742H15.3311C15.6133 0.510742 15.8418 0.7402 15.8418 1.02246V4.08887C15.8417 4.37108 15.6133 4.59961 15.3311 4.59961H9.19824C8.91602 4.59961 8.68658 4.37108 8.68652 4.08887V1.02246C8.68652 0.7402 8.91598 0.510742 9.19824 0.510742Z"
                                                fill="#0F4C45" stroke="#0F4C45" stroke-width="1.02223" />
                                        </svg>

                                    </div>
                                    <div class="">
                                        <h4 class="font-bold text-20px text-primary">
                                            تكليف التطبيق العملي (السلام)
                                        </h4>
                                        <p class="font-semibold text-12px text-primary">
                                            الممارس المعتمد في جودة الرعاية الصحية CPHQ
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 mb-8">
                                    <div class="bg-[#F8F2EC] rounded-4px border-[#EBDDCD] h-9 center px-7 py-4">
                                        <div class="font-bold text-13px text-[#475569]">
                                            🏆 25 درجة
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="bg-[#F8F2EC] rounded-4px border-[#EBDDCD] h-9 center px-7 py-4">
                                            <div class="font-bold text-13px text-[#475569]">
                                                ⏳ غير محدود
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div>
                                    <button type="button" class="btn btn-primary btn-block" aria-haspopup="dialog"
                                        aria-expanded="false" aria-controls="assignment-submit-modal"
                                        data-overlay="#assignment-submit-modal">عرض التكليف</button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#F9F5F5] rounded-4px px-7 py-3 mb-10 ">
                            <p class="font-semibold text-24px text-primary">جميع التكليفات المسلمة</p>


                        </div>

                        <div
                            class="border border-d9 rounded-10px px-7 py-6 flex items-center justify-between mb-8 gap-16">
                            <div>
                                <span class="font-medium text-14px text-gray mb-1.5">العنوان والدورة</span>
                                <p class="font-bold text-18px text-primary">تكليف التطبيق العملي (السلام)</p>
                                <p class="font-medium text-12px text-primary">الممارس المعتمد في جودة الرعاية الصحية
                                    CPHQ</p>
                            </div>
                            <div class="flex items-center gap-8">
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">الموعدالنهائي</p>
                                    <p class="text-center text-black">__</p>
                                </div>

                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">التسليم الأول</p>
                                    <p class="text-center text-black">__</p>
                                </div>


                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">التسليم الأخير</p>
                                    <p class="text-center text-black">__</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">المحاولات</p>
                                    <p class="text-center text-black">__</p>
                                </div>

                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">الدرجة</p>
                                    <p class="text-center text-black">__</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">درجة النجاح</p>
                                    <p class="text-center text-black">__</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">حالة</p>
                                    <p class="text-center text-black">__</p>
                                </div>

                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">اجراءات</p>
                                    <p class="text-center text-black">__</p>
                                </div>


                            </div>
                        </div>

                        <div
                            class="border border-d9 rounded-10px px-7 py-6 flex items-center justify-between mb-8 gap-16">
                            <div>
                                <span class="font-medium text-14px text-gray mb-1.5">العنوان والدورة</span>
                                <p class="font-bold text-18px text-primary">تكليف التطبيق العملي (السلام)</p>
                                <p class="font-medium text-12px text-primary">الممارس المعتمد في جودة الرعاية الصحية
                                    CPHQ</p>
                            </div>
                            <div class="flex items-center gap-8">
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">الموعدالنهائي</p>
                                    <p class="text-center text-black">__</p>
                                </div>

                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">التسليم الأول</p>
                                    <p class="text-center text-black">__</p>
                                </div>


                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">التسليم الأخير</p>
                                    <p class="text-center text-black">__</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">المحاولات</p>
                                    <p class="text-center text-black">__</p>
                                </div>

                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">الدرجة</p>
                                    <p class="text-center text-black">__</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">درجة النجاح</p>
                                    <p class="text-center text-black">__</p>
                                </div>
                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">حالة</p>
                                    <p class="text-center text-black">__</p>
                                </div>

                                <div>
                                    <p class="font-medium text-16px text-gray mb-12">اجراءات</p>
                                    <p class="text-center text-black">__</p>
                                </div>


                            </div>
                        </div>

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
                <div class="mt-16 center flex-col text-center">
                    <div class="mb-8">
                        <img src="{{ $noDataImg }}" alt="لا توجد لديك اختبار بعد"
                            class="max-w-xs w-full mx-auto" width="" height="" loading="lazy" decoding="async">
                    </div>
                    <p class="font-semibold text-32px text-black">لا توجد لديك اختبار بعد</p>
                </div>
            </div>
                <div id="tabs-large-5" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-5">
                    <div class="mt-15  gap-14 grid grid-cols-[repeat(auto-fill,minmax(25%,1fr))]">
                        <div class="rounded-13px border border-d9 p-6 flex flex-col gap-5">
                            <div class="bg-primary h-56 rounded-8px">

                            </div>
                            <div class="flex  justify-between">
                                <div>
                                    <h5 class="font-semibold text-28px text-primary mb-4">
                                        قياس النجاح والجودة
                                    </h5>
                                    <p class="fontmedium text-20px text-gray mb-7">
                                        الادارة والتنفيذ
                                    </p>
                                    <p class="fontmedium text-base text-gray">اكتمل في 16 يوليو 2025</p>
                                </div>

                                <div class="btn btn-text h-fit justify-center ">
                                    <svg width="38" height="38" viewBox="0 0 38 38" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M4.75 17.416C4.75 18.8917 4.75 19.6295 4.99067 20.2106C5.14981 20.5951 5.38317 20.9444 5.6774 21.2386C5.97162 21.5328 6.32095 21.7662 6.70542 21.9253C7.2865 22.166 8.02433 22.166 9.5 22.166H10.5688C11.4887 22.166 11.9494 22.166 12.3167 22.3782C12.4094 22.4315 12.4964 22.4941 12.5764 22.565C12.8931 22.8468 13.0387 23.2823 13.3301 24.1563L13.528 24.7516C13.8763 25.7966 14.0505 26.3191 14.4653 26.6168C14.8802 26.916 15.4312 26.916 16.5332 26.916H21.4684C22.5688 26.916 23.1198 26.916 23.5347 26.6168C23.9495 26.3175 24.1237 25.7966 24.472 24.7516L24.6699 24.1563C24.9613 23.2823 25.1069 22.8468 25.4236 22.565C25.5036 22.4941 25.5906 22.4315 25.6833 22.3782C26.0506 22.166 26.5113 22.166 27.4313 22.166H28.5C29.9757 22.166 30.7135 22.166 31.2946 21.9253C31.679 21.7662 32.0284 21.5328 32.3226 21.2386C32.6168 20.9444 32.8502 20.5951 33.0093 20.2106C33.25 19.6295 33.25 18.8917 33.25 17.416M25.3333 14.2493L19 18.9993L12.6667 14.2493M19 18.9993V3.16602"
                                            stroke="#0F4C45" stroke-width="3.16667" />
                                        <path
                                            d="M25.3333 7.91602H26.9167C29.9028 7.91602 31.3943 7.91602 32.3222 8.84385C33.25 9.77168 33.25 11.2632 33.25 14.2493V26.916C33.25 29.9022 33.25 31.3937 32.3222 32.3215C31.3943 33.2493 29.9028 33.2493 26.9167 33.2493H11.0833C8.09717 33.2493 6.60567 33.2493 5.67783 32.3215C4.75 31.3937 4.75 29.9022 4.75 26.916V14.2493C4.75 11.2632 4.75 9.77168 5.67783 8.84385C6.60567 7.91602 8.09717 7.91602 11.0833 7.91602H12.6667"
                                            stroke="#0F4C45" stroke-width="3.16667" />
                                    </svg>


                                    <div class="text-center w-full font-semibold text-14px text-gray">تحميل</div>
                                </div>
                            </div>

                        </div>
                        <div class="rounded-13px border border-d9 p-6 flex flex-col gap-5">
                            <div class="bg-primary h-56 rounded-8px">

                            </div>
                            <div class="flex  justify-between">
                                <div>
                                    <h5 class="font-semibold text-28px text-primary mb-4">
                                        قياس النجاح والجودة
                                    </h5>
                                    <p class="fontmedium text-20px text-gray mb-7">
                                        الادارة والتنفيذ
                                    </p>
                                    <p class="fontmedium text-base text-gray">اكتمل في 16 يوليو 2025</p>
                                </div>

                                <div class="btn btn-text h-fit justify-center ">
                                    <svg width="38" height="38" viewBox="0 0 38 38" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M4.75 17.416C4.75 18.8917 4.75 19.6295 4.99067 20.2106C5.14981 20.5951 5.38317 20.9444 5.6774 21.2386C5.97162 21.5328 6.32095 21.7662 6.70542 21.9253C7.2865 22.166 8.02433 22.166 9.5 22.166H10.5688C11.4887 22.166 11.9494 22.166 12.3167 22.3782C12.4094 22.4315 12.4964 22.4941 12.5764 22.565C12.8931 22.8468 13.0387 23.2823 13.3301 24.1563L13.528 24.7516C13.8763 25.7966 14.0505 26.3191 14.4653 26.6168C14.8802 26.916 15.4312 26.916 16.5332 26.916H21.4684C22.5688 26.916 23.1198 26.916 23.5347 26.6168C23.9495 26.3175 24.1237 25.7966 24.472 24.7516L24.6699 24.1563C24.9613 23.2823 25.1069 22.8468 25.4236 22.565C25.5036 22.4941 25.5906 22.4315 25.6833 22.3782C26.0506 22.166 26.5113 22.166 27.4313 22.166H28.5C29.9757 22.166 30.7135 22.166 31.2946 21.9253C31.679 21.7662 32.0284 21.5328 32.3226 21.2386C32.6168 20.9444 32.8502 20.5951 33.0093 20.2106C33.25 19.6295 33.25 18.8917 33.25 17.416M25.3333 14.2493L19 18.9993L12.6667 14.2493M19 18.9993V3.16602"
                                            stroke="#0F4C45" stroke-width="3.16667" />
                                        <path
                                            d="M25.3333 7.91602H26.9167C29.9028 7.91602 31.3943 7.91602 32.3222 8.84385C33.25 9.77168 33.25 11.2632 33.25 14.2493V26.916C33.25 29.9022 33.25 31.3937 32.3222 32.3215C31.3943 33.2493 29.9028 33.2493 26.9167 33.2493H11.0833C8.09717 33.2493 6.60567 33.2493 5.67783 32.3215C4.75 31.3937 4.75 29.9022 4.75 26.916V14.2493C4.75 11.2632 4.75 9.77168 5.67783 8.84385C6.60567 7.91602 8.09717 7.91602 11.0833 7.91602H12.6667"
                                            stroke="#0F4C45" stroke-width="3.16667" />
                                    </svg>


                                    <div class="text-center w-full font-semibold text-14px text-gray">تحميل</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div id="tabs-large-6" class="hidden" role="tabpanel" aria-labelledby="tabs-large-item-6">
                    <p class="text-base-content/80 text-lg">التعليقات</p>
                </div>
            </div>
        </div>


        @include('panel_v1.student.components.assignment-submit-modal')
</section>


@endsection
