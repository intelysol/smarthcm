<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeAssignment;
use App\Domains\Employee\Models\Employment;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationExecutionService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationExecutionAndCoreHrUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_atomic_execution_core_hr_mutation_and_idempotency(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-EXE-1',
            'employee_number' => 'EMP-EXE-1',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael@example.com',
            'joining_date' => now()->subYears(5)->toDateString(),
            'employment_status' => 'active',
        ]);

        $employment = Employment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'company_id' => $company->id,
            'employment_number' => 'EMP-NUM-001',
            'start_date' => now()->subYears(5)->toDateString(),
            'effective_from' => now()->subYears(5)->toDateString(),
            'status' => 'active',
        ]);
        $employee->update(['current_employment_id' => $employment->id]);

        $assignment = EmployeeAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'employment_id' => $employment->id,
            'company_id' => $company->id,
            'is_primary' => true,
            'effective_from' => now()->subYears(5)->toDateString(),
            'status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Resignation',
            'category' => 'voluntary',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-EXE-01',
            'status' => SeparationStatus::READY_FOR_EXIT->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->toDateString(),
            'approved_last_working_day' => now()->toDateString(),
            'effective_date' => now()->toDateString(),
        ]);

        $executionService = new SeparationExecutionService();

        // 1. Execute Separation
        $executed = $executionService->execute($request, $user);
        $this->assertEquals(SeparationStatus::EXITED->value, $executed->status);
        $this->assertNotNull($executed->executed_at);
        $this->assertEquals(now()->toDateString(), $executed->actual_last_working_day->toDateString());

        // 2. Core HR state check
        $this->assertEquals('separated', $employee->fresh()->employment_status);
        $this->assertEquals(now()->toDateString(), $employee->fresh()->termination_date->toDateString());

        // 3. Assignment terminated check
        $this->assertEquals('terminated', $assignment->fresh()->status);
        $this->assertEquals(now()->toDateString(), $assignment->fresh()->effective_to->toDateString());

        // 4. Idempotency check: re-executing returns the already exited record safely
        $reExecuted = $executionService->execute($executed, $user);
        $this->assertEquals(SeparationStatus::EXITED->value, $reExecuted->status);
    }
}
