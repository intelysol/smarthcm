<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Enums\ExceptionStatus;
use App\Domains\WorkforceAdmin\Models\OpsSlaPolicy;
use App\Domains\WorkforceAdmin\Services\HrExceptionService;
use App\Domains\WorkforceAdmin\Services\SlaMonitoringService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrExceptionAndSlaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_exception_lifecycle_and_sla_tracking(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-WA-003',
            'employee_number' => 'EMP-WA-003',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'jim@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        // Configure SLA Policy for exceptions
        OpsSlaPolicy::create([
            'tenant_id' => $tenant->id,
            'code' => 'SLA-EXC-CRITICAL',
            'name' => 'Critical Exception SLA',
            'target_entity_type' => 'exception',
            'priority' => 'critical',
            'response_time_hours' => 2,
            'resolution_time_hours' => 8,
            'is_active' => true,
        ]);

        $exceptionService = app(HrExceptionService::class);

        // 1. Record Exception
        $exception = $exceptionService->recordException([
            'tenant_id' => $tenant->id,
            'exception_type' => 'PAYROLL_SYNC_FAILURE',
            'severity' => 'critical',
            'domain' => 'payroll',
            'entity_type' => 'Employee',
            'entity_id' => $employee->id,
            'employee_id' => $employee->id,
            'description' => 'Employee tax identification could not be synchronized to external payroll gateway.',
            'resolution_guidance' => 'Verify national ID / SSN format in Employee personal data domain.',
        ]);

        $this->assertDatabaseHas('hcm_ops_exceptions', [
            'id' => $exception->id,
            'status' => ExceptionStatus::DETECTED->value,
            'severity' => 'critical',
        ]);

        // Verify SLA instance created
        $this->assertDatabaseHas('hcm_ops_sla_instances', [
            'target_entity_type' => 'exception',
            'target_entity_id' => $exception->id,
            'status' => 'running',
            'is_breached' => false,
        ]);

        Carbon::setTestNow('2026-09-01 11:00:00');

        // 2. Assign Exception
        $exceptionService->assignException($exception, $user, 'Payroll Operations', $user);
        $this->assertEquals(ExceptionStatus::ASSIGNED, $exception->fresh()->status);
        $this->assertEquals($user->id, $exception->fresh()->owner_id);

        Carbon::setTestNow('2026-09-01 14:00:00');

        // 3. Resolve Exception (within 8 hours resolution SLA)
        $exceptionService->resolveException($exception, 'Tax ID corrected and resubmitted to payroll gateway.', $user);
        $this->assertEquals(ExceptionStatus::RESOLVED, $exception->fresh()->status);
        $this->assertNotNull($exception->fresh()->resolved_at);

        // Verify SLA instance is fulfilled and not breached
        $this->assertDatabaseHas('hcm_ops_sla_instances', [
            'target_entity_id' => $exception->id,
            'status' => 'fulfilled',
            'is_breached' => false,
        ]);
    }
}
