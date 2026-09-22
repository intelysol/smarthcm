<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\GovernanceAttestation;
use App\Domains\Compliance\Models\GovernanceControl;
use App\Domains\Compliance\Models\GovernanceControlTest;
use App\Domains\Compliance\Models\GovernanceException;
use App\Domains\Compliance\Models\GovernanceFinding;
use App\Domains\Compliance\Models\GovernanceFramework;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class EnterpriseGovernanceService
{
    /**
     * Standard framework catalog with baseline controls.
     */
    protected array $standardFrameworks = [
        [
            'code' => 'ISO27001',
            'name' => 'ISO/IEC 27001:2022 ISMS',
            'authority' => 'International Organization for Standardization',
            'jurisdiction' => 'Global',
            'version' => '2022',
            'controls' => [
                ['code' => 'A.5.1', 'title' => 'Policies for Information Security', 'objective' => 'Maintain defined security policies approved by management.'],
                ['code' => 'A.8.1', 'title' => 'User Endpoint Devices', 'objective' => 'Protect endpoint devices with encryption and remote wipe.'],
                ['code' => 'A.8.24', 'title' => 'Use of Cryptography', 'objective' => 'Enforce AES-256 encryption at rest and TLS 1.3 in transit.'],
            ],
        ],
        [
            'code' => 'SOC2',
            'name' => 'AICPA SOC 2 Type II',
            'authority' => 'AICPA',
            'jurisdiction' => 'Global',
            'version' => '2024',
            'controls' => [
                ['code' => 'CC6.1', 'title' => 'Logical Access Controls & Multi-Factor Auth', 'objective' => 'Restrict logical access to infrastructure via least-privilege.'],
                ['code' => 'CC6.6', 'title' => 'Boundary Protection & Network Segmentation', 'objective' => 'Enforce network isolation and tenant boundaries.'],
                ['code' => 'CC7.2', 'title' => 'Incident Monitoring & Continuous Telemetry', 'objective' => 'Detect and remediate security vulnerabilities.'],
            ],
        ],
        [
            'code' => 'GDPR',
            'name' => 'EU General Data Protection Regulation',
            'authority' => 'European Data Protection Board',
            'jurisdiction' => 'European Union',
            'version' => '2016/679',
            'controls' => [
                ['code' => 'ART-30', 'title' => 'Records of Processing Activities (ROPA)', 'objective' => 'Document all categories of personal data processing.'],
                ['code' => 'ART-32', 'title' => 'Security of Processing & Data Pseudonymization', 'objective' => 'Implement appropriate technical safeguards.'],
                ['code' => 'ART-35', 'title' => 'Data Protection Impact Assessment (DPIA)', 'objective' => 'Assess high-risk data processing prior to execution.'],
            ],
        ],
    ];

    /**
     * Initialize standard compliance frameworks and controls for platform or tenant.
     *
     * @return array<string, mixed>
     */
    public function initializeDefaultFrameworks(?string $tenantId = null): array
    {
        $createdFrameworks = 0;
        $createdControls = 0;

        foreach ($this->standardFrameworks as $fwData) {
            $framework = GovernanceFramework::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'code' => $fwData['code'],
                ],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $fwData['name'],
                    'authority' => $fwData['authority'],
                    'jurisdiction' => $fwData['jurisdiction'],
                    'version' => $fwData['version'],
                    'status' => 'active',
                ]
            );
            $createdFrameworks++;

            foreach ($fwData['controls'] as $cData) {
                GovernanceControl::query()->firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'framework_id' => $framework->id,
                        'code' => $cData['code'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'title' => $cData['title'],
                        'objective' => $cData['objective'],
                        'control_type' => 'PREVENTIVE',
                        'frequency' => 'CONTINUOUS',
                        'status' => 'ACTIVE',
                    ]
                );
                $createdControls++;
            }
        }

        return [
            'tenant_id' => $tenantId,
            'frameworks_count' => $createdFrameworks,
            'controls_count' => $createdControls,
            'status' => 'initialized',
        ];
    }

    /**
     * Execute a compliance control test and record cryptographic evidence hash.
     * Automatically opens a finding and assigns remediation if the test fails.
     */
    public function executeControlTest(
        string $tenantId,
        string $controlId,
        string $testProcedure,
        string $result,
        ?string $evidenceContent = null,
        string $testedBy = 'compliance-auditor'
    ): GovernanceControlTest {
        $control = GovernanceControl::query()->findOrFail($controlId);

        // Calculate cryptographic evidence hash
        $evidenceHash = $evidenceContent !== null
            ? hash('sha256', $evidenceContent)
            : hash('sha256', "TEST_EXECUTION_{$controlId}_" . now()->timestamp);

        $test = GovernanceControlTest::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'control_id' => $control->id,
            'test_procedure' => $testProcedure,
            'tested_by' => $testedBy,
            'result' => $result,
            'evidence_reference' => "evidence/{$tenantId}/{$control->code}/test_" . now()->format('Ymd_His') . ".log",
            'evidence_hash_sha256' => $evidenceHash,
            'tested_at' => now(),
        ]);

        // If control test fails, automatically generate an audit finding & remediation task
        if ($result === 'FAIL') {
            GovernanceFinding::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'control_id' => $control->id,
                'title' => "Control Failure: {$control->code} - {$control->title}",
                'severity' => 'HIGH',
                'owner' => 'Compliance Operations',
                'remediation_plan' => "Remediate failure detected during procedure: {$testProcedure}",
                'status' => 'OPEN',
                'due_date' => now()->addDays(30),
            ]);
        }

        return $test;
    }

    /**
     * Request a formal compliance exception with business justification and compensating controls.
     */
    public function requestException(
        string $tenantId,
        string $controlId,
        string $reason,
        string $businessJustification,
        string $riskLevel,
        string $compensatingControl,
        int $durationDays = 90,
        ?string $approver = 'ciso'
    ): GovernanceException {
        $control = GovernanceControl::query()->findOrFail($controlId);

        return GovernanceException::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'control_id' => $control->id,
            'reason' => $reason,
            'business_justification' => $businessJustification,
            'risk_level' => $riskLevel,
            'compensating_control' => $compensatingControl,
            'approved_by' => $approver,
            'expires_at' => now()->addDays($durationDays),
            'status' => 'approved',
        ]);
    }

    /**
     * Record a tamper-evident management attestation with digital signature hash.
     */
    public function recordAttestation(
        string $tenantId,
        string $subjectType,
        string $statement,
        string $attestor,
        string $version = '1.0'
    ): GovernanceAttestation {
        $signaturePayload = "{$tenantId}:{$subjectType}:{$statement}:{$attestor}:{$version}:" . now()->toIso8601String();
        $signatureHash = hash('sha256', $signaturePayload);

        return GovernanceAttestation::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'subject_type' => $subjectType,
            'statement' => $statement,
            'attestor' => $attestor,
            'version' => $version,
            'signature_hash' => $signatureHash,
            'attested_at' => now(),
        ]);
    }

    /**
     * Get authoritative governance dashboard telemetry.
     *
     * @return array<string, mixed>
     */
    public function getGovernanceDashboardScorecard(?string $tenantId = null): array
    {
        $fwQuery = GovernanceFramework::query();
        $ctlQuery = GovernanceControl::query();
        $testQuery = GovernanceControlTest::query();
        $findingQuery = GovernanceFinding::query();
        $exQuery = GovernanceException::query();
        $attQuery = GovernanceAttestation::query();

        if ($tenantId) {
            $fwQuery->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'));
            $ctlQuery->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'));
            $testQuery->where('tenant_id', $tenantId);
            $findingQuery->where('tenant_id', $tenantId);
            $exQuery->where('tenant_id', $tenantId);
            $attQuery->where('tenant_id', $tenantId);
        }

        $frameworksCount = $fwQuery->count();
        $controlsCount = $ctlQuery->count();
        $passedTests = (clone $testQuery)->where('result', 'PASS')->count();
        $failedTests = (clone $testQuery)->where('result', 'FAIL')->count();
        $openFindings = (clone $findingQuery)->where('status', 'OPEN')->count();
        $activeExceptions = (clone $exQuery)->where('status', 'approved')->where('expires_at', '>', now())->count();
        $totalAttestations = $attQuery->count();

        $totalTests = $passedTests + $failedTests;
        $complianceRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 100.0;

        return [
            'status' => $failedTests > 0 ? 'attention_required' : 'compliant',
            'frameworks_active' => $frameworksCount,
            'controls_active' => $controlsCount,
            'passed_tests' => $passedTests,
            'failed_tests' => $failedTests,
            'open_findings' => $openFindings,
            'active_exceptions' => $activeExceptions,
            'attestations_count' => $totalAttestations,
            'compliance_score_percent' => $complianceRate,
        ];
    }
}
