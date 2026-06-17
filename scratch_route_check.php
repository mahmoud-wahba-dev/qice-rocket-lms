<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$routes = app('router')->getRoutes()->get('GET');
foreach ($routes as $route) {
    if (in_array($route->uri(), ['contact', 'instructors', 'cart', 'about'])) {
        echo $route->uri() . ' -> ' . $route->getName() . PHP_EOL;
    }
}
