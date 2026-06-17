<!doctype html>
<html data-theme="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'Siemat Plus' }}</title>
    <meta name="description" content="{{ $pageDescription ?? '' }}">
    <meta name="robots" content="{{ $pageRobot ?? 'index,follow' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/landing_v1.css', 'resources/js/landing_v1.js'])
</head>
<body>
    <div id="landing-v1-app" class="min-h-screen">
        @php($landingImg = asset('assets/landing_v1/img'))
        @include('landing_v1.layouts.navbar')
           @yield('content')
        @include('landing_v1.layouts.footer')
    </div>
    @stack('scripts')
</body>
</html>
