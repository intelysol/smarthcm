<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitElection;
use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitEnrollmentWindow;
use App\Domains\Benefits\Models\BenefitIntegration;
use App\Domains\Benefits\Models\BenefitLifeEvent;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitReconciliation;
use App\Domains\Benefits\Models\BenefitWaiver;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BenefitsAdministrationService
{
    public function __construct(
        protected BenefitEligibilityService $eligibilityService,
        protected AuditService $auditService
    ) {}

    /**
     * Executive Benefits Administration Dashboard Metrics
     */
    public function getDashboardMetrics(string $tenantId): array
    {
        $activeEmployeesCount = Employee::where('tenant_id', $tenantId)->where('employment_status', 'active')->count();

        $enrolledEmployeesCount = BenefitEnrollment::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'active'])
            ->distinct('employee_id')
            ->count('employee_id');

        $pendingEnrollmentsCount = BenefitEnrollment::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'submitted', 'pending_approval'])
            ->count();

        $pendingElectionsCount = BenefitElection::where('tenant_id', $tenantId)
            ->whereIn('status', ['elected', 'confirmed'])
            ->count();

        $openWindowsCount = BenefitEnrollmentWindow::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->count();

        $pendingLifeEventsCount = BenefitLifeEvent::where('tenant_id', $tenantId)
            ->where('status', 'submitted')
            ->count();

        $pendingDocVerificationCount = BenefitLifeEvent::where('tenant_id', $tenantId)
            ->where('documentation_status', 'pending')
            ->count();

        $pendingProviderIntegrationsCount = BenefitIntegration::where('tenant_id', $tenantId)
            ->where('integration_type', 'provider')
            ->where('status', 'pending')
            ->count();

        $pendingReconciliationsCount = BenefitReconciliation::where('tenant_id', $tenantId)
            ->whereIn('status', ['missing_in_payroll', 'amount_mismatch', 'pending'])
            ->count();

        $totalWaiversCount = BenefitWaiver::where('tenant_id', $tenantId)->count();

        $participationRate = $activeEmployeesCount > 0
            ? round(($enrolledEmployeesCount / $activeEmployeesCount) * 100, 1)
            : 0;

        return [
            'total_active_employees' => $activeEmployeesCount,
            'enrolled_employees' => $enrolledEmployeesCount,
            'participation_rate' => $participationRate,
            'pending_enrollments' => $pendingEnrollmentsCount + $pendingElectionsCount,
            'open_enrollment_campaigns' => $openWindowsCount,
            'pending_life_events' => $pendingLifeEventsCount,
            'pending_document_verifications' => $pendingDocVerificationCount,
            'pending_provider_integrations' => $pendingProviderIntegrationsCount,
            'pending_payroll_reconciliations' => $pendingReconciliationsCount,
            'total_waivers' => $totalWaiversCount,
        ];
    }

    /**
     * Perform dry-run and execution for bulk operations
     */
    public function executeBulkOperation(
        string $tenantId,
        string $operationType,
        array $params,
        bool $dryRun = true,
        ?User $actor = null
    ): array {
        if ($operationType === 'bulk_eligibility_evaluation') {
            if ($dryRun) {
                $count = Employee::where('tenant_id', $tenantId)->where('employment_status', 'active')->count();
                return [
                    'dry_run' => true,
                    'operation' => $operationType,
                    'eligible_population_preview' => $count,
                    'message' => "Preview: {$count} active employees will be evaluated against active plans.",
                ];
            }

            $stats = $this->eligibilityService->batchEvaluate($tenantId, $params['plan_id'] ?? null);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'benefits.bulk_eligibility_evaluated',
                action: 'bulk_execute',
                entityType: BenefitPlan::class,
                entityId: $params['plan_id'] ?? $tenantId,
                actorId: $actor?->id,
                after: $stats
            );

            return [
                'dry_run' => false,
                'operation' => $operationType,
                'results' => $stats,
            ];
        }

        return ['error' => "Unsupported bulk operation: {$operationType}"];
    }
}
