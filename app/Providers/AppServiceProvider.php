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
        // Behind Cloudflare Flexible / reverse proxy, force HTTPS so redirects
        // never send the Android WebView to cleartext http:// (app crash / blocked).
        $appUrl = (string) config('app.url');
        if (str_starts_with($appUrl, 'https://') || $this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
