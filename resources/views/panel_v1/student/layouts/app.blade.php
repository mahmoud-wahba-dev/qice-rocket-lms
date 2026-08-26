<!doctype html>
<html data-theme="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'لوحة المتدرب' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap">
    </noscript>
    {{-- Same landing chrome assets so copied navbar/footer styles & JS work --}}
    @vite([
        'resources/css/landing_v1.css',
        'resources/js/landing_v1.js',
        'resources/css/panel_v1/student.css',
        'resources/js/panel_v1/student.js',
    ])
</head>

<body>
    {{-- #landing-v1-app required: Tailwind/FlyonUI utilities are scoped to this id --}}
    <div id="landing-v1-app" class="min-h-screen panel-v1-student">
        @php($landingImg = asset('assets/landing_v1/img'))
        @include('panel_v1.student.layouts.navbar')
        @yield('content')
        @include('landing_v1.components.prefooter-cta')
        @include('panel_v1.student.layouts.footer')
    </div>
    @stack('scripts')
</body>

</html>
