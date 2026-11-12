<?php

namespace App\Providers;

use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RequestSigner::class, fn () => new RequestSigner(
            (string) env('INTERNAL_HMAC_SECRET', 'local-secret'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
