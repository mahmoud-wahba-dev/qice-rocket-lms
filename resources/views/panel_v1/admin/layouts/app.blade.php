<!doctype html>
<html data-theme="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

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

    @vite([
        'resources/css/landing_v1.css',
        'resources/js/landing_v1.js',
        'resources/css/panel_v1/admin.css',
        'resources/js/panel_v1/admin.js',
    ])
    <script>
        (function () {
            try {
                if (localStorage.getItem('panel_v1_admin_sidebar') === 'collapsed'
                    && window.matchMedia('(min-width: 1024px)').matches) {
                    document.documentElement.dataset.adminSidebarPref = 'collapsed';
                }
            } catch (e) {}
        })();
    </script>
</head>

<body>
    {{-- Admin dashboard shell: fixed sidebar + header/main padding synced via CSS (parity with instructor) --}}
    <div id="landing-v1-app" class="bg-[#F9FAF5] flex min-h-screen flex-col panel-v1-admin"
        data-admin-sidebar="expanded" data-admin-dashboard="{{ $adminDashboard ?? 'education' }}">
        <script>
            (function () {
                var app = document.getElementById('landing-v1-app');
                if (app && document.documentElement.dataset.adminSidebarPref === 'collapsed') {
                    app.setAttribute('data-admin-sidebar', 'collapsed');
                }
            })();
        </script>

        {{-- ---------- HEADER ---------- --}}
        <div class="admin-shell-header bg-white border-[#E8E8E8] sticky top-0 z-50 flex border-b">
            <div class="mx-auto w-full">
                @include('panel_v1.admin.components.header')
            </div>
        </div>

        {{-- ---------- SIDEBAR ---------- --}}
        @include('panel_v1.admin.components.sidebar')
        <div id="admin-sidebar-backdrop"
            class="fixed inset-0 z-[55] bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300 ease-out lg:hidden"
            data-admin-sidebar-close
            aria-hidden="true"></div>

        {{-- ---------- MAIN ---------- --}}
        <div class="admin-shell-main flex grow flex-col">
            <main class="mx-auto mt-0 w-full flex-1 space-y-6 p-4 sm:p-6 lg:p-8 bg-[#FAFAF4]">
                @include('components.v1.flash')
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
