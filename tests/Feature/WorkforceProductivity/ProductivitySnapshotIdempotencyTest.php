<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\Models\HcmProductivityAudit;
use App\Domains\WorkforceProductivity\Models\HcmProductivitySnapshot;
use App\Domains\WorkforceProductivity\Services\ProductivitySnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductivitySnapshotIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected ProductivitySnapshotService $snapshotService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshotService = app(ProductivitySnapshotService::class);
    }

    public function test_snapshot_generation_is_idempotent_and_creates_audit_log(): void
    {
        $tenant = Tenant::factory()->create();
        $idempotencyKey = 'SNAP-IDEMPOTENT-2026-10-KEY';

        $snapshot1 = $this->snapshotService->buildSnapshot(
            tenantId: $tenant->id,
            periodType: 'monthly',
            periodName: '2026-10',
            startDate: '2026-10-01',
            endDate: '2026-10-31',
            idempotencyKey: $idempotencyKey
        );

        $this->assertDatabaseHas('hcm_productivity_snapshots', [
            'id' => $snapshot1->id,
            'tenant_id' => $tenant->id,
            'period_name' => '2026-10',
            'status' => 'draft',
        ]);

        // Second call with same idempotency key should return snapshot1 without creating a new record
        $snapshot2 = $this->snapshotService->buildSnapshot(
            tenantId: $tenant->id,
            periodType: 'monthly',
            periodName: '2026-10',
            startDate: '2026-10-01',
            endDate: '2026-10-31',
            idempotencyKey: $idempotencyKey
        );

        $this->assertEquals($snapshot1->id, $snapshot2->id);
        $this->assertEquals(1, HcmProductivitySnapshot::where('tenant_id', $tenant->id)->count());

        // Lock snapshot
        $lockedSnapshot = $this->snapshotService->lockSnapshot($snapshot1->id);
        $this->assertEquals('locked', $lockedSnapshot->status);
        $this->assertNotNull($lockedSnapshot->locked_at);

        // Verify audit log has CREATE_SNAPSHOT and LOCK_SNAPSHOT
        $this->assertDatabaseHas('hcm_productivity_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'CREATE_SNAPSHOT',
            'target_id' => $snapshot1->id,
        ]);

        $this->assertDatabaseHas('hcm_productivity_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'LOCK_SNAPSHOT',
            'target_id' => $snapshot1->id,
        ]);
    }
}
