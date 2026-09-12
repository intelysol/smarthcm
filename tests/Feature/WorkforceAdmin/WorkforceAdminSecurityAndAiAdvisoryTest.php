<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsException;
use App\Domains\WorkforceAdmin\Models\OpsQueue;
use App\Domains\WorkforceAdmin\Services\WorkforceAdminAiAdvisoryService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAdminSecurityAndAiAdvisoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_isolation_on_operational_api_endpoints(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);

        $queueTenant1 = OpsQueue::create([
            'tenant_id' => $tenant1->id,
            'code' => 'T1-QUEUE',
            'name' => 'Tenant 1 Operations Queue',
        ]);

        $queueTenant2 = OpsQueue::create([
            'tenant_id' => $tenant2->id,
            'code' => 'T2-QUEUE',
            'name' => 'Tenant 2 Operations Queue',
        ]);

        $response = $this->actingAs($user1)->getJson('/api/v1/hcm/workforce-admin/queues');
        $response->assertStatus(200);

        $data = $response->json('data');
        $codes = collect($data)->pluck('code')->toArray();

        $this->assertContains('T1-QUEUE', $codes);
        $this->assertNotContains('T2-QUEUE', $codes);
    }

    public function test_ai_advisor_generates_strict_advisory_insights_without_autonomous_mutation(): void
    {
        $tenant = Tenant::factory()->create();

        OpsException::create([
            'tenant_id' => $tenant->id,
            'exception_number' => 'EXC-TEST-001',
            'exception_type' => 'CRITICAL_PAYROLL_BREACH',
            'severity' => 'critical',
            'domain' => 'payroll',
            'entity_type' => 'Employee',
            'entity_id' => (string) \Illuminate\Support\Str::uuid(),
            'description' => 'Unmapped general ledger department code in salary disbursement.',
            'status' => 'detected',
        ]);

        $aiService = app(WorkforceAdminAiAdvisoryService::class);
        $insights = $aiService->generateAdvisoryInsights($tenant->id);

        $this->assertTrue($insights['is_advisory_only']);
        $this->assertNotEmpty($insights['insights']);
        $this->assertNotEmpty($insights['recommendations']);

        $summaryText = implode(' ', $insights['insights']);
        $this->assertStringContainsString('critical operational exceptions', $summaryText);
    }
}
