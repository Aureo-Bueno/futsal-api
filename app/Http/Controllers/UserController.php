<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;

class UserController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function loginUser(Request $request): Response
  {
    $input = $request->all();

    Auth::attempt($input);
    $user = Auth::user();
    $token = $user->createToken('example')->accessToken;
    return Response(['status' => 200, 'token' => $token], 200);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function getUser(): Response
  {
    if (Auth::guard('api')->check()) {
      $user = Auth::guard('api')->user();
      return Response(['status' => 200, 'user' => $user], 200);
    }

    return Response(['status' => 401, 'message' => 'Unauthorized'], 401);
  }

  /**
   * Display the specified resource.
   */
  public function userLogout(): Response
  {
    if (Auth::guard('api')->check()) {
      $accessToken = Auth::guard('api')->user()->token();

      \DB::table('oauth_access_tokens')
        ->where('id', $accessToken->id)
        ->update(['revoked' => true]);

      $accessToken->revoke();
      return Response(['status' => 200, 'message' => 'User logged out'], 200);
    }

    return Response(['status' => 401, 'message' => 'Unauthorized'], 401);
  }
}
