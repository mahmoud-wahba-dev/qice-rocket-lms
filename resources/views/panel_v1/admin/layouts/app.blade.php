<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'لوحة الإدارة' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

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
    @vite(['resources/css/panel_v1/admin.css', 'resources/js/panel_v1/admin.js'])
</head>

<body class="panel-v1-admin">
    <div id="panel-v1-admin-app" class="panel-v1-app panel-v1-app--sidebar">
        @include('panel_v1.admin.layouts.sidebar')
        <div class="panel-v1-workspace">
            @include('panel_v1.admin.layouts.header')
            <main class="panel-v1-main panel-v1-main--panel">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
