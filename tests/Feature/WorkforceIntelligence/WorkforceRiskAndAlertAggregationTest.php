<?php

namespace Tests\Feature\WorkforceIntelligence;

use App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface;
use App\Domains\WorkforceIntelligence\Services\WorkforceAlertService;
use App\Domains\WorkforceIntelligence\Services\WorkforceDecisionQueueService;
use App\Domains\WorkforceIntelligence\Services\WorkforceRiskAggregationService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkforceRiskAndAlertAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_risk_aggregation_and_decision_queue_processing(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $riskService = app(WorkforceRiskAggregationService::class);
        $detected = $riskService->detectAndAggregateRisks($tenant->id);
        $this->assertNotEmpty($detected);

        $alertService = app(WorkforceAlertService::class);
        $alert = $alertService->generateAlert([
            'tenant_id' => $tenant->id,
            'title' => 'Excessive Shift Variance',
            'message' => 'Shift 3 recorded 25% overtime volume',
            'severity' => 'WARNING',
        ]);
        $this->assertNotNull($alert->id);

        $decisionService = app(WorkforceDecisionQueueService::class);
        $decision = $decisionService->submitDecisionItem([
            'tenant_id' => $tenant->id,
            'title' => 'Authorize Contingent Workers in Assembly',
            'summary' => 'Onboard 4 contingent operators to mitigate delivery risk',
            'urgency' => 'HIGH',
            'estimated_cost_impact' => 12500.00,
        ]);
        $this->assertEquals('PENDING', $decision->status);

        // Action Decision via API
        $response = $this->withHeaders(['X-Tenant-ID' => $tenant->id])
            ->postJson("/api/hcm/workforce-intelligence/decisions/{$decision->id}/action", [
                'status' => 'APPROVED',
                'notes' => 'Authorized by Operations Director',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hcm_command_center_decision_items', [
            'id' => $decision->id,
            'status' => 'APPROVED',
        ]);
    }
}
