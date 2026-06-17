<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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

        // Register the landing_v1 view namespace for Blade components
        \Illuminate\Support\Facades\View::addNamespace('landing_v1', resource_path('views/landing_v1'));
        \Illuminate\Support\Facades\Blade::componentNamespace('App\\View\\Components\\LandingV1', 'landing_v1');
        // Existing validation
        Validator::extend('check_price', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^\d*\.?\d*$/', $value);
        });


        Paginator::defaultView('pagination::default');
    }
}
