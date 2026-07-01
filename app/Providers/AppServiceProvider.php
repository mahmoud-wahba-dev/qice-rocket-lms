<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Web\LandingV1Controller;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Landing v1 views + anonymous Blade components (no PHP classes)
        \Illuminate\Support\Facades\View::addNamespace('landing_v1', resource_path('views/landing_v1'));
        Blade::anonymousComponentPath(resource_path('views/landing_v1/components'), 'landing_v1');

        View::composer('landing_v1.layouts.navbar', function ($view) {
            $paidCourseCategories = Cache::remember(
                'landing_v1.navbar_paid_categories',
                now()->addMinutes(30),
                fn () => app(LandingV1Controller::class)->getPaidCourseFilterCategories()
            );

            $view->with('paidCourseCategories', $paidCourseCategories);
        });
        // Existing validation
        Validator::extend('check_price', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^\d*\.?\d*$/', $value);
        });


        Paginator::defaultView('pagination::default');
    }
}
