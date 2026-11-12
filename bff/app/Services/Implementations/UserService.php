<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Psr\Log\LoggerInterface;

/**
 * Application service for users and authentication.
 */
class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Attempt to authenticate a user statelessly and return it on success.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function login(array $credentials): ?User
    {
        $email = (string) ($credentials['email'] ?? '');
        $password = (string) ($credentials['password'] ?? '');

        $user = $email !== '' ? $this->userRepository->findByEmail($email) : null;
        if (! $user || ! Hash::check($password, $user->password)) {
            $this->logger->warning('Login failed', ['email' => $credentials['email'] ?? null]);

            return null;
        }

        $user->lastLogin = now();
        $user->save();
        $this->logger->info('User logged in', ['user_id' => $user->id]);

        return $user;
    }

    /**
     * Get the currently authenticated API user.
     */
    public function getAuthenticatedUser(): ?User
    {
        $userId = Auth::guard('api')->id();
        if (! $userId) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    /**
     * Revoke the current access token for a user.
     */
    public function logout(User $user): void
    {
        $accessToken = $user->token();

        if ($accessToken) {
            $tokenId = $accessToken->id ?? null;
            if ($tokenId) {
                DB::table('oauth_access_tokens')
                    ->where('id', $tokenId)
                    ->update(['revoked' => true]);
            }

            if (method_exists($accessToken, 'revoke')) {
                $accessToken->revoke();
            }
        }

        $this->logger->info('User logged out', ['user_id' => $user->id]);
    }
}
