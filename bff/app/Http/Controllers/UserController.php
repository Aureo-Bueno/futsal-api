<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    #[OA\Post(
        path: '/api/login',
        summary: 'Login',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function loginUser(AuthLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = $this->userService->login($credentials);
        if (! $user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $token = $user->createToken('example')->accessToken;

        return response()->json(['status' => 200, 'token' => $token, 'token_type' => 'Bearer'], 200);
    }

    #[OA\Post(
        path: '/api/user',
        summary: 'Get authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function getUser(): JsonResponse
    {
        $user = $this->userService->getAuthenticatedUser();
        if ($user) {
            return (new UserResource($user))
                ->additional(['status' => 200])
                ->response()
                ->setStatusCode(200);
        }

        return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function userLogout(): JsonResponse
    {
        $user = $this->userService->getAuthenticatedUser();
        if ($user) {
            $this->userService->logout($user);

            return response()->json(['status' => 200, 'message' => 'User logged out'], 200);
        }

        return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
    }
}
