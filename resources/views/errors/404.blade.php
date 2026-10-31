@extends('landing_v1.layouts.app')

@php
    $statusCode = 404;
    $errorSettings = get404ErrorPageSettings();
    $landingImg = asset('assets/landing_v1/img');
    $title = $errorSettings['title'] ?? 'الصفحة غير موجودة';
    $description = strip_tags($errorSettings['description'] ?? 'الرابط الذي حاولت فتحه غير صحيح أو لم يعد متاحاً.');
    $heroSrc = $landingImg . '/errors/404.jpg';
    $panelHome = auth()->check() ? panelV1HomeUrl(auth()->user()) : null;
    $landingHome = route('landing.v1.index');
    $pageTitle = $title;
@endphp

@section('content')
<main class="relative min-h-[70vh] overflow-hidden bg-secondary pt-28 pb-20">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-24 -start-24 size-[28rem] rounded-full bg-primary/[0.06] blur-3xl"></div>
        <div class="absolute -bottom-32 -end-20 size-[24rem] rounded-full bg-gold/30 blur-3xl"></div>
    </div>

    <div class="container relative z-10 mx-auto max-w-xl px-4">
        <section class="mx-auto overflow-hidden rounded-[24px] border border-primary/5 bg-white/90 px-6 py-0 mt-0 text-center shadow-[0_24px_60px_-28px_rgba(15,76,69,0.35)] backdrop-blur-sm sm:px-10 sm:py-14">
            <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-12px font-semibold text-primary">
                <span class="size-1.5 rounded-full bg-current opacity-70"></span>
                خطأ 404
            </div>

            <div class="relative mx-auto mb-8 w-full max-w-[260px]">
                <img src="{{ $heroSrc }}" alt="{{ $title }}" class="mx-auto aspect-square w-full rounded-[20px] object-cover shadow-sm" width="260" height="260">
            </div>

            <h1 class="font-bold text-28px leading-tight text-primary sm:text-32px">{{ $title }}</h1>
            <p class="mx-auto mt-3 max-w-md font-medium text-15px leading-7 text-primary/55">{{ $description }}</p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                @if(!empty($panelHome))
                    <a href="{{ $panelHome }}" class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] bg-primary px-6 font-bold text-15px text-white transition hover:bg-primary/90">{{ trans('public.my_panel') }}</a>
                    <a href="{{ $landingHome }}" class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] border border-primary/15 bg-f7 px-6 font-bold text-15px text-primary transition hover:bg-white">الرئيسية</a>
                @else
                    <a href="{{ $landingHome }}" class="inline-flex h-12 min-w-[180px] items-center justify-center rounded-[12px] bg-primary px-6 font-bold text-15px text-white transition hover:bg-primary/90">العودة للرئيسية</a>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
