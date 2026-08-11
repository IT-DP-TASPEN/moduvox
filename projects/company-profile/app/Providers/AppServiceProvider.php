<?php

namespace App\Providers;

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
        require_once app_path('Helpers/setting.php');
        require_once app_path('Helpers/format.php');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!app()->runningInConsole()) {
            \Illuminate\Support\Facades\View::share('socials', \App\Models\SocialMedia::where('is_active', true)->orderBy('order')->get());
        }
    }
}
