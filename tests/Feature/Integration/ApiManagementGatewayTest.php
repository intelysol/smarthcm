<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiKey;
use App\Domains\Api\Models\ApiRateLimitPolicy;
use App\Domains\Api\Models\ApiRequestLog;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiManagementGatewayTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected ApiClient $client;
    protected string $plainApiKey;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'test-api-gateway-tenant'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'API Gateway Tenant',
                'tenant_code' => 'API-GW-01',
                'status' => 'active',
            ]
        );

        $this->client = ApiClient::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test External Payroll Provider',
            'client_type' => 'partner',
            'client_identifier' => 'client_' . Str::random(12),
            'scopes' => ['employees:read', 'payroll:sync'],
            'status' => 'active',
        ]);

        $this->plainApiKey = 'fep_' . Str::random(32);

        ApiKey::create([
            'client_id' => $this->client->id,
            'key_hash' => hash('sha256', $this->plainApiKey),
            'label' => 'Primary Production Key',
            'expires_at' => now()->addYear(),
        ]);
    }

    public function test_api_key_authentication_missing_key_fails(): void
    {
        $res = $this->getJson('/api/v1/external/ping');
        $res->assertStatus(401);
        $res->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_api_key_authentication_invalid_key_fails(): void
    {
        $res = $this->withHeaders(['X-API-Key' => 'fep_invalid_key_12345'])->getJson('/api/v1/external/ping');
        $res->assertStatus(401);
        $res->assertJsonPath('error.code', 'INVALID_API_KEY');
    }

    public function test_api_key_authentication_valid_key_succeeds_with_audit_and_rate_limit(): void
    {
        $res = $this->withHeaders([
            'X-API-Key' => $this->plainApiKey,
        ])->getJson('/api/v1/external/ping');

        $res->assertStatus(200);
        $res->assertJsonPath('authenticated', true);
        $res->assertHeader('X-RateLimit-Limit');
        $res->assertHeader('X-RateLimit-Remaining');
        $res->assertHeader('X-Correlation-ID');

        // Verify request logged to api_request_logs
        $log = ApiRequestLog::where('api_client_id', $this->client->id)->latest('id')->first();
        $this->assertNotNull($log, 'API request must be logged in api_request_logs');
        $this->assertSame(200, $log->response_status);
        $this->assertGreaterThanOrEqual(0, $log->latency_ms);
    }

    public function test_rate_limiting_enforcement_returns_429(): void
    {
        // Configure policy with 2 requests per minute
        ApiRateLimitPolicy::create([
            'api_client_id' => $this->client->id,
            'requests_per_minute' => 2,
            'burst_limit' => 2,
        ]);

        Cache::flush();

        // 1st request - 200
        $res1 = $this->withHeaders(['X-API-Key' => $this->plainApiKey])->getJson('/api/v1/external/ping');
        $res1->assertStatus(200);

        // 2nd request - 200
        $res2 = $this->withHeaders(['X-API-Key' => $this->plainApiKey])->getJson('/api/v1/external/ping');
        $res2->assertStatus(200);

        // 3rd request - 429 Too Many Requests
        $res3 = $this->withHeaders(['X-API-Key' => $this->plainApiKey])->getJson('/api/v1/external/ping');
        $res3->assertStatus(429);
        $res3->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
        $res3->assertHeader('Retry-After');
    }
}
