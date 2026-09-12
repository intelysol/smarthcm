<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Domains\Compensation\Services\CompensationCalibrationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompensationCalibrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_calibration_session_adjustment_with_mandatory_reason(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $hrLead = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-CAL-001',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'employment_status' => 'active',
            'joining_date' => '2022-03-01',
        ]);

        $cycle = CompensationCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Merit Calibration',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'effective_on' => '2026-04-01',
            'status' => 'calibration',
            'currency' => 'USD',
        ]);

        $recommendation = CompensationRecommendation::create([
            'tenant_id' => $tenant->id,
            'compensation_cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'current_base_salary' => 80000.0,
            'recommended_base_salary' => 86400.0,
            'increase_amount' => 6400.0,
            'increase_percentage' => 8.0,
            'recommendation_type' => 'merit',
            'status' => 'proposed',
        ]);

        $service = app(CompensationCalibrationService::class);

        // 1. Create Session
        $session = $service->createSession(
            $hrLead,
            $cycle,
            'Engineering Calibration Committee',
            'department',
            'dept-001'
        );

        $this->assertEquals('scheduled', $session->status);

        // 2. Reject adjust if reason is missing or too short (< 10 chars)
        try {
            $service->recordAdjustment($hrLead, $session, $recommendation, 6.0, 'Too short');
            $this->fail('Expected ValidationException due to short justification reason.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('mandatory_calibration_reason', $e->errors());
        }

        // 3. Successful calibration adjustment with auditable reason
        $record = $service->recordAdjustment(
            $hrLead,
            $session,
            $recommendation,
            6.0,
            'Calibrated to 6.0% to align with cross-department engineering cohort budget.'
        );

        $this->assertEquals(8.0, (float) $record->original_increase_pct);
        $this->assertEquals(6.0, (float) $record->calibrated_increase_pct);
        $this->assertEquals(4800.0, (float) $record->calibrated_increase_amount);
        $this->assertEquals($hrLead->id, $record->calibrated_by);

        // Verify recommendation updated
        $recommendation->refresh();
        $this->assertEquals(6.0, (float) $recommendation->increase_percentage);
        $this->assertEquals(4800.0, (float) $recommendation->increase_amount);
        $this->assertEquals(84800.0, (float) $recommendation->recommended_base_salary);
        $this->assertEquals('calibrated', $recommendation->status);

        // Complete session
        $completed = $service->completeSession($session);
        $this->assertEquals('completed', $completed->status);
    }
}
