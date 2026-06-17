@extends('landing_v1.layouts.app')

@section('content')
@php($landingImg = asset('assets/landing_v1/img/about'))
<main>
    <header class=" min-h-screen text-white flex items-center"
        style="background-image: url('{{ $landingImg }}/hero.webp'); background-repeat: no-repeat; background-size: cover;">
        <div class="container ">
            <div class="lg:w-[50%] w-full px-4">
                <div class="breadcrumbs mb-1.5">
                    <ul>
                        <li>
                            <a href="{{ route('landing.v1.index') }}" class="font-medium text-24px text-white">الرئيسية</a>
                        </li>
                        <li class="breadcrumbs-separator rtl:rotate-180"><span
                                class="icon-[tabler--chevron-right] text-white"></span></li>
                        <li>
                            <a href="{{ route('landing.v1.about') }}" class="font-medium text-24px text-white">من نحن</a>
                        </li>
                    </ul>
                </div>
                <h1 class="font-bold text-48px  mb-5 text-white">من قلب الحاجة إلى قلب الإنجاز</h1>
                <p class="font-medium text-20px">منصة سمات بلس هي منصة تدريب إلكترونية سعودية وطنية تهدف إلى تقديم
                    برامج
                    تعليمية وتدريبية عالية الجودة
                    تواكب
                    متطلبات سوق العمل المتسارعة. نعمل على تمكين الأفراد والمهنيين من تطوير مهاراتهم وبناء قدراتهم عبر
                    محتوى
                    تدريبي حديث، يقدمه خبراء ومتخصصون في مجالات متعددة.
                    نسعى في سمات بلس إلى أن تكون المعرفة متاحة وسهلة الوصول، وأن نساهم في بناء مجتمع معرفي قادر على
                    الابتكار
                    والمنافسة محليًا وعالميًا.</p>
            </div>
        </div>
    </header>

    <section class="section-gap">
        <div class="container">
            <h2 class="font-semibold text-32px text-primary mb-8 text-center">معتمدون لدى</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-9">
                <div class="center  px-4 shadow-[0px_3px_20.6px_0px_#3D455D1A] h-48 ">
                    <img class="object-contain w-full h-full  "
                        src="{{ asset('assets/landing_v1/img/partners/partner_1.webp') }}" alt="partner 1">
                </div>

                <div class="center px-8 border-x shadow-[0px_3px_20.6px_0px_#3D455D1A] h-48">
                    <img class="object-contain w-full h-full "
                        src="{{ asset('assets/landing_v1/img/partners/partner_2.webp') }}" alt="partner 2">
                </div>

                <div class="center px-8 border-e shadow-[0px_3px_20.6px_0px_#3D455D1A] h-48">
                    <img class="object-contain w-full h-full "
                        src="{{ asset('assets/landing_v1/img/partners/partner_3.webp') }}" alt="partner 3">
                </div>

                <div class="center px-4 shadow-[0px_3px_20.6px_0px_#3D455D1A] h-48">
                    <img class="object-contain w-full h-full "
                        src="{{ asset('assets/landing_v1/img/partners/partner_4.webp') }}" alt="partner 4">
                </div>
                <div class="center px-4 shadow-[0px_3px_20.6px_0px_#3D455D1A] h-48">
                    <img class="object-contain w-full h-full "
                        src="{{ asset('assets/landing_v1/img/partners/partner_5.webp') }}" alt="partner 5">
                </div>
                <div class="center px-4 shadow-[0px_3px_20.6px_0px_#3D455D1A] h-48">
                    <img class="object-contain w-full h-full "
                        src="{{ asset('assets/landing_v1/img/partners/partner_6.webp') }}" alt="partner 6">

                </div>
            </div>
    </section>

    <x-landing_v1::stats  titleClass="text-center" />
</main>
@endsection
