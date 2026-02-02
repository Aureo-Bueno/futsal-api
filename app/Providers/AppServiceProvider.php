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
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->bind(PlayersRepositoryInterface::class, EloquentPlayersRepository::class);
    $this->app->bind(TeamRepositoryInterface::class, EloquentTeamRepository::class);
    $this->app->bind(TeamMatchRepositoryInterface::class, EloquentTeamMatchRepository::class);
    $this->app->bind(TeamClassificationRepositoryInterface::class, EloquentTeamClassificationRepository::class);
    $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

    $this->app->bind(PlayersServiceInterface::class, PlayersService::class);
    $this->app->bind(TeamServiceInterface::class, TeamService::class);
    $this->app->bind(TeamMatchServiceInterface::class, TeamMatchService::class);
    $this->app->bind(TeamClassificationServiceInterface::class, TeamClassificationService::class);
    $this->app->bind(UserServiceInterface::class, UserService::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
  }
}
