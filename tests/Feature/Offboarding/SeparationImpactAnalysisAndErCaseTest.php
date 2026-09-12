<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\ImpactSeverity;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationImpactService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationImpactAnalysisAndErCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_impact_analysis_engine_and_er_case_referencing(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-IMP-29',
            'employee_number' => 'EMP-IMP-29',
            'first_name' => 'Ryan',
            'last_name' => 'Howard',
            'official_email' => 'ryan@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'INVOLUNTARY_TERMINATION',
            'name' => 'Termination',
            'category' => 'involuntary',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-IMP-01',
            'status' => 'draft',
            'requested_by' => $user->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->toDateString(),
            'effective_date' => now()->toDateString(),
            'er_case_reference_id' => 'ER-CASE-2026-904', // Safe case reference
        ]);

        $impactService = new SeparationImpactService();
        $impacts = $impactService->analyzeImpact($request);

        $this->assertCount(4, $impacts);

        // Verify Payroll Impact
        $payrollImpact = $request->impacts()->where('domain', 'payroll')->first();
        $this->assertNotNull($payrollImpact);
        $this->assertEquals(ImpactSeverity::WARNING->value, $payrollImpact->severity);

        // Verify Asset Blocking Impact
        $assetImpact = $request->impacts()->where('domain', 'assets')->first();
        $this->assertNotNull($assetImpact);
        $this->assertEquals(ImpactSeverity::BLOCKING->value, $assetImpact->severity);

        // Verify ER Case Reference Impact (Safe reference without confidential notes)
        $erImpact = $request->impacts()->where('domain', 'er')->first();
        $this->assertNotNull($erImpact);
        $this->assertStringContainsString('ER-CASE-2026-904', $erImpact->message);
        $this->assertStringNotContainsString('fraud', $erImpact->message);
    }
}
