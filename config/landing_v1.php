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
    | WhatsApp Contact (Landing V1)
    |--------------------------------------------------------------------------
    */
    'whatsapp_url' => 'https://api.whatsapp.com/send/?phone=966503976186&text&type=phone_number&app_absent=0',
    'whatsapp_number' => '+966 50 397 6186',
    'whatsapp_phone' => '966503976186',

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
            'icon' => 'facebook',
            'label' => 'Facebook',
            'url' => 'https://www.facebook.com/people/%D9%85%D8%B1%D9%83%D8%B2-%D8%A7%D9%84%D8%AC%D9%88%D8%AF%D8%A9-%D9%88%D8%A7%D9%84%D8%AA%D9%85%D9%8A%D8%B2-%D9%84%D9%84%D8%AA%D8%AF%D8%B1%D9%8A%D8%A8/61573579601951/?mibextid=wwXIfr',
        ],
        [
            'icon' => 'tiktok',
            'label' => 'TikTok',
            'url' => 'https://www.tiktok.com/@quality.excellence',
        ],
        [
            'icon' => 'instagram',
            'label' => 'Instagram',
            'url' => 'https://www.instagram.com/quality.excellence',
        ],
        [
            'icon' => 'linkedin',
            'label' => 'LinkedIn',
            'url' => 'https://www.linkedin.com/in/%D9%85%D8%B1%D9%83%D8%B2-%D8%A7%D9%84%D8%AC%D9%88%D8%AF%D8%A9-%D9%88%D8%A7%D9%84%D8%AA%D9%85%D9%8A%D8%B2-%D9%84%D9%84%D8%AA%D8%AF%D8%B1%D9%8A%D8%A8-954161354/',
        ],
        [
            'icon' => 'whatsapp',
            'label' => 'WhatsApp',
            'url' => 'https://api.whatsapp.com/send/?phone=966503976186&text&type=phone_number&app_absent=0',
        ],
    ],

];
