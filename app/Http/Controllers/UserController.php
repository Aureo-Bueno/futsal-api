<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use App\Services\Contracts\UserServiceInterface;

/**
 * Handle authentication endpoints.
 */
class UserController extends Controller
{
  public function __construct(
    private readonly UserServiceInterface $userService
  ) {
  }

  /**
   * Authenticate a user and return an access token.
   *
   * @OA\Post(
   *   path="/api/login",
   *   tags={"Auth"},
   *   summary="Login",
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\JsonContent(
   *       required={"email","password"},
   *       @OA\Property(property="email", type="string", format="email"),
   *       @OA\Property(property="password", type="string")
   *     )
   *   ),
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized"),
   *   @OA\Response(response=422, description="Validation error")
   * )
   */
  public function loginUser(AuthLoginRequest $request): JsonResponse
  {
    $credentials = $request->validated();

    $user = $this->userService->login($credentials);
    if (!$user) {
      return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    $token = $user->createToken('example')->accessToken;
    return response()->json(['status' => 200, 'token' => $token, 'token_type' => 'Bearer'], 200);
  }

  /**
   * Get the authenticated user.
   *
   * @OA\Post(
   *   path="/api/user",
   *   tags={"Auth"},
   *   summary="Get authenticated user",
   *   security={{"bearerAuth":{}}},
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
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

  /**
   * Revoke the current access token.
   *
   * @OA\Post(
   *   path="/api/logout",
   *   tags={"Auth"},
   *   summary="Logout",
   *   security={{"bearerAuth":{}}},
   *   @OA\Response(response=200, description="OK"),
   *   @OA\Response(response=401, description="Unauthorized")
   * )
   */
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
