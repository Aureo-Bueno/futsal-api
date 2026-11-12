<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequestId;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequestIdMiddlewareTest extends TestCase
{
    public function test_generates_request_id_when_missing(): void
    {
        Log::shouldReceive('withContext')->once();

        $middleware = new RequestId;
        $request = Request::create('/api/health', 'GET');

        $response = $middleware->handle($request, fn (Request $req) => new Response('ok'));

        $this->assertNotEmpty($request->header('X-Request-Id'));
        $this->assertSame($request->header('X-Request-Id'), $response->headers->get('X-Request-Id'));
    }

    public function test_preserves_existing_request_id_header(): void
    {
        Log::shouldReceive('withContext')->once();

        $middleware = new RequestId;
        $request = Request::create('/api/health', 'GET');
        $request->headers->set('X-Request-Id', 'custom-req-id-123');

        $response = $middleware->handle($request, fn (Request $req) => new Response('ok'));

        $this->assertSame('custom-req-id-123', $response->headers->get('X-Request-Id'));
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
