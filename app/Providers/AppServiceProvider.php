<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BladeUI\Heroicons\HeroiconsServiceProvider;
use BladeUI\Icons\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // app(Factory::class)->add('heroicons-outline', [
        //     'path' => resource_path('views/vendor/heroicons/outline'),
        //     'prefix' => 'heroicons-outline',
        // ]);
    }
}
