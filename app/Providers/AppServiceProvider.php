<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Request, View;

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
        $menuUrl = Request::segment(1);
        View::share('menuUrl', $menuUrl);
    }
}
