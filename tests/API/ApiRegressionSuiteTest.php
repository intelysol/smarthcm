<?php

declare(strict_types=1);

namespace Tests\API;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiRegressionSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $user;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'API Contract Technologies',
            'slug' => 'api-contract-tech',
            'tenant_code' => 'API-CTR-01',
            'status' => 'active',
        ]);

        $this->company = Company::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'API Contract Global',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Claire Redfield',
            'email' => 'claire@apicontract.internal',
            'password' => bcrypt('ContractSec2026!'),
            'status' => 'active',
        ]);

        $this->employee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-CTR-01',
            'employee_code' => 'EMP-CTR-01',
            'first_name' => 'Claire',
            'last_name' => 'Redfield',
            'official_email' => 'claire@apicontract.internal',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);
    }

    /**
     * Test 1: Unauthenticated Error Envelope (401)
     */
    public function test_unauthenticated_api_response_matches_contract(): void
    {
        $response = $this->getJson('/api/v1/platform/me');

        $response->assertStatus(401);
        $response->assertHeader('X-Request-ID');
        $response->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
                'details',
            ],
            'request_id',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertSame('UNAUTHENTICATED', $response->json('error.code'));
    }

    /**
     * Test 2: Validation Error Envelope (422)
     */
    public function test_validation_error_api_response_matches_contract(): void
    {
        $response = $this->postJson('/api/v1/platform/auth/login', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $response->assertHeader('X-Request-ID');
        $response->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
                'details',
            ],
            'request_id',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertSame('VALIDATION_ERROR', $response->json('error.code'));
        $this->assertIsArray($response->json('error.details'));
    }

    /**
     * Test 3: Resource Not Found Error Envelope (404)
     */
    public function test_resource_not_found_api_response_matches_contract(): void
    {
        $nonExistentId = (string) Str::uuid();

        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->getJson("/api/me/pay/payslips/{$nonExistentId}");

        $response->assertStatus(404);
        $response->assertHeader('X-Request-ID');
        $response->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
            ],
            'request_id',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertTrue(in_array($response->json('error.code'), ['RESOURCE_NOT_FOUND', 'HTTP_404']));
    }

    /**
     * Test 4: Successful API Response Structure
     */
    public function test_successful_api_response_contract(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->getJson('/api/me/dashboard');

        $response->assertStatus(200);
        $this->assertArrayHasKey('employee', $response->json());
        $this->assertArrayHasKey('attendance', $response->json());
        $this->assertArrayHasKey('tasks', $response->json());
    }
}
