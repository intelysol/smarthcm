<?php

declare(strict_types=1);

namespace Tests\Feature\UX;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class UiRouteCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Route Coverage Corp',
            'slug' => 'route-coverage',
            'tenant_code' => 'RTC-CORP',
            'status' => 'active',
        ]);

        $companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Coverage Holdings',
            'legal_name' => 'Coverage Holdings Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User with global/platform privileges
        $this->superUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Coverage Tester',
            'email' => 'tester@coverage.internal',
            'password' => bcrypt('Password123!'),
            'is_platform_admin' => true,
            'status' => 'active',
        ]);

        Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->superUser->id,
            'company_id' => $companyId,
            'employee_number' => 'COV-001',
            'employee_code' => 'COV-001',
            'first_name' => 'Coverage',
            'last_name' => 'Tester',
            'official_email' => 'tester@coverage.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYear()->toDateString(),
        ]);
    }

    /**
     * Test all primary workspace routes resolve without unhandled 500 exceptions
     */
    public function test_all_workspace_routes_resolve_with_valid_status_codes(): void
    {
        $this->actingAs($this->superUser)
            ->withSession(['tenant_uuid' => $this->tenant->id]);

        $routesToTest = [
            // Platform
            '/platform/control-center',
            '/platform/tenants',
            '/platform/billing',
            '/platform/security',
            '/platform/ai-governance',
            '/platform/settings',

            // Tenant Admin
            '/admin/dashboard',
            '/admin/settings',
            '/admin/users',
            '/admin/departments',
            '/admin/positions',
            '/admin/workflows',

            // HR
            '/hr/dashboard',

            // Manager
            '/manager/workbench',
            '/manager/members',
            '/manager/performance',
            '/manager/analytics',

            // Employee
            '/employee/home',
            '/portal/profile',
            '/portal/requests',
            '/portal/work',
            '/portal/pay',
            '/portal/growth',
            '/portal/documents',
            '/portal/services',
            '/portal/directory',

            // Executive
            '/executive/overview',
            '/executive/costs',

            // Operations
            '/operations/queues',
            '/operations/logs',
            '/operations/system-health',
        ];

        foreach ($routesToTest as $uri) {
            $response = $this->get($uri);
            $this->assertNotEquals(
                500,
                $response->getStatusCode(),
                "Route {$uri} threw an internal 500 server error."
            );
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302], true),
                "Route {$uri} returned unexpected HTTP code: {$response->getStatusCode()}"
            );
        }
    }
}
