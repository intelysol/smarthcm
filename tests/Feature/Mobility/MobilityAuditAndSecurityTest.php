<?php

namespace Tests\Feature\Mobility;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityProgram;
use App\Domains\Mobility\Models\MobilityRequest;
use App\Domains\Mobility\Services\MobilityAssignmentService;
use App\Domains\Mobility\Services\MobilityRequestService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilityAuditAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobility_lifecycle_actions_are_audited_with_cryptographic_hashes(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-6001',
            'employee_number' => 'EMP-6001',
            'first_name' => 'Robert',
            'last_name' => 'Langdon',
            'official_email' => 'robert@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $requestService = app(MobilityRequestService::class);
        $assignmentService = app(MobilityAssignmentService::class);
        $auditService = app(AuditService::class);

        // 1. Create Request
        $request = $requestService->createRequest([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'mobility_type' => 'international_assignment',
            'home_company_id' => $company->id,
            'home_country' => 'United States',
            'host_company_id' => $company->id,
            'host_country' => 'Italy',
            'proposed_start_date' => '2026-11-01',
            'proposed_end_date' => '2027-10-31',
            'duration_months' => 12,
            'business_justification' => 'Historical archives project leadership',
        ], $user);

        Carbon::setTestNow('2026-09-01 11:00:00');

        // 2. Submit Request
        $requestService->submitRequest($request, $user);

        Carbon::setTestNow('2026-09-01 12:00:00');

        // 3. Approve Request
        $requestService->approveRequest($request, $user);

        Carbon::setTestNow('2026-09-01 13:00:00');

        // 4. Create Assignment
        $assignment = $assignmentService->createFromRequest($request, $user);

        Carbon::setTestNow('2026-09-01 14:00:00');

        // 5. Activate Assignment
        $assignmentService->activateAssignment($assignment, $user);

        // Verify that audit events exist and integrity chain is unbroken
        $this->assertTrue($auditService->verify($tenant->id));
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $tenant->id,
            'event_type' => 'mobility_request_created',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $tenant->id,
            'event_type' => 'mobility_assignment_activated',
        ]);
    }

    public function test_api_endpoints_enforce_tenant_scoping(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);

        $programTenant1 = MobilityProgram::create([
            'tenant_id' => $tenant1->id,
            'code' => 'T1-PROG',
            'name' => 'Tenant 1 Program',
        ]);

        $programTenant2 = MobilityProgram::create([
            'tenant_id' => $tenant2->id,
            'code' => 'T2-PROG',
            'name' => 'Tenant 2 Program',
        ]);

        $response = $this->actingAs($user1)->getJson('/api/v1/hcm/mobility/programs');
        $response->assertStatus(200);

        $data = $response->json('data');
        $codes = collect($data)->pluck('code')->toArray();

        $this->assertContains('T1-PROG', $codes);
        $this->assertNotContains('T2-PROG', $codes);
    }
}
