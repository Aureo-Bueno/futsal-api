<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestId
{
  /**
   * Attach a request id and log context for API requests.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $requestId = $request->header('X-Request-Id') ?: (string) Str::uuid();

    $request->headers->set('X-Request-Id', $requestId);
    Log::withContext([
      'request_id' => $requestId,
      'method' => $request->getMethod(),
      'path' => $request->path(),
      'ip' => $request->ip(),
    ]);

    $response = $next($request);
    $response->headers->set('X-Request-Id', $requestId);

    return $response;
  }
}
