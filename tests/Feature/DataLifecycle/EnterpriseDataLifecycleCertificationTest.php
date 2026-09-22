<?php

declare(strict_types=1);

namespace Tests\Feature\DataLifecycle;

use App\Domains\Operations\Models\DataLifecycleArchive;
use App\Domains\Operations\Models\DataLifecycleLegalHold;
use App\Domains\Operations\Models\DataLifecyclePolicy;
use App\Domains\Operations\Services\DataLifecycleService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class EnterpriseDataLifecycleCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected DataLifecycleService $lifecycleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lifecycleService = app(DataLifecycleService::class);
    }

    /**
     * 1. Hierarchical Inheritance & Statutory Floor Protection
     */
    public function test_lifecycle_policies_support_hierarchical_inheritance_and_enforce_platform_minimums(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Retention Compliance Corp',
            'slug' => 'retention-' . Str::random(6),
            'status' => 'active',
        ]);

        // Attempt to lower Employee Master Data retention to 1000 days (below statutory floor of 2555 days)
        $policyWithFloor = $this->lifecycleService->resolveEffectivePolicy(
            $tenant->id,
            'Employee',
            'Employee Master Data',
            1000
        );

        $this->assertEquals(2555, $policyWithFloor['effective_retention_days']);
        $this->assertTrue($policyWithFloor['statutory_floor_enforced']);
        $this->assertEquals(2555, $policyWithFloor['platform_statutory_floor_days']);

        // Request retention greater than statutory floor (3000 days > 2555 days)
        $policyAllowed = $this->lifecycleService->resolveEffectivePolicy(
            $tenant->id,
            'Employee',
            'Employee Master Data',
            3000
        );

        $this->assertEquals(3000, $policyAllowed['effective_retention_days']);
        $this->assertFalse($policyAllowed['statutory_floor_enforced']);
    }

    /**
     * 2. Retention Engine Lifecycle States Evaluation
     */
    public function test_retention_engine_evaluates_lifecycle_states_accurately(): void
    {
        // 1. Active record (< 365 days old)
        $activeRecord = $this->lifecycleService->evaluateRecordLifecycleState([
            'created_at' => now()->subDays(30)->toIso8601String(),
            'active_days' => 365,
            'data_class' => 'Employee Master Data',
        ]);
        $this->assertEquals('ACTIVE', $activeRecord['state']);
        $this->assertTrue($activeRecord['deletion_blocked']);
        $this->assertTrue($activeRecord['archive_blocked']);

        // 2. Aging record (between active threshold and +90 days)
        $agingRecord = $this->lifecycleService->evaluateRecordLifecycleState([
            'created_at' => now()->subDays(400)->toIso8601String(),
            'active_days' => 365,
            'data_class' => 'Employee Master Data',
        ]);
        $this->assertEquals('AGING', $agingRecord['state']);
        $this->assertFalse($agingRecord['archive_blocked']);

        // 3. Archive-eligible record (> 455 days)
        $eligibleRecord = $this->lifecycleService->evaluateRecordLifecycleState([
            'created_at' => now()->subDays(500)->toIso8601String(),
            'active_days' => 365,
            'data_class' => 'Employee Master Data',
        ]);
        $this->assertEquals('ARCHIVE_ELIGIBLE', $eligibleRecord['state']);
        $this->assertTrue($eligibleRecord['is_actionable']);

        // 4. Archived record within retention period (< 2555 days)
        $archivedRecord = $this->lifecycleService->evaluateRecordLifecycleState([
            'is_archived' => true,
            'created_at' => now()->subDays(1000)->toIso8601String(),
            'data_class' => 'Employee Master Data',
        ]);
        $this->assertEquals('ARCHIVED', $archivedRecord['state']);
        $this->assertTrue($archivedRecord['deletion_blocked']);

        // 5. Archived record with expired retention (> 2555 days) but protected by dependencies
        $protectedRecord = $this->lifecycleService->evaluateRecordLifecycleState([
            'is_archived' => true,
            'created_at' => now()->subDays(3000)->toIso8601String(),
            'data_class' => 'Employee Master Data',
            'has_dependencies' => true,
        ]);
        $this->assertEquals('RETENTION_PERIOD', $protectedRecord['state']);
        $this->assertTrue($protectedRecord['deletion_blocked']);

        // 6. Archived record with expired retention (> 2555 days) and zero dependencies
        $deletionEligible = $this->lifecycleService->evaluateRecordLifecycleState([
            'is_archived' => true,
            'created_at' => now()->subDays(3000)->toIso8601String(),
            'data_class' => 'Employee Master Data',
            'has_dependencies' => false,
        ]);
        $this->assertEquals('DELETION_ELIGIBLE', $deletionEligible['state']);
        $this->assertFalse($deletionEligible['deletion_blocked']);
    }

    /**
     * 3. Legal Hold Placement, Deletion Blocking & Release
     */
    public function test_legal_hold_strictly_blocks_archive_and_deletion_actions(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Litigation Target LLC',
            'slug' => 'litigation-' . Str::random(6),
            'status' => 'active',
        ]);

        $employeeId = (string) Str::uuid();

        // 1. Place Legal Hold
        $hold = $this->lifecycleService->placeLegalHold(
            tenantId: $tenant->id,
            holdReference: 'HOLD-2026-SEC-01',
            reason: 'Litigation pending in Matter #44910',
            scopeType: 'employee',
            scopeId: $employeeId,
            dataClasses: ['Employee Master Data', 'Biometric Punches & Timesheets']
        );

        $this->assertEquals('active', $hold->status);
        $this->assertTrue($this->lifecycleService->isUnderLegalHold($tenant->id, 'Employee Master Data', $employeeId));

        // 2. Evaluation returns LEGAL_HOLD state and blocks deletion/archival
        $evaluated = $this->lifecycleService->evaluateRecordLifecycleState([
            'tenant_id' => $tenant->id,
            'scope_id' => $employeeId,
            'data_class' => 'Employee Master Data',
            'created_at' => now()->subDays(3000)->toIso8601String(),
            'is_archived' => true,
        ]);

        $this->assertEquals('LEGAL_HOLD', $evaluated['state']);
        $this->assertTrue($evaluated['deletion_blocked']);
        $this->assertTrue($evaluated['archive_blocked']);

        // 3. Attempting batch archival under hold throws exception
        $this->expectException(RuntimeException::class);
        $this->lifecycleService->executeBatchArchival(
            $tenant->id,
            'Employee Master Data',
            'employees',
            [['id' => $employeeId, 'name' => 'John Doe']]
        );

        // 4. Release Legal Hold
        $released = $this->lifecycleService->releaseLegalHold($hold, 'Case dismissed by court');
        $this->assertEquals('released', $released->status);
        $this->assertNotNull($released->released_at);
        $this->assertFalse($this->lifecycleService->isUnderLegalHold($tenant->id, 'Employee Master Data', $employeeId));
    }

    /**
     * 4. Non-Destructive Dry Run & Preview Simulation
     */
    public function test_archive_dry_run_and_deletion_preview_execute_without_destructive_side_effects(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Simulation Corp',
            'slug' => 'simulation-' . Str::random(6),
            'status' => 'active',
        ]);

        // Archive Dry Run
        $archiveDryRun = $this->lifecycleService->runArchiveDryRun($tenant->id, 'Biometric Punches & Timesheets');
        $this->assertEquals('dry_run', $archiveDryRun['mode']);
        $this->assertEquals(0, $archiveDryRun['mutations_executed']);
        $this->assertGreaterThan(0, $archiveDryRun['candidates_count']);
        $this->assertGreaterThan(0.0, $archiveDryRun['projected_archive_size_mb']);

        // Deletion Dry Run
        $deletionDryRun = $this->lifecycleService->runDeletionDryRun($tenant->id, 'Biometric Punches & Timesheets');
        $this->assertEquals('dry_run_preview', $deletionDryRun['mode']);
        $this->assertEquals(0, $deletionDryRun['mutations_executed']);
        $this->assertGreaterThan(0, $deletionDryRun['eligible_for_deletion']);
        $this->assertGreaterThan(0, $deletionDryRun['protected_dependencies']);
        $this->assertTrue($deletionDryRun['requires_approval']);
    }

    /**
     * 5. Batch Archival Manifest & Cryptographic SHA-256 Checksum
     */
    public function test_batch_archival_generates_cryptographic_manifest_and_sha256_checksum(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Archive Vault Inc',
            'slug' => 'vault-' . Str::random(6),
            'status' => 'active',
        ]);

        $records = [
            ['id' => 1, 'punch_time' => '2025-01-10 08:00:00', 'type' => 'in'],
            ['id' => 2, 'punch_time' => '2025-01-10 17:00:00', 'type' => 'out'],
            ['id' => 3, 'punch_time' => '2025-01-11 08:05:00', 'type' => 'in'],
        ];

        $archive = $this->lifecycleService->executeBatchArchival(
            tenantId: $tenant->id,
            dataClass: 'Biometric Punches & Timesheets',
            originalTable: 'attendance_punches',
            records: $records,
            actor: 'qa-tester'
        );

        $this->assertInstanceOf(DataLifecycleArchive::class, $archive);
        $this->assertEquals('ARCHIVED', $archive->status);
        $this->assertEquals(3, $archive->record_count);
        $this->assertEquals(64, strlen($archive->checksum_sha256));

        // Re-calculate hash directly from manifest and verify exact match
        $manifestJson = json_encode($archive->manifest, JSON_THROW_ON_ERROR);
        $expectedHash = hash('sha256', $manifestJson);
        $this->assertEquals($expectedHash, $archive->checksum_sha256);
    }

    /**
     * 6. Multi-Tenant Boundary Isolation
     */
    public function test_multi_tenant_isolation_prevents_cross_tenant_lifecycle_mutations(): void
    {
        $tenantA = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant A Security',
            'slug' => 'tenant-a-' . Str::random(6),
            'status' => 'active',
        ]);

        $tenantB = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant B Security',
            'slug' => 'tenant-b-' . Str::random(6),
            'status' => 'active',
        ]);

        $archiveA = $this->lifecycleService->executeBatchArchival(
            tenantId: $tenantA->id,
            dataClass: 'Candidate Profiles & Resumes',
            originalTable: 'hcm_recruitment_candidates',
            records: [['id' => 101, 'name' => 'Applicant Alice']]
        );

        // Tenant B attempts to restore Tenant A archive
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unauthorized cross-tenant restoration attempt rejected.');

        $this->lifecycleService->restoreArchive($archiveA->id, $tenantB->id);
    }

    /**
     * 7. Safe Restoration with Collision Handling & Tamper Detection
     */
    public function test_archive_restoration_detects_data_conflicts_and_validates_integrity(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Restore Corp',
            'slug' => 'restore-' . Str::random(6),
            'status' => 'active',
        ]);

        $records = [
            ['id' => 201, 'title' => 'Standard Policy Note', 'simulated_collision' => false],
            ['id' => 202, 'title' => 'Conflicting Record', 'simulated_collision' => true],
        ];

        $archive = $this->lifecycleService->executeBatchArchival(
            tenantId: $tenant->id,
            dataClass: 'Candidate Profiles & Resumes',
            originalTable: 'candidates',
            records: $records
        );

        // 1. Safe Restoration with conflict detection
        $restoreResult = $this->lifecycleService->restoreArchive($archive->id, $tenant->id, 'skip_existing');
        $this->assertEquals('restored_successfully', $restoreResult['status']);
        $this->assertEquals(1, $restoreResult['restored_records']);
        $this->assertEquals(1, $restoreResult['conflicts_detected']);

        // 2. Tampering detection
        $archive->update(['checksum_sha256' => 'corrupted_sha256_hash_that_does_not_match_payload_000000000000000000']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Integrity verification failure');

        $this->lifecycleService->restoreArchive($archive->id, $tenant->id);
    }

    /**
     * 8. Operations & Tenant Admin Web Dashboards Render HTTP 200
     */
    public function test_operations_and_tenant_admin_data_lifecycle_web_dashboards_render(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Web Dashboard Corp',
            'slug' => 'web-dash-' . Str::random(6),
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_platform_admin' => true,
        ]);

        // Operations Dashboard
        $opsResponse = $this->actingAs($admin)->get(route('operations.data-lifecycle'));
        $opsResponse->assertStatus(200);
        $opsResponse->assertSee('Data Lifecycle Management');
        $opsResponse->assertSee('Governed Lifecycle Progression Pipeline');

        // Tenant Admin Dashboard
        $tenantResponse = $this->actingAs($admin)->get(route('admin.data-lifecycle'));
        $tenantResponse->assertStatus(200);
        $tenantResponse->assertSee('Data Retention &amp; Archival Policies', false);
        $tenantResponse->assertSee('Statutory Retention Floor Protection');
    }
}
