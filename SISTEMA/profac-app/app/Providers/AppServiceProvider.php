<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Services\PosIntegration\Settings::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Console\Events\CommandStarting::class, function ($event) {
            if (is_string($event->command) && str_starts_with($event->command, 'pos-integration:')) {
                app(\App\Services\PosIntegration\Settings::class)->reload();
            }
        });
        date_default_timezone_set('America/Tegucigalpa');
        
    }
}
