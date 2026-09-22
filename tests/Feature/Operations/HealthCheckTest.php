<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_liveness_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/health/live');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertHeader('X-Request-ID');
    }

    public function test_readiness_endpoint_returns_ready(): void
    {
        $response = $this->getJson('/health/ready');

        $response->assertStatus(200)
            ->assertJsonPath('ready', true)
            ->assertJsonStructure([
                'status',
                'ready',
                'timestamp',
                'checks' => [
                    'database',
                    'storage',
                ],
            ]);
    }

    public function test_detailed_health_endpoint_returns_comprehensive_status(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'application',
                'version',
                'environment',
                'timestamp',
                'system' => [
                    'php_version',
                    'laravel_version',
                    'memory_usage_mb',
                ],
                'checks' => [
                    'database',
                    'cache',
                    'queue',
                    'storage',
                ],
            ]);
    }

    public function test_request_correlation_headers_are_propagated_and_echoed(): void
    {
        $customId = 'test-req-custom-998877';

        $response = $this->withHeaders([
            'X-Request-ID' => $customId,
        ])->getJson('/health/live');

        $response->assertStatus(200);
        $response->assertHeader('X-Request-ID', $customId);
        $response->assertHeader('X-Correlation-ID', $customId);
    }

    public function test_standardized_error_envelope_for_not_found(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'RESOURCE_NOT_FOUND')
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                    'details',
                ],
                'request_id',
            ]);

        $this->assertNotEmpty($response->json('request_id'));
    }

    public function test_system_health_web_dashboard_renders(): void
    {
        $response = $this->get('/operations/system-health');

        $response->assertStatus(200);
        $response->assertSee('System Health', false);
        $response->assertSee('Database (MySQL)', false);
    }
}
