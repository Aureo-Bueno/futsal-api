<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\InternalClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class InternalClientTest extends TestCase
{
    public function test_signs_internal_request_with_identity_headers(): void
    {
        Http::fake(['*team-service:8001/*' => Http::response(['id' => 't1'], 200)]);

        $user = new class
        {
            public string $id = '00000000-0000-0000-0000-000000000001';
        };

        $response = $this->app->make(InternalClient::class)->get('team', '/teams', [], $user);

        $this->assertTrue($response->successful());
        $this->assertSame('t1', $response->json('id'));

        Http::assertSent(function ($request): bool {
            return $request->hasHeader('X-User-Id')
                && $request->hasHeader('X-Request-Id')
                && $request->hasHeader('X-Correlation-Id')
                && $request->hasHeader('X-Internal-Timestamp')
                && $request->hasHeader('X-Signature');
        });
    }
}
