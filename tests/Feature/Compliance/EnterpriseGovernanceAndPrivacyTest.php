<?php

declare(strict_types=1);

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\GovernanceAttestation;
use App\Domains\Compliance\Models\GovernanceControl;
use App\Domains\Compliance\Models\GovernanceControlTest;
use App\Domains\Compliance\Models\GovernanceException;
use App\Domains\Compliance\Models\GovernanceFinding;
use App\Domains\Compliance\Models\GovernanceFramework;
use App\Domains\Compliance\Models\PrivacyImpactAssessment;
use App\Domains\Compliance\Models\PrivacyProcessingActivity;
use App\Domains\Compliance\Models\PrivacyRequest;
use App\Domains\Compliance\Services\EnterpriseGovernanceService;
use App\Domains\Compliance\Services\EnterprisePrivacyService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Operations\Services\DataLifecycleService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class EnterpriseGovernanceAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected EnterpriseGovernanceService $governanceService;
    protected EnterprisePrivacyService $privacyService;
    protected DataLifecycleService $lifecycleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->governanceService = app(EnterpriseGovernanceService::class);
        $this->privacyService = app(EnterprisePrivacyService::class);
        $this->lifecycleService = app(DataLifecycleService::class);
    }

    /**
     * 1. Initialize Standard Framework Catalog (ISO 27001, SOC 2, GDPR)
     */
    public function test_standard_framework_and_controls_catalog_initialization(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global Corp',
            'slug' => 'acme-' . Str::random(6),
            'status' => 'active',
        ]);

        $result = $this->governanceService->initializeDefaultFrameworks($tenant->id);

        $this->assertEquals('initialized', $result['status']);
        $this->assertEquals(3, $result['frameworks_count']);
        $this->assertGreaterThanOrEqual(9, $result['controls_count']);

        $this->assertDatabaseHas('governance_frameworks', [
            'tenant_id' => $tenant->id,
            'code' => 'ISO27001',
        ]);
        $this->assertDatabaseHas('governance_frameworks', [
            'tenant_id' => $tenant->id,
            'code' => 'SOC2',
        ]);
        $this->assertDatabaseHas('governance_frameworks', [
            'tenant_id' => $tenant->id,
            'code' => 'GDPR',
        ]);

        $this->assertDatabaseHas('governance_controls', [
            'tenant_id' => $tenant->id,
            'code' => 'A.8.24',
        ]);
    }

    /**
     * 2. Control Test Execution with Cryptographic Evidence SHA-256 Hashing
     */
    public function test_control_test_execution_records_cryptographic_evidence_hash(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Audit Tech Corp',
            'slug' => 'audit-' . Str::random(6),
            'status' => 'active',
        ]);

        $this->governanceService->initializeDefaultFrameworks($tenant->id);
        $control = GovernanceControl::query()->where('tenant_id', $tenant->id)->where('code', 'A.8.24')->firstOrFail();

        $evidenceRaw = "TLS 1.3 cipher suite verified: TLS_AES_256_GCM_SHA384 on port 443.";
        $expectedHash = hash('sha256', $evidenceRaw);

        $test = $this->governanceService->executeControlTest(
            tenantId: $tenant->id,
            controlId: $control->id,
            testProcedure: 'Verify TLS cipher suite configuration',
            result: 'PASS',
            evidenceContent: $evidenceRaw,
            testedBy: 'ciso-auditor'
        );

        $this->assertEquals('PASS', $test->result);
        $this->assertEquals($expectedHash, $test->evidence_hash_sha256);
        $this->assertEquals(64, strlen($test->evidence_hash_sha256));

        // Ensure no finding was created on PASS
        $this->assertDatabaseMissing('governance_findings', [
            'tenant_id' => $tenant->id,
            'control_id' => $control->id,
        ]);
    }

    /**
     * 3. Control Failure Automatically Generates Remediation Finding
     */
    public function test_control_test_failure_automatically_generates_remediation_finding(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Security Ops Corp',
            'slug' => 'secops-' . Str::random(6),
            'status' => 'active',
        ]);

        $this->governanceService->initializeDefaultFrameworks($tenant->id);
        $control = GovernanceControl::query()->where('tenant_id', $tenant->id)->where('code', 'CC6.1')->firstOrFail();

        $test = $this->governanceService->executeControlTest(
            tenantId: $tenant->id,
            controlId: $control->id,
            testProcedure: 'Inspect MFA enforcement for privileged admin accounts',
            result: 'FAIL',
            evidenceContent: 'Found 2 administrative users without active hardware MFA.',
            testedBy: 'soc2-auditor'
        );

        $this->assertEquals('FAIL', $test->result);

        // Assert audit finding was automatically created
        $this->assertDatabaseHas('governance_findings', [
            'tenant_id' => $tenant->id,
            'control_id' => $control->id,
            'severity' => 'HIGH',
            'status' => 'OPEN',
        ]);

        $finding = GovernanceFinding::query()->where('control_id', $control->id)->firstOrFail();
        $this->assertStringContainsString('Control Failure: CC6.1', $finding->title);
        $this->assertNotNull($finding->due_date);
    }

    /**
     * 4. Formal Compliance Exception Governance with Compensating Control & Expiration
     */
    public function test_formal_compliance_exception_governance(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Exemption Corp',
            'slug' => 'exempt-' . Str::random(6),
            'status' => 'active',
        ]);

        $this->governanceService->initializeDefaultFrameworks($tenant->id);
        $control = GovernanceControl::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $exception = $this->governanceService->requestException(
            tenantId: $tenant->id,
            controlId: $control->id,
            reason: 'Legacy API integration lacks mTLS support',
            businessJustification: 'Required for critical banking synchronization until Q3 migration',
            riskLevel: 'MEDIUM',
            compensatingControl: 'IP allowlist on perimeter firewall + HMAC request signatures',
            durationDays: 60,
            approver: 'ciso@flowplatform.io'
        );

        $this->assertEquals('approved', $exception->status);
        $this->assertEquals('MEDIUM', $exception->risk_level);
        $this->assertTrue($exception->expires_at->isFuture());
    }

    /**
     * 5. Tamper-Evident Management Attestation with Digital Signature Hash
     */
    public function test_tamper_evident_management_attestation_signature(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Attestation Corp',
            'slug' => 'attest-' . Str::random(6),
            'status' => 'active',
        ]);

        $attestation = $this->governanceService->recordAttestation(
            tenantId: $tenant->id,
            subjectType: 'SOC2_TYPE_II_READINESS',
            statement: 'Management confirms all logical access reviews were conducted according to policy for Q2.',
            attestor: 'jane.ciso@attestationcorp.com',
            version: '2026.2'
        );

        $this->assertNotNull($attestation->signature_hash);
        $this->assertEquals(64, strlen($attestation->signature_hash));
        $this->assertDatabaseHas('governance_attestations', [
            'id' => $attestation->id,
            'tenant_id' => $tenant->id,
            'subject_type' => 'SOC2_TYPE_II_READINESS',
        ]);
    }

    /**
     * 6. Record of Processing Activities (ROPA & GDPR Art. 30) & DPIA
     */
    public function test_record_of_processing_activities_and_dpia(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Privacy Flow Corp',
            'slug' => 'prv-' . Str::random(6),
            'status' => 'active',
        ]);

        $activity = $this->privacyService->recordProcessingActivity(
            tenantId: $tenant->id,
            name: 'Employee Biometric Punch Processing',
            purpose: 'Time and attendance verification and payroll preparation',
            businessOwner: 'People Operations',
            dataCategories: ['Employee ID', 'Timestamp', 'Biometric Hash', 'Geo Coordinates'],
            legalBasis: 'Legitimate Interest / Employment Contract',
            processingLocation: 'Frankfurt, EU-Central',
            retentionPolicyRef: 'RET-HCM-ATTENDANCE-3Y'
        );

        $this->assertDatabaseHas('privacy_processing_activities', [
            'id' => $activity->id,
            'tenant_id' => $tenant->id,
            'name' => 'Employee Biometric Punch Processing',
        ]);

        $dpia = $this->privacyService->createPrivacyImpactAssessment(
            tenantId: $tenant->id,
            activityId: $activity->id,
            title: 'DPIA: Automated Geofenced Mobile Punch Clock',
            necessitySummary: 'Strictly necessary for remote shift adherence validation',
            riskLevel: 'HIGH',
            mitigations: ['One-way SHA-256 coordinates anonymization', 'Purged after 90 days'],
            dpoApproval: true,
            status: 'approved'
        );

        $this->assertDatabaseHas('privacy_impact_assessments', [
            'id' => $dpia->id,
            'processing_activity_id' => $activity->id,
            'dpo_approval' => true,
        ]);
    }

    /**
     * 7. DSAR Data Subject Access Request Fulfillment & SHA-256 Integrity Hash
     */
    public function test_dsar_export_fulfillment_and_sha256_hashing(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'DSAR Subject Corp',
            'slug' => 'dsar-' . Str::random(6),
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Elena Rostova',
            'email' => 'elena@subjectcorp.com',
        ]);

        $request = $this->privacyService->submitPrivacyRequest(
            tenantId: $tenant->id,
            userId: $user->id,
            requestType: 'ACCESS'
        );

        $this->assertEquals('RECEIVED', $request->status);

        $export = $this->privacyService->fulfillDsarExport($request->id, $tenant->id);

        $this->assertEquals('COMPLETED', $export['status']);
        $this->assertNotEmpty($export['sha256_hash']);
        $this->assertEquals(64, strlen($export['sha256_hash']));
        $this->assertEquals('Elena Rostova', $export['payload']['personal_identity']['name']);

        $request->refresh();
        $this->assertEquals('COMPLETED', $request->status);
        $this->assertEquals($export['sha256_hash'], $request->export_hash_sha256);
    }

    /**
     * 8. Strict Multi-Tenant Boundary Isolation for Privacy Requests
     */
    public function test_dsar_cross_tenant_isolation_is_strictly_enforced(): void
    {
        $tenantA = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant A Privacy',
            'slug' => 'tenant-a-' . Str::random(6),
            'status' => 'active',
        ]);

        $tenantB = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant B Privacy',
            'slug' => 'tenant-b-' . Str::random(6),
            'status' => 'active',
        ]);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $requestA = $this->privacyService->submitPrivacyRequest($tenantA->id, $userA->id, 'ACCESS');

        // Tenant B attempting to fulfill Tenant A's privacy export must be rejected
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unauthorized cross-tenant privacy request access');

        $this->privacyService->fulfillDsarExport($requestA->id, $tenantB->id);
    }

    /**
     * 9. Privacy Deletion Intercepted & Blocked by Active Legal Hold (Epic 2.78 Integration)
     */
    public function test_privacy_deletion_is_blocked_by_active_legal_hold(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Hold Protection Corp',
            'slug' => 'hold-' . Str::random(6),
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Target User Under Litigation',
            'created_at' => now()->subYears(10), // Old enough to pass statutory retention
        ]);

        // Place active legal hold on this user scope
        $this->lifecycleService->placeLegalHold(
            tenantId: $tenant->id,
            holdReference: 'LIT-2026-0044',
            reason: 'Active pending litigation inquiry',
            scopeType: 'user',
            scopeId: (string) $user->id,
            dataClasses: ['Employee Master Data']
        );

        $deletionRequest = $this->privacyService->submitPrivacyRequest($tenant->id, $user->id, 'DELETION');

        $result = $this->privacyService->processDeletionRequest($deletionRequest->id, $tenant->id, 'Employee Master Data');

        $this->assertEquals('REJECTED', $result['status']);
        $this->assertStringContainsString('active enterprise legal hold', $result['reason']);

        $deletionRequest->refresh();
        $this->assertEquals('REJECTED', $deletionRequest->status);
        $this->assertNotNull($deletionRequest->blocked_reason);

        // Confirm user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * 10. Privacy Deletion Blocked by Unexpired Statutory Retention Floor
     */
    public function test_privacy_deletion_is_blocked_by_statutory_retention_floor(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Statutory Corp',
            'slug' => 'statutory-' . Str::random(6),
            'status' => 'active',
        ]);

        // Recent user created 1 year ago (statutory floor is 2555 days / 7 years)
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Recent Employee',
            'created_at' => now()->subDays(365),
        ]);

        $deletionRequest = $this->privacyService->submitPrivacyRequest($tenant->id, $user->id, 'DELETION');

        $result = $this->privacyService->processDeletionRequest($deletionRequest->id, $tenant->id, 'Employee Master Data');

        $this->assertEquals('REJECTED', $result['status']);
        $this->assertStringContainsString('statutory retention floor of 2555 days has not expired', $result['reason']);

        // User must remain intact
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * 11. Operations, Tenant Admin, and Employee Self-Service Web Dashboards Render HTTP 200
     */
    public function test_compliance_and_privacy_dashboards_render(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Full Dashboard Corp',
            'slug' => 'full-dash-' . Str::random(6),
            'status' => 'active',
        ]);

        $company = Company::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Full Dash Company',
            'code' => 'FDC-01',
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_platform_admin' => true,
        ]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'employee_code' => 'EMP-79',
            'employee_number' => 'EMP-79',
            'first_name' => 'Admin',
            'last_name' => 'Auditor',
            'official_email' => $admin->email,
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $this->governanceService->initializeDefaultFrameworks($tenant->id);
        $this->privacyService->recordProcessingActivity(
            tenantId: $tenant->id,
            name: 'Core HR Master Data',
            purpose: 'Employment administration',
            businessOwner: 'HR Director',
            dataCategories: ['Personal Identification', 'Payroll Data'],
            legalBasis: 'Contractual Obligation'
        );

        // 1. Operations Workspace: /operations/compliance
        $opsResponse = $this->actingAs($admin)->get(route('operations.compliance'));
        $opsResponse->assertStatus(200);
        $opsResponse->assertSee('Compliance &amp; Privacy Control Plane', false);
        $opsResponse->assertSee('Certified Regulatory Frameworks');

        // 2. Tenant Admin Workspace: /admin/compliance-governance
        $tenantResponse = $this->actingAs($admin)->get(route('admin.compliance-governance'));
        $tenantResponse->assertStatus(200);
        $tenantResponse->assertSee('Compliance &amp; Privacy Governance', false);
        $tenantResponse->assertSee('Record of Processing Activities');

        // 3. Employee Self-Service: /portal/privacy
        $portalResponse = $this->actingAs($admin)->get(route('portal.privacy'));
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Privacy &amp; My Data Rights', false);
        $portalResponse->assertSee('Right of Access &amp; Portability', false);
    }
}
