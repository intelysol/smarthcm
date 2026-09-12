<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Services;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationPayrollExport;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompensationPayrollIntegrationService
{
    /**
     * Export approved compensation recommendations to Payroll integration staging.
     * Architectural boundary: Compensation produces approved effective-dated pay changes;
     * Payroll remains authoritative for actual gross/net processing.
     */
    public function exportApprovedCycleToPayroll(User $user, CompensationCycle $cycle): CompensationPayrollExport
    {
        $approvedRecs = $cycle->recommendations()
            ->whereIn('status', ['approved', 'calibrated'])
            ->get();

        if ($approvedRecs->isEmpty()) {
            throw ValidationException::withMessages([
                'cycle' => 'No approved compensation recommendations found to export for this cycle.',
            ]);
        }

        $payload = [];
        $totalImpact = 0.0;

        foreach ($approvedRecs as $rec) {
            $impact = (float) $rec->increase_amount;
            $totalImpact += $impact;

            $payload[] = [
                'recommendation_id' => $rec->id,
                'employee_id' => $rec->employee_id,
                'current_base_salary' => (float) $rec->current_base_salary,
                'new_base_salary' => (float) $rec->recommended_base_salary,
                'increase_amount' => $impact,
                'increase_percentage' => (float) $rec->increase_percentage,
                'recommendation_type' => $rec->recommendation_type,
                'promotion_grade_id' => $rec->promotion_grade_id,
                'promotion_increase_amount' => (float) $rec->promotion_increase_amount,
                'market_adjustment_amount' => (float) $rec->market_adjustment_amount,
                'effective_date' => $rec->effective_date ? $rec->effective_date->format('Y-m-d') : ($cycle->effective_on ? $cycle->effective_on->format('Y-m-d') : now()->format('Y-m-d')),
                'currency' => $rec->currency ?? $cycle->currency ?? 'USD',
            ];
        }

        $batchRef = 'PAYROLL-COMP-' . strtoupper(Str::random(8));

        $export = CompensationPayrollExport::create([
            'tenant_id' => $user->tenant_id,
            'compensation_cycle_id' => $cycle->id,
            'batch_reference' => $batchRef,
            'effective_date' => $cycle->effective_on ?? now(),
            'record_count' => count($payload),
            'total_increase_payroll_impact' => $totalImpact,
            'currency' => $cycle->currency ?? 'USD',
            'status' => 'exported',
            'sync_payload' => json_encode($payload),
            'integration_response' => json_encode([
                'status' => 'success',
                'gateway' => 'Flow-Enterprise-Payroll-Adapter',
                'timestamp' => now()->toIso8601String(),
                'batch_id' => $batchRef,
            ]),
            'exported_by' => $user->id,
            'exported_at' => now(),
        ]);

        // Transition recommendations to exported status
        foreach ($approvedRecs as $rec) {
            $rec->update(['status' => 'exported']);
        }

        return $export;
    }

    public function updateIntegrationStatus(CompensationPayrollExport $export, string $newStatus, ?array $response = null): CompensationPayrollExport
    {
        $export->update([
            'status' => $newStatus,
            'integration_response' => $response ? json_encode($response) : $export->integration_response,
        ]);

        return $export->fresh();
    }
}
