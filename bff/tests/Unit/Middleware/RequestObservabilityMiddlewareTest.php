<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SecurityHeaders;
use Futsal\Observability\Http\RequestObservabilityMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequestObservabilityMiddlewareTest extends TestCase
{
    public function test_generates_request_and_correlation_ids_when_missing(): void
    {
        Log::shouldReceive('withContext')->once();
        Log::shouldReceive('info')->once();

        $middleware = new RequestObservabilityMiddleware;
        $request = Request::create('/api/health', 'GET');

        $response = $middleware->handle($request, fn (Request $req) => new Response('ok'));

        $requestId = $request->header('X-Request-Id');

        $this->assertNotEmpty($requestId);
        $this->assertSame($requestId, $request->header('X-Correlation-Id'));
        $this->assertSame($requestId, $response->headers->get('X-Request-Id'));
        $this->assertSame($requestId, $response->headers->get('X-Correlation-Id'));
    }

    public function test_preserves_existing_request_and_correlation_headers(): void
    {
        Log::shouldReceive('withContext')->once();
        Log::shouldReceive('info')->once();

        $middleware = new RequestObservabilityMiddleware;
        $request = Request::create('/api/health', 'GET');
        $request->headers->set('X-Request-Id', 'custom-req-id-123');
        $request->headers->set('X-Correlation-Id', 'custom-corr-id-456');

        $response = $middleware->handle($request, fn (Request $req) => new Response('ok'));

        $this->assertSame('custom-req-id-123', $response->headers->get('X-Request-Id'));
        $this->assertSame('custom-corr-id-456', $response->headers->get('X-Correlation-Id'));
    }

    public function test_security_headers_middleware_attaches_expected_headers(): void
    {
        $middleware = new SecurityHeaders;
        $request = Request::create('/api/health', 'GET');

        $response = $middleware->handle($request, fn (Request $req) => new Response('ok'));

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
        $this->assertSame('geolocation=(), microphone=(), camera=()', $response->headers->get('Permissions-Policy'));
    }
}
