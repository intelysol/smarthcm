<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeAssignment;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationReversalService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationReversalAndReinstatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_reversal_and_formal_employee_reinstatement(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REV-29',
            'employee_number' => 'EMP-REV-29',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'official_email' => 'dwight@example.com',
            'joining_date' => now()->subYears(3)->toDateString(),
            'employment_status' => 'separated',
            'termination_date' => now()->toDateString(),
        ]);

        $employment = \App\Domains\Employee\Models\Employment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'company_id' => $company->id,
            'employment_number' => 'EMP-REV-NUM-001',
            'start_date' => now()->subYears(3)->toDateString(),
            'effective_from' => now()->subYears(3)->toDateString(),
            'status' => 'active',
        ]);
        $employee->update(['current_employment_id' => $employment->id]);

        $assignment = EmployeeAssignment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'employment_id' => $employment->id,
            'company_id' => $company->id,
            'is_primary' => true,
            'effective_from' => now()->subYears(3)->toDateString(),
            'effective_to' => now()->toDateString(),
            'status' => 'terminated',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Resignation',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-REV-01',
            'status' => SeparationStatus::EXITED->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->toDateString(),
            'actual_last_working_day' => now()->toDateString(),
            'effective_date' => now()->toDateString(),
            'executed_at' => now(),
        ]);

        $reversalService = new SeparationReversalService();

        // Formal Reinstatement
        $reversal = $reversalService->reverseSeparation(
            $request,
            $user,
            'Executive board approved immediate reinstatement and promotion to Regional Manager',
            'reinstatement'
        );

        $this->assertEquals('reinstatement', $reversal->action_type);
        $this->assertEquals(SeparationStatus::REVERSED->value, $request->fresh()->status);

        // Verify Core HR reinstatement
        $this->assertEquals('active', $employee->fresh()->employment_status);
        $this->assertNull($employee->fresh()->termination_date);

        // Verify assignment reactivation
        $this->assertEquals('active', $assignment->fresh()->status);
        $this->assertNull($assignment->fresh()->effective_to);
    }
}
