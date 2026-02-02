<?php

namespace App\Services\Implementations;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

/**
 * Application service for users and authentication.
 */
class UserService implements UserServiceInterface
{
  public function __construct(
    private readonly UserRepositoryInterface $userRepository,
    private readonly LoggerInterface $logger
  ) {
  }

  /**
   * Attempt to authenticate a user and return it on success.
   *
   * @param array<string, mixed> $credentials
   */
  public function login(array $credentials): ?User
  {
    if (!Auth::attempt($credentials)) {
      $this->logger->warning('Login failed', ['email' => $credentials['email'] ?? null]);
      return null;
    }

    $userId = Auth::id();
    if (!$userId) {
      $this->logger->warning('Login failed: missing user id');
      return null;
    }

    $user = $this->userRepository->findById($userId);
    if ($user) {
      $user->lastLogin = now();
      $user->save();
      $this->logger->info('User logged in', ['user_id' => $user->id]);
    }
    return $user;
  }

  /**
   * Get the currently authenticated API user.
   */
  public function getAuthenticatedUser(): ?User
  {
    $userId = Auth::guard('api')->id();
    if (!$userId) {
      return null;
    }

    return $this->userRepository->findById($userId);
  }

  /**
   * Revoke the current access token for a user.
   *
   * @param User $user
   */
  public function logout(User $user): void
  {
    $accessToken = $user->token();

    DB::table('oauth_access_tokens')
      ->where('id', $accessToken->id)
      ->update(['revoked' => true]);

    $accessToken->revoke();
    $this->logger->info('User logged out', ['user_id' => $user->id]);
  }
}
