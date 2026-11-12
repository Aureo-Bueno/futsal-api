<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addDays((int) config('auth.passport.tokens_expire_days', 15)));
        Passport::refreshTokensExpireIn(now()->addDays((int) config('auth.passport.refresh_tokens_expire_days', 30)));
        Passport::personalAccessTokensExpireIn(now()->addHours((int) config('auth.passport.personal_access_token_expire_hours', 8)));
    }
}
