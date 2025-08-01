<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Принудительно устанавливаем правильный URL
        URL::forceRootUrl('https://b24.perspektiva-crimea.ru');
        URL::forceScheme('https');
    }
}
