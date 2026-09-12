<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Services\PerformanceFinalOutcomeService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceAcknowledgementAndAppealTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_publish_outcome_employee_acknowledgement_and_appeal(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $empUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'user_id' => $empUser->id,
            'employee_code' => 'EMP-ACK-01',
            'employee_number' => 'EMP-ACK-01',
            'first_name' => 'Nadia',
            'last_name' => 'Ali',
            'official_email' => 'nadia@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Appraisal Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $review = PerformanceReview::create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'review_type' => 'annual',
            'status' => 'calibrated',
            'final_rating' => 4.0,
        ]);

        $service = app(PerformanceFinalOutcomeService::class);

        // 1. HR Publishes Final Outcome
        $outcome = $service->publishOutcome([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'final_rating' => 4.0,
            'final_score' => 82.5,
            'summary' => 'Solid annual delivery meeting all core organizational KPIs.',
            'strengths' => 'Reliable sprint throughput and client responsiveness.',
            'development_areas' => 'Cross-functional architecture documentation.',
        ], $admin);

        $this->assertEquals(4.0, (float) $outcome->final_rating);
        $this->assertEquals('pending', $outcome->acknowledgement_status);

        // 2. Employee Acknowledges Review
        $acknowledged = $service->acknowledgeReview($outcome, 'I acknowledge discussion of the final review with my manager.');
        $this->assertEquals('acknowledged', $acknowledged->acknowledgement_status);
        $this->assertNotNull($acknowledged->employee_acknowledged_at);

        // 3. Employee Submits Appeal / Dispute
        $appeal = $service->submitAppeal(
            $review->id,
            $employee->id,
            'Q2 product release delay was external and beyond team direct control.',
            $tenant->id
        );

        $this->assertEquals('submitted', $appeal->status);

        // 4. HR Resolves Appeal
        $resolved = $service->resolveAppeal($appeal, 'HR reviewed vendor dependencies and confirmed mitigating factors.', $admin);
        $this->assertEquals('resolved', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
    }
}
