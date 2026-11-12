<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Implementations\UserService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private UserRepositoryInterface $repository;

    private LoggerInterface $logger;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new UserService($this->repository, $this->logger);
    }

    public function test_login_returns_null_and_logs_warning_when_credentials_invalid(): void
    {
        $this->repository->shouldReceive('findByEmail')
            ->once()
            ->with('wrong@example.com')
            ->andReturnNull();

        $this->logger->shouldReceive('warning')
            ->once()
            ->with('Login failed', ['email' => 'wrong@example.com']);

        $result = $this->service->login(['email' => 'wrong@example.com', 'password' => 'bad']);

        $this->assertNull($result);
    }

    public function test_get_authenticated_user_returns_null_when_unauthenticated(): void
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('id')->once()->andReturnNull();
        Auth::shouldReceive('guard')->once()->with('api')->andReturn($guard);

        $this->assertNull($this->service->getAuthenticatedUser());
    }

    public function test_get_authenticated_user_returns_user_when_authenticated(): void
    {
        $user = new User(['name' => 'Admin', 'email' => 'admin@admin.com']);
        $user->id = 42;

        $guard = Mockery::mock();
        $guard->shouldReceive('id')->once()->andReturn(42);
        Auth::shouldReceive('guard')->once()->with('api')->andReturn($guard);
        $this->repository->shouldReceive('findById')->once()->with(42)->andReturn($user);

        $this->assertSame($user, $this->service->getAuthenticatedUser());
    }
}
