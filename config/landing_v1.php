<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel header navigation (Landing V1)
    |--------------------------------------------------------------------------
    |
    | Rendered in: resources/views/design_1/panel/includes/header.blade.php
    | Loaded via:  app/Http/Middleware/PanelAuthenticate.php → getPanelHeaderNavLinks()
    |
    | Edit links here (title + route). Set merge_admin_navbar_links to true only if you
    | also want links from Admin → Additional Pages → Top Navbar (old LMS links).
    */
    'merge_admin_navbar_links' => false,

    'panel_nav_links' => [
        [
            'title' => 'الرئيسية',
            'route' => 'landing.v1.index',
            'order' => 10,
        ],
        [
            'title' => 'دورات مجانية',
            'route' => 'landing.v1.workshops',
            'order' => 20,
        ],
        [
            'title' => 'الدورات المعتمدة',
            'route' => 'landing.v1.courses-paid',
            'order' => 30,
        ],
        [
            'title' => 'المدربين',
            'route' => 'landing.v1.instructors',
            'order' => 40,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Homepage cache (Landing V1)
    |--------------------------------------------------------------------------
    |
    | Caches homepage DB queries to reduce TTFB. Set to 0 to disable.
    | Clear after content changes: php artisan cache:forget landing_v1.homepage.ar
    */
    'homepage_cache_minutes' => 10,

    /*
    |--------------------------------------------------------------------------
    | Footer social links (Landing V1)
    |--------------------------------------------------------------------------
    |
    | Only entries with a non-empty url are shown in the footer.
    | URLs below match QIEC official profiles (qiec.sa / @qxtc_qiec).
    | Edit here if you add or change social accounts.
    */
    'social_links' => [
        [
            'icon' => 'telegram',
            'label' => 'Telegram',
            'url' => 'https://t.me/qxtc_qiec',
        ],
        [
            'icon' => 'whatsapp',
            'label' => 'WhatsApp',
            'url' => 'https://wa.me/966562730122',
        ],
        [
            'icon' => 'instagram',
            'label' => 'Instagram',
            'url' => 'https://www.instagram.com/qxtc_qiec/',
        ],
        [
            'icon' => 'x',
            'label' => 'X',
            'url' => 'https://x.com/qxtc_qiec',
        ],
    ],

];
