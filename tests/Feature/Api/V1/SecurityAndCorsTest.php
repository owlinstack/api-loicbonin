<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SecurityAndCorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cors_headers_are_present_for_allowed_origins(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->json('GET', '/api/v1/profile');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');

        $responseProd = $this->withHeaders([
            'Origin' => 'https://loicbonin.com',
        ])->json('GET', '/api/v1/profile');

        $responseProd->assertHeader('Access-Control-Allow-Origin', 'https://loicbonin.com');
    }

    public function test_cors_headers_are_restricted_for_unauthorized_origins(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://unauthorized-domain.com',
        ])->json('GET', '/api/v1/profile');

        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_rate_limiting_is_applied_to_api_routes(): void
    {
        // Make 60 requests (which should succeed)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/v1/profile');
            $response->assertStatus(200);
        }

        // The 61st request should be throttled (returns 429)
        $response = $this->getJson('/api/v1/profile');
        $response->assertStatus(429);
    }

    public function test_cors_preflight_response_headers(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Content-Type',
        ])->json('OPTIONS', '/api/v1/profile');

        $response->assertStatus(204)
            ->assertHeader('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS')
            ->assertHeader('Access-Control-Max-Age', '86400');
    }
}
