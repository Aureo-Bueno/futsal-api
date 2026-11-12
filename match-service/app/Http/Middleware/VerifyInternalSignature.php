<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyInternalSignature
{
    public function __construct(private readonly RequestSigner $signer) {}

    public function handle(Request $request, Closure $next): Response
    {
        $headers = [
            'X-User-Id' => $request->header('X-User-Id'),
            'X-Request-Id' => $request->header('X-Request-Id'),
            'X-Correlation-Id' => $request->header('X-Correlation-Id'),
            'X-Internal-Timestamp' => $request->header('X-Internal-Timestamp'),
            'X-Signature' => $request->header('X-Signature'),
        ];

        foreach ($headers as $name => $value) {
            if ($value === null || $value === '') {
                return response()->json(['message' => "Missing {$name}"], 401);
            }
        }

        $age = abs(time() - (int) $headers['X-Internal-Timestamp']);

        if ($age > 300) {
            return response()->json(['message' => 'Expired signature'], 401);
        }

        $expected = $this->signer->sign(
            $request->method(),
            '/'.$request->path(),
            $request->getContent(),
            $headers['X-Internal-Timestamp'],
        );

        if (! hash_equals($expected, $headers['X-Signature'])) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
