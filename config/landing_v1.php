<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel header navigation (Landing V1)
    |--------------------------------------------------------------------------
    |
    | Links shown in the user panel top bar (design_1/panel/includes/header).
    | Use a named Laravel route OR a direct "link" URL. Set "order" to control position.
    | These merge with links added from Admin → Additional Pages → Top Navbar.
    |
    */
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
        [
            'title' => 'المدونة',
            'route' => 'landing.v1.blogs',
            'order' => 50,
        ],
        [
            'title' => 'تواصل معنا',
            'route' => 'landing.v1.contact',
            'order' => 60,
        ],

        // Example: add a static page by route name
        // [
        //     'title' => 'تفاصيل دورة مدفوعة',
        //     'route' => 'landing.v1.course-details-paid',
        //     'order' => 35,
        // ],

        // Example: external or custom URL
        // [
        //     'title' => 'رابط مخصص',
        //     'link' => 'https://example.com',
        //     'order' => 70,
        // ],
    ],

];
