<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitReconciliation;
use App\Domains\Payroll\Models\PayrollInput;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BenefitPayrollReconciliationService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Reconcile benefits planned contributions against actual payroll deductions for a payroll period.
     */
    public function reconcilePeriod(PayrollPeriod $period, ?User $actor = null): array
    {
        $tenantId = $period->tenant_id;
        $periodDate = $period->end_date->toDateString();

        // Get all active enrollments for this period with employee contributions
        $enrollments = BenefitEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'active'])
            ->whereDate('effective_from', '<=', $periodDate)
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $periodDate);
            })
            ->where('employee_contribution', '>', 0)
            ->with(['employee', 'plan'])
            ->get();

        // Get payroll inputs & lines for this period
        $payrollInputs = PayrollInput::query()
            ->where('tenant_id', $tenantId)
            ->where('payroll_period_id', $period->id)
            ->with(['lines' => function ($q) {
                $q->where('source_module', 'benefits');
            }])
            ->get()
            ->keyBy('employee_id');

        $reconciliations = [];
        $stats = [
            'total_enrollments' => $enrollments->count(),
            'matched' => 0,
            'amount_mismatch' => 0,
            'missing_in_payroll' => 0,
            'total_benefit_amount' => 0.0,
            'total_payroll_amount' => 0.0,
            'total_variance' => 0.0,
        ];

        DB::transaction(function () use ($enrollments, $payrollInputs, $period, $tenantId, &$reconciliations, &$stats, $actor) {
            // Delete prior reconciliations for this period to allow re-runs
            BenefitReconciliation::where('tenant_id', $tenantId)
                ->where('payroll_period_id', $period->id)
                ->delete();

            foreach ($enrollments as $enr) {
                $plannedAmount = (float) $enr->employee_contribution;
                $empInput = $payrollInputs->get($enr->employee_id);

                $matchedLine = null;
                if ($empInput) {
                    $matchedLine = $empInput->lines->first(function ($line) use ($enr) {
                        return $line->source_entity_id === $enr->id || $line->source_entity_type === BenefitEnrollment::class;
                    });
                }

                $payrollAmount = $matchedLine ? (float) $matchedLine->amount : 0.0;
                $variance = round($payrollAmount - $plannedAmount, 4);

                if (! $matchedLine) {
                    $status = 'missing_in_payroll';
                    $notes = 'Deduction missing in payroll inputs for this period.';
                    $stats['missing_in_payroll']++;
                } elseif (abs($variance) > 0.01) {
                    $status = 'amount_mismatch';
                    $notes = "Deduction variance of {$variance} ({$enr->currency}). Planned: {$plannedAmount}, Payroll: {$payrollAmount}.";
                    $stats['amount_mismatch']++;
                } else {
                    $status = 'matched';
                    $notes = 'Perfect match between benefit election and payroll deduction.';
                    $stats['matched']++;
                }

                $stats['total_benefit_amount'] += $plannedAmount;
                $stats['total_payroll_amount'] += $payrollAmount;
                $stats['total_variance'] += abs($variance);

                $rec = BenefitReconciliation::create([
                    'tenant_id' => $tenantId,
                    'payroll_period_id' => $period->id,
                    'benefit_enrollment_id' => $enr->id,
                    'employee_id' => $enr->employee_id,
                    'benefit_amount' => $plannedAmount,
                    'payroll_deduction_amount' => $payrollAmount,
                    'variance' => $variance,
                    'status' => $status,
                    'reconciled_at' => now(),
                    'notes' => $notes,
                ]);

                $reconciliations[] = $rec;
            }

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'benefit_payroll.reconciled',
                action: 'reconcile',
                entityType: PayrollPeriod::class,
                entityId: $period->id,
                actorId: $actor?->id,
                after: $stats
            );
        });

        return [
            'period_id' => $period->id,
            'summary' => $stats,
            'reconciliations' => $reconciliations,
        ];
    }

    public function getReconciliations(string $tenantId, ?string $periodId = null, ?string $status = null): Collection
    {
        $query = BenefitReconciliation::where('tenant_id', $tenantId)->with(['employee', 'enrollment.plan']);
        if ($periodId) {
            $query->where('payroll_period_id', $periodId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        return $query->get();
    }
}
