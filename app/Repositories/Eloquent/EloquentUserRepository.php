<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Eloquent implementation of UserRepositoryInterface.
 */
class EloquentUserRepository implements UserRepositoryInterface
{
  /**
   * Find a user by id.
   *
   * @param int|string $id
   */
  public function findById(int|string $id): ?User
  {
    return User::find($id);
  }

  /**
   * Find a user by email.
   *
   * @param string $email
   */
  public function findByEmail(string $email): ?User
  {
    return User::where('email', $email)->first();
  }
}
