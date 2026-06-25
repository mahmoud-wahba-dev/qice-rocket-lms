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

];
