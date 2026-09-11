<!doctype html>
<html data-theme="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'لوحة المنظمة' }}{{ !empty($generalSettings['site_name']) ? (' |
        '.$generalSettings['site_name']) : '' }}</title>

    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap">
    </noscript>

    @vite([
    'resources/css/landing_v1.css',
    'resources/js/landing_v1.js',
    'resources/css/panel_v1/instructor.css',
    'resources/js/panel_v1/instructor.js',
    ])
</head>

<body>
    <div id="landing-v1-app" class="bg-[#F9FAF5] flex min-h-screen flex-col panel-v1-instructor">

        {{-- ---------- HEADER ---------- --}}
        <div class="bg-white border-[#E8E8E8] sticky top-0 z-50 flex border-b lg:ps-[280px]">
            <div class="mx-auto w-full">
                @include('panel_v1.instructor.components.header')
            </div>
        </div>

        {{-- ---------- SIDEBAR ---------- --}}
        @include('panel_v1.organization.components.sidebar')

        {{-- ---------- MAIN ---------- --}}
        <div class="flex grow flex-col lg:ps-[280px]">
            <main class="mx-auto mt-0 w-full flex-1 space-y-6 p-4 sm:p-6 lg:p-8 bg-[#FAFAF4]">
                @include('components.v1.flash')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
