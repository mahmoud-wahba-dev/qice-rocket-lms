<!doctype html>
<html data-theme="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'مشاهدة الدورة' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

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

    @vite([
        'resources/css/landing_v1.css',
        'resources/js/landing_v1.js',
        'resources/css/panel_v1/student.css',
        'resources/js/panel_v1/student.js',
    ])
</head>

<body class="bg-fa">
    <div id="landing-v1-app" class="min-h-screen panel-v1-student panel-v1-course-player">
        @include('panel_v1.student.course-player.components.navbar')

        <div class="container-fluid px-0 lg:px-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[calc(100vh-4.5rem)]">
                {{-- Sidebar: right on RTL desktop; drawer on mobile --}}
                <aside id="course-player-sidebar"
                    class="course-player-sidebar lg:col-span-3 lg:relative fixed inset-y-0 start-0 z-40 w-[min(100%,20rem)] lg:w-auto
                           bg-white border-e border-d9 pt-[4.5rem] lg:pt-0
                           -translate-x-full rtl:translate-x-full lg:translate-x-0 rtl:lg:translate-x-0
                           transition-transform duration-300 overflow-y-auto
                           data-[open=true]:translate-x-0 rtl:data-[open=true]:translate-x-0">
                    @include('panel_v1.student.course-player.components.sidebar')
                </aside>

                <div id="course-player-sidebar-backdrop"
                    class="fixed inset-0 z-30 bg-black/40 lg:hidden hidden"
                    data-course-sidebar-close aria-hidden="true"></div>

                <main class="lg:col-span-9 bg-fa min-w-0 mt-0">
                    <div class="px-4 sm:px-6 lg:px-10 py-6 lg:py-8">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>

        @include('panel_v1.student.course-player.components.support-modal')
    </div>
    @stack('scripts')
</body>

</html>
