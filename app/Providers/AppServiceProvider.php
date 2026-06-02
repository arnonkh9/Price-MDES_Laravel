<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Fix Livewire update URI for subdirectory deployment (XAMPP).
        // Without this, data-update-uri="/livewire/update" resolves to the
        // server root instead of /price-MDES_V1/public/livewire/update.
        URL::forceRootUrl(config('app.url'));
    }
}
