<?php

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Repository contract for users.
 */
interface UserRepositoryInterface
{
  /**
   * Find a user by id.
   *
   * @param int|string $id
   */
  public function findById(int|string $id): ?User;

  /**
   * Find a user by email.
   *
   * @param string $email
   */
  public function findByEmail(string $email): ?User;
}
