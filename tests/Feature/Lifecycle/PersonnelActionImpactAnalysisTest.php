<?php

namespace Tests\Feature\Lifecycle;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\ImpactSeverity;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Domains\Lifecycle\Services\PersonnelActionImpactService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelActionImpactAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_domain_impact_analysis_and_payroll_variance(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-IMP-1',
            'employee_number' => 'EMP-IMP-1',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'official_email' => 'dwight@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $actionType = PersonnelActionType::create([
            'tenant_id' => $tenant->id,
            'code' => 'PROMOTION',
            'name' => 'Promotion & Compensation Adjustment',
        ]);

        $request = PersonnelActionRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'action_type_id' => $actionType->id,
            'request_number' => 'PA-IMP-01',
            'status' => 'draft',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'effective_date' => now()->toDateString(),
        ]);

        // Change 1: Position
        PersonnelActionChange::create([
            'tenant_id' => $tenant->id,
            'personnel_action_request_id' => $request->id,
            'field_name' => 'position_id',
            'old_value_label' => 'Assistant to the Regional Manager',
            'new_value_label' => 'Regional Manager',
        ]);

        // Change 2: Salary Revision (+45,000 monthly)
        PersonnelActionChange::create([
            'tenant_id' => $tenant->id,
            'personnel_action_request_id' => $request->id,
            'field_name' => 'base_salary',
            'old_value' => '180000',
            'new_value' => '225000',
        ]);

        // Change 3: Job Grade Change
        PersonnelActionChange::create([
            'tenant_id' => $tenant->id,
            'personnel_action_request_id' => $request->id,
            'field_name' => 'job_grade_id',
            'old_value_label' => 'G8',
            'new_value_label' => 'G10',
        ]);

        $impactService = new PersonnelActionImpactService();
        $impacts = $impactService->analyzeImpact($request);

        $this->assertCount(3, $impacts);

        // Verify Payroll Variance Impact
        $payrollImpact = $request->impacts()->where('domain', 'payroll')->first();
        $this->assertNotNull($payrollImpact);
        $this->assertEquals(ImpactSeverity::WARNING->value, $payrollImpact->severity);
        $this->assertStringContainsString('+45,000.00 monthly', $payrollImpact->message);
        $this->assertEquals(45000.0, $payrollImpact->metadata['monthly_variance']);

        // Verify Benefits Impact
        $benefitsImpact = $request->impacts()->where('domain', 'benefits')->first();
        $this->assertNotNull($benefitsImpact);
        $this->assertEquals('benefit_reevaluation', $benefitsImpact->impact_type);
    }
}
