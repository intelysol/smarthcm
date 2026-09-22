<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\PrivacyImpactAssessment;
use App\Domains\Compliance\Models\PrivacyProcessingActivity;
use App\Domains\Compliance\Models\PrivacyRequest;
use App\Domains\Operations\Services\DataLifecycleService;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class EnterprisePrivacyService
{
    public function __construct(
        protected ?DataLifecycleService $lifecycleService = null
    ) {
        $this->lifecycleService = $lifecycleService ?? app(DataLifecycleService::class);
    }

    /**
     * Record or update an official Record of Processing Activities (ROPA).
     *
     * @param  array<string>  $dataCategories
     */
    public function recordProcessingActivity(
        string $tenantId,
        string $name,
        string $purpose,
        string $businessOwner,
        array $dataCategories,
        string $legalBasis,
        string $processingLocation = 'EU-Central / Germany',
        ?string $retentionPolicyRef = null
    ): PrivacyProcessingActivity {
        return PrivacyProcessingActivity::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'name' => $name,
            ],
            [
                'id' => (string) Str::uuid(),
                'purpose' => $purpose,
                'business_owner' => $businessOwner,
                'data_categories' => $dataCategories,
                'legal_basis' => $legalBasis,
                'processing_location' => $processingLocation,
                'retention_policy_ref' => $retentionPolicyRef,
            ]
        );
    }

    /**
     * Create a formal Data Protection Impact Assessment (DPIA) for high-risk processing.
     *
     * @param  array<string>  $mitigations
     */
    public function createPrivacyImpactAssessment(
        string $tenantId,
        string $activityId,
        string $title,
        string $necessitySummary,
        string $riskLevel,
        array $mitigations,
        bool $dpoApproval = false,
        string $status = 'draft'
    ): PrivacyImpactAssessment {
        return PrivacyImpactAssessment::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'processing_activity_id' => $activityId,
            'title' => $title,
            'necessity_summary' => $necessitySummary,
            'risk_level' => $riskLevel,
            'mitigations' => $mitigations,
            'dpo_approval' => $dpoApproval,
            'status' => $status,
        ]);
    }

    /**
     * Submit a formal privacy request (DSAR, deletion, portability, rectification, etc.).
     */
    public function submitPrivacyRequest(
        string $tenantId,
        int|string $userId,
        string $requestType,
        bool $identityVerified = true
    ): PrivacyRequest {
        $reference = 'PRV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        return PrivacyRequest::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'request_reference' => $reference,
            'user_id' => $userId,
            'request_type' => strtoupper($requestType),
            'identity_verified' => $identityVerified,
            'status' => 'RECEIVED',
        ]);
    }

    /**
     * Fulfill a Data Subject Access Request (DSAR) or Portability export.
     * Compiles user personal data into a structured payload and signs it with a SHA-256 hash.
     * Strictly enforces tenant boundary verification.
     *
     * @return array<string, mixed>
     */
    public function fulfillDsarExport(string $requestId, string $requestingTenantId): array
    {
        $request = PrivacyRequest::query()->findOrFail($requestId);

        // Enforce strict tenant boundary check
        if ($request->tenant_id !== $requestingTenantId) {
            throw new RuntimeException("Unauthorized cross-tenant privacy request access attempted.");
        }

        $user = User::query()
            ->where('tenant_id', $requestingTenantId)
            ->findOrFail($request->user_id);

        // Compile subject data dossier
        $exportData = [
            'export_metadata' => [
                'request_reference' => $request->request_reference,
                'request_type' => $request->request_type,
                'tenant_id' => $requestingTenantId,
                'generated_at' => now()->toIso8601String(),
                'export_standard' => 'GDPR-Art-15-16 / CCPA-1798.100',
            ],
            'personal_identity' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'locale' => $user->locale ?? 'en',
                'timezone' => $user->timezone ?? 'UTC',
                'account_created_at' => $user->created_at?->toIso8601String(),
            ],
        ];

        // Include employee details if table exists
        if (DB::getSchemaBuilder()->hasTable('employees')) {
            $employee = DB::table('employees')
                ->where('tenant_id', $requestingTenantId)
                ->where('user_id', $user->id)
                ->first();

            if ($employee) {
                $exportData['employment_record'] = [
                    'employee_number' => $employee->employee_number ?? null,
                    'department' => $employee->department ?? null,
                    'job_title' => $employee->job_title ?? null,
                    'hire_date' => $employee->hire_date ?? null,
                    'status' => $employee->status ?? null,
                ];
            }
        }

        $jsonPayload = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $payloadHash = hash('sha256', $jsonPayload);
        $storagePath = "privacy/exports/{$requestingTenantId}/dsar_{$request->request_reference}.json";

        $request->update([
            'export_path' => $storagePath,
            'export_hash_sha256' => $payloadHash,
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        return [
            'request_reference' => $request->request_reference,
            'status' => 'COMPLETED',
            'export_path' => $storagePath,
            'sha256_hash' => $payloadHash,
            'payload' => $exportData,
        ];
    }

    /**
     * Process a Right to Erasure / Deletion request.
     * Intercepted by DataLifecycleService: if active legal hold or statutory retention floor
     * restricts deletion, the request is formally blocked and auditable reason recorded.
     *
     * @return array<string, mixed>
     */
    public function processDeletionRequest(
        string $requestId,
        string $requestingTenantId,
        string $dataClass = 'Employee Master Data'
    ): array {
        $request = PrivacyRequest::query()->findOrFail($requestId);

        if ($request->tenant_id !== $requestingTenantId) {
            throw new RuntimeException("Unauthorized cross-tenant privacy deletion access attempted.");
        }

        $userIdStr = (string) $request->user_id;

        // 1. Check for Active Legal Hold
        if ($this->lifecycleService->isUnderLegalHold($requestingTenantId, $dataClass, $userIdStr)) {
            $blockedReason = "Erasure blocked: subject is protected under active enterprise legal hold for {$dataClass}.";
            $request->update([
                'status' => 'REJECTED',
                'blocked_reason' => $blockedReason,
                'completed_at' => now(),
            ]);

            return [
                'request_reference' => $request->request_reference,
                'status' => 'REJECTED',
                'reason' => $blockedReason,
            ];
        }

        // 2. Check statutory retention floors
        $user = User::query()->where('tenant_id', $requestingTenantId)->findOrFail($request->user_id);
        $policy = $this->lifecycleService->resolveEffectivePolicy($requestingTenantId, 'HCM', $dataClass);
        $statutoryFloorDays = $policy['platform_statutory_floor_days'] ?? 2555;
        $retentionCutoff = now()->subDays($statutoryFloorDays);

        if ($user->created_at && $user->created_at > $retentionCutoff) {
            $blockedReason = "Erasure rejected: mandatory statutory retention floor of {$statutoryFloorDays} days has not expired for {$dataClass}.";
            $request->update([
                'status' => 'REJECTED',
                'blocked_reason' => $blockedReason,
                'completed_at' => now(),
            ]);

            return [
                'request_reference' => $request->request_reference,
                'status' => 'REJECTED',
                'reason' => $blockedReason,
            ];
        }

        // 3. Eligible for deletion / anonymization
        $user->update([
            'name' => 'Redacted Privacy User ' . Str::random(6),
            'email' => 'redacted_' . Str::random(10) . '@anonymized.local',
            'phone' => null,
            'status' => 'anonymized',
        ]);
        $user->delete(); // Soft delete

        $request->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        return [
            'request_reference' => $request->request_reference,
            'status' => 'COMPLETED',
            'reason' => 'Subject personal records anonymized and erased successfully.',
        ];
    }

    /**
     * Aggregate privacy metrics for operations or tenant admin dashboards.
     *
     * @return array<string, mixed>
     */
    public function getPrivacyDashboardMetrics(?string $tenantId = null): array
    {
        $actQuery = PrivacyProcessingActivity::query();
        $piaQuery = PrivacyImpactAssessment::query();
        $reqQuery = PrivacyRequest::query();

        if ($tenantId) {
            $actQuery->where('tenant_id', $tenantId);
            $piaQuery->where('tenant_id', $tenantId);
            $reqQuery->where('tenant_id', $tenantId);
        }

        $activitiesCount = $actQuery->count();
        $piaCount = $piaQuery->count();
        $highRiskPiaCount = (clone $piaQuery)->where('risk_level', 'HIGH')->count();

        $totalRequests = $reqQuery->count();
        $pendingRequests = (clone $reqQuery)->whereIn('status', ['RECEIVED', 'IN_PROGRESS'])->count();
        $completedRequests = (clone $reqQuery)->where('status', 'COMPLETED')->count();
        $rejectedRequests = (clone $reqQuery)->where('status', 'REJECTED')->count();

        $resolved = $completedRequests + $rejectedRequests;
        $fulfillmentRate = $totalRequests > 0 ? round(($resolved / $totalRequests) * 100, 1) : 100.0;

        return [
            'activities_count' => $activitiesCount,
            'dpias_count' => $piaCount,
            'dpias_high_risk' => $highRiskPiaCount,
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'completed_requests' => $completedRequests,
            'rejected_requests' => $rejectedRequests,
            'fulfillment_rate_percent' => $fulfillmentRate,
        ];
    }
}
