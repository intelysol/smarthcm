<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\CompetencyCategory;
use App\Domains\Performance\Models\CompetencyFramework;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Services\PerformanceCompetencyService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceCompetencyAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_competency_assessment_and_gap_evaluation(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CMP-01',
            'employee_number' => 'EMP-CMP-01',
            'first_name' => 'Faisal',
            'last_name' => 'Qureshi',
            'official_email' => 'faisal@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Appraisal Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $review = PerformanceReview::create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'review_type' => 'annual',
            'status' => 'draft',
        ]);

        $framework = CompetencyFramework::create([
            'tenant_id' => $tenant->id,
            'name' => 'Core Framework',
            'status' => 'active',
        ]);

        $category = CompetencyCategory::create([
            'framework_id' => $framework->id,
            'name' => 'Leadership',
        ]);

        $compLeadership = Competency::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Strategic Vision & Leadership',
            'status' => 'active',
        ]);

        $compExecution = Competency::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Operational Execution',
            'status' => 'active',
        ]);

        $service = app(PerformanceCompetencyService::class);

        // 1. Record Assessments: Observed 3.0 on Leadership, 4.5 on Execution
        $service->recordAssessment($review, $compLeadership->id, 3.0, 'Needs more team coaching', 'Mentored 1 intern');
        $service->recordAssessment($review, $compExecution->id, 4.5, 'Outstanding operational delivery', 'Zero SLA breaches');

        // 2. Evaluate Gaps against expected benchmark (Expected 4.0 on Leadership, 4.0 on Execution)
        $gaps = $service->evaluateGaps($review, [
            $compLeadership->id => 4.0,
            $compExecution->id => 4.0,
        ]);

        // Gap on leadership (Expected: 4.0, Observed: 3.0 => Gap: 1.0)
        // No gap on execution (Observed 4.5 >= Expected 4.0)
        $this->assertCount(1, $gaps);
        $this->assertEquals($compLeadership->id, $gaps[0]['competency_id']);
        $this->assertEquals(1.0, $gaps[0]['gap']);
        $this->assertTrue($gaps[0]['is_development_need']);
    }
}
