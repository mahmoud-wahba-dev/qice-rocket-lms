<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use App\Services\LandingV1Cache;
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

        // Landing v1 views + anonymous Blade components (no PHP classes)
        \Illuminate\Support\Facades\View::addNamespace('landing_v1', resource_path('views/landing_v1'));
        Blade::anonymousComponentPath(resource_path('views/landing_v1/components'), 'landing_v1');
        // Existing validation
        Validator::extend('check_price', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^\d*\.?\d*$/', $value);
        });


        Paginator::defaultView('pagination::default');

        View::composer('landing_v1.layouts.footer', function ($view) {
            if ($view->offsetExists('footerCategories')) {
                return;
            }

            $footerCategories = LandingV1Cache::remember(
                LandingV1Cache::key('footer_categories'),
                fn () => Category::whereNull('parent_id')
                    ->where('enable', true)
                    ->orderBy('order')
                    ->limit(6)
                    ->get()
            );

            $view->with('footerCategories', $footerCategories);
        });
    }
}
