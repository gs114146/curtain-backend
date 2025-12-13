<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

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
        // Force HTTPS for URL generation when behind ngrok/valet proxy serving HTTPS
        // Conditions: production-like env over ngrok OR forwarded proto says https
        try {
            $host = Request::getHost();
            $isNgrok = str_contains($host, 'ngrok');
            $forwardedProto = Request::header('X-Forwarded-Proto');

            if ($isNgrok || ($forwardedProto && strtolower($forwardedProto) === 'https')) {
                URL::forceScheme('https');
            }
        } catch (\Throwable $e) {
            // noop - avoid crashing boot in CLI/testing
        }
    }
}
