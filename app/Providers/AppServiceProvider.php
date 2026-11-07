<?php

namespace App\Providers;

use App\Repositories\Contracts\PlayersRepositoryInterface;
use App\Repositories\Contracts\TeamClassificationRepositoryInterface;
use App\Repositories\Contracts\TeamMatchRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentPlayersRepository;
use App\Repositories\Eloquent\EloquentTeamClassificationRepository;
use App\Repositories\Eloquent\EloquentTeamMatchRepository;
use App\Repositories\Eloquent\EloquentTeamRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Contracts\PlayersServiceInterface;
use App\Services\Contracts\TeamClassificationServiceInterface;
use App\Services\Contracts\TeamMatchServiceInterface;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\Implementations\PlayersService;
use App\Services\Implementations\TeamClassificationService;
use App\Services\Implementations\TeamMatchService;
use App\Services\Implementations\TeamService;
use App\Services\Implementations\UserService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlayersRepositoryInterface::class, EloquentPlayersRepository::class);
        $this->app->singleton(TeamRepositoryInterface::class, EloquentTeamRepository::class);
        $this->app->singleton(TeamMatchRepositoryInterface::class, EloquentTeamMatchRepository::class);
        $this->app->singleton(TeamClassificationRepositoryInterface::class, EloquentTeamClassificationRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->singleton(PlayersServiceInterface::class, PlayersService::class);
        $this->app->singleton(TeamServiceInterface::class, TeamService::class);
        $this->app->singleton(TeamMatchServiceInterface::class, TeamMatchService::class);
        $this->app->singleton(TeamClassificationServiceInterface::class, TeamClassificationService::class);
        $this->app->singleton(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        $this->loadMigrationsFrom(base_path('vendor/laravel/passport/database/migrations'));
    }
}
