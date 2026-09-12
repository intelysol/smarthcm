<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Enums\ExceptionStatus;
use App\Domains\WorkforceAdmin\Jobs\CheckSlaBreachesJob;
use App\Domains\WorkforceAdmin\Models\OpsException;
use App\Domains\WorkforceAdmin\Models\OpsSlaInstance;
use App\Domains\WorkforceAdmin\Models\OpsSlaPolicy;
use App\Domains\WorkforceAdmin\Services\HrExceptionService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAdminExceptionsAndSlaTest extends TestCase
{
    use RefreshDatabase;

    public function test_exception_lifecycle_and_sla_tracking(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-EXC-001',
            'employee_number' => 'EMP-EXC-001',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'official_email' => 'dwight@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // SLA Policy
        OpsSlaPolicy::create([
            'tenant_id' => $tenant->id,
            'code' => 'CRITICAL_SLA',
            'name' => 'Critical Exception Policy',
            'target_entity_type' => 'exception',
            'priority' => 'critical',
            'response_time_hours' => 2,
            'resolution_time_hours' => 6,
        ]);

        $exceptionService = app(HrExceptionService::class);

        // 1. Record Exception
        $exception = $exceptionService->recordException([
            'tenant_id' => $tenant->id,
            'exception_type' => 'PAYROLL_MISMATCH',
            'severity' => 'critical',
            'domain' => 'payroll',
            'entity_type' => 'Employee',
            'entity_id' => $employee->id,
            'employee_id' => $employee->id,
            'description' => 'Discrepancy in bank routing transit code.',
        ]);

        $this->assertEquals(ExceptionStatus::DETECTED, $exception->status);
        $this->assertDatabaseHas('hcm_ops_exceptions', ['id' => $exception->id]);
        $this->assertDatabaseHas('hcm_ops_sla_instances', [
            'target_entity_type' => 'exception',
            'target_entity_id' => $exception->id,
            'status' => 'running',
        ]);

        // 2. Assign Exception
        $assigned = $exceptionService->assignException($exception, $user, 'Payroll Ops', $user);
        $this->assertEquals(ExceptionStatus::ASSIGNED, $assigned->status);
        $this->assertEquals($user->id, $assigned->owner_id);

        $slaInstance = OpsSlaInstance::where('target_entity_id', $exception->id)->first();
        $this->assertNotNull($slaInstance->first_response_at);

        // 3. Resolve Exception
        $resolved = $exceptionService->resolveException($assigned, 'Corrected routing number in bank master.', $user);
        $this->assertEquals(ExceptionStatus::RESOLVED, $resolved->status);
        $this->assertNotNull($resolved->resolved_at);

        $slaInstance->refresh();
        $this->assertNotNull($slaInstance->resolved_at);
        $this->assertEquals('fulfilled', $slaInstance->status);
    }

    public function test_sla_breach_detection_job(): void
    {
        $tenant = Tenant::factory()->create();

        $slaInstance = OpsSlaInstance::create([
            'tenant_id' => $tenant->id,
            'sla_policy_id' => (string) \Illuminate\Support\Str::uuid(),
            'target_entity_type' => 'exception',
            'target_entity_id' => (string) \Illuminate\Support\Str::uuid(),
            'started_at' => now()->subDays(2),
            'response_due_at' => now()->subDays(1),
            'resolution_due_at' => now()->subHours(12),
            'status' => 'running',
            'is_breached' => false,
        ]);

        CheckSlaBreachesJob::dispatchSync($tenant->id);

        $slaInstance->refresh();
        $this->assertTrue($slaInstance->is_breached);
    }
}
