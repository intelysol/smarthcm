<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Recruitment\Enums\ApplicationStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\RecruitmentAnalyticsService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentAnalyticsAndFunnelConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_funnel_conversions_and_time_to_hire_kpis(): void
    {
        $tenant = Tenant::factory()->create();

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-METRICS-01',
            'title' => 'Product Designer',
            'status' => 'open',
        ]);

        // Candidate 1: Hired (30 days)
        $cand1 = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-M1',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'status' => 'hired',
        ]);
        HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-M1',
            'candidate_id' => $cand1->id,
            'requisition_id' => $requisition->id,
            'status' => ApplicationStatus::HIRED->value,
            'applied_at' => now()->subDays(30),
            'hired_at' => now(),
        ]);

        // Candidate 2: Interview stage
        $cand2 = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-M2',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'email' => 'bob@example.com',
            'status' => 'active',
        ]);
        HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-M2',
            'candidate_id' => $cand2->id,
            'requisition_id' => $requisition->id,
            'status' => ApplicationStatus::INTERVIEW->value,
            'applied_at' => now()->subDays(10),
        ]);

        // Candidate 3: Rejected at screening
        $cand3 = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-M3',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie@example.com',
            'status' => 'active',
        ]);
        HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-M3',
            'candidate_id' => $cand3->id,
            'requisition_id' => $requisition->id,
            'status' => ApplicationStatus::REJECTED->value,
            'applied_at' => now()->subDays(5),
        ]);

        $service = new RecruitmentAnalyticsService();

        // 1. Funnel
        $funnel = $service->getRecruitmentFunnel($tenant->id, $requisition->id);
        $this->assertEquals(3, $funnel['total_applications']);
        $this->assertEquals(2, $funnel['screened']); // Hired and Interview are past screening
        $this->assertEquals(2, $funnel['interviewed']);
        $this->assertEquals(1, $funnel['hired']);
        $this->assertEquals(33.3, $funnel['overall_yield_pct']);

        // 2. KPIs
        $kpis = $service->getRecruitmentKpis($tenant->id);
        $this->assertEquals(1, $kpis['open_requisitions']);
        $this->assertEquals(3, $kpis['total_candidates']);
        $this->assertEquals(30.0, $kpis['average_time_to_hire_days']);
    }
}
