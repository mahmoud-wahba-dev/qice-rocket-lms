<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$routes = app('router')->getRoutes()->get('GET');
foreach ($routes as $route) {
    if (in_array($route->uri(), ['contact', 'instructors', 'cart'])) {
        echo $route->uri() . ' -> action: ';
        $action = $route->getAction();
        if (isset($action['controller'])) {
            echo $action['controller'] . PHP_EOL;
            try {
                $ref = new ReflectionMethod($action['uses']);
                echo "  File: " . $ref->getFileName() . ":" . $ref->getStartLine() . PHP_EOL;
            } catch (Exception $e) { }
        } else {
            echo "Closure" . PHP_EOL;
        }
    }
}
