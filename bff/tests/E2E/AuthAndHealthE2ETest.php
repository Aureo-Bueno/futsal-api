<?php

namespace Tests\E2E;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class AuthAndHealthE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Test Personal Access Client',
            'users'
        );
    }

    public function test_health_endpoint_returns_ok_and_request_id_header(): void
    {
        $response = $this->getJson('/api/health', ['X-Request-Id' => 'e2e-health-check']);

        $response->assertStatus(200)
            ->assertHeader('X-Request-Id', 'e2e-health-check')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()')
            ->assertJson([
                'status' => 'ok',
                'database' => 'ok',
            ])
            ->assertJsonStructure(['status', 'database', 'timestamp']);
    }

    public function test_full_authentication_flow_login_me_and_logout(): void
    {
        $user = User::factory()->create([
            'name' => 'Aureo Admin',
            'email' => 'admin@futsal.com',
            'password' => Hash::make('secret123'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@futsal.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['status', 'token', 'token_type'])
            ->assertJson(['status' => 200, 'token_type' => 'Bearer']);

        $token = $loginResponse->json('token');
        $this->assertNotEmpty($token);
        $this->assertNotNull($user->fresh()->last_login);

        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/user');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'admin@futsal.com')
            ->assertJsonPath('status', 200);

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['status' => 200, 'message' => 'User logged out']);
    }

    public function test_login_fails_with_invalid_credentials_or_invalid_payload(): void
    {
        User::factory()->create([
            'email' => 'admin@futsal.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@futsal.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)
            ->assertJson(['status' => 401, 'message' => 'Unauthorized']);

        $this->postJson('/api/login', [
            'email' => 'not-an-email',
        ], ['X-Request-Id' => 'req-422-check'])->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonPath('request_id', 'req-422-check')
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_protected_endpoints_reject_unauthenticated_requests(): void
    {
        $this->postJson('/api/user', [], ['X-Request-Id' => 'req-401-check'])
            ->assertStatus(401)
            ->assertJsonPath('status', 401)
            ->assertJsonPath('request_id', 'req-401-check');
        $this->postJson('/api/logout')->assertStatus(401);
        $this->getJson('/api/team')->assertStatus(401);
        $this->getJson('/api/player')->assertStatus(401);
        $this->getJson('/api/teamMatch')->assertStatus(401);
        $this->getJson('/api/teamClassification')->assertStatus(401);
    }

    public function test_login_rate_limiter_throttles_by_email_and_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'brute@futsal.com',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        // 6th attempt for the same email+IP is throttled (429)
        $this->postJson('/api/login', [
            'email' => 'brute@futsal.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);

        // Different email from the same IP is not throttled
        $this->postJson('/api/login', [
            'email' => 'other@futsal.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }
}
