<?php

namespace App\Providers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Contracts\UserServiceInterface;
use App\Services\Implementations\UserService;
use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->singleton(UserServiceInterface::class, UserService::class);

        $this->app->singleton(RequestSigner::class, fn () => new RequestSigner(
            (string) config('services.internal.secret'),
        ));
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        $this->loadMigrationsFrom(base_path('vendor/laravel/passport/database/migrations'));
    }
}
