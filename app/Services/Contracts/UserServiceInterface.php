<?php

namespace App\Services\Contracts;

use App\Models\User;

/**
 * Service contract for users and authentication.
 */
interface UserServiceInterface
{
  /**
   * Attempt to authenticate a user and return it on success.
   *
   * @param array<string, mixed> $credentials
   */
  public function login(array $credentials): ?User;

  /**
   * Get the currently authenticated API user.
   */
  public function getAuthenticatedUser(): ?User;

  /**
   * Revoke the current access token for a user.
   *
   * @param User $user
   */
  public function logout(User $user): void;
}
