<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\HcmPayrollTimeExport;
use App\Domains\Attendance\Models\HcmPayrollTimeReconciliation;
use Illuminate\Support\Str;

class PayrollTimeReconciliationService
{
    /**
     * Reconcile an export payload against payroll processed registers.
     * Detects: missing records, hour mismatches, and overtime mismatches.
     */
    public function reconcileExportAgainstPayrollRecords(
        string $exportId,
        array $payrollProcessedRecords,
        ?string $payrollBatchId = null,
        ?int $reconciledById = null
    ): HcmPayrollTimeReconciliation {
        $export = HcmPayrollTimeExport::findOrFail($exportId);
        $exportedRecords = $export->export_payload['records'] ?? [];

        $exportedMap = collect($exportedRecords)->keyBy('employee_id');
        $payrollMap = collect($payrollProcessedRecords)->keyBy('employee_id');

        $discrepancies = [];

        // 1. Check exported employees vs payroll
        foreach ($exportedMap as $empId => $exported) {
            if (! $payrollMap->has($empId)) {
                $discrepancies[] = [
                    'employee_id' => $empId,
                    'type' => 'missing_in_payroll',
                    'detail' => 'Employee was exported from Time & Attendance but is missing in payroll run.',
                    'exported_regular_hours' => $exported['regular_hours'],
                    'payroll_regular_hours' => 0,
                ];
                continue;
            }

            $payItem = $payrollMap->get($empId);
            $regDiff = abs((float) $exported['regular_hours'] - (float) ($payItem['regular_hours'] ?? 0));
            $otDiff = abs((float) $exported['total_overtime_hours'] - (float) ($payItem['total_overtime_hours'] ?? 0));

            if ($regDiff > 0.05) {
                $discrepancies[] = [
                    'employee_id' => $empId,
                    'type' => 'regular_hours_mismatch',
                    'detail' => "Regular hours mismatch: exported {$exported['regular_hours']}h vs payroll {$payItem['regular_hours']}h.",
                    'exported_regular_hours' => $exported['regular_hours'],
                    'payroll_regular_hours' => $payItem['regular_hours'] ?? 0,
                    'variance' => round((float) $exported['regular_hours'] - (float) ($payItem['regular_hours'] ?? 0), 2),
                ];
            }

            if ($otDiff > 0.05) {
                $discrepancies[] = [
                    'employee_id' => $empId,
                    'type' => 'overtime_hours_mismatch',
                    'detail' => "Overtime mismatch: exported {$exported['total_overtime_hours']}h vs payroll {$payItem['total_overtime_hours']}h.",
                    'exported_ot_hours' => $exported['total_overtime_hours'],
                    'payroll_ot_hours' => $payItem['total_overtime_hours'] ?? 0,
                    'variance' => round((float) $exported['total_overtime_hours'] - (float) ($payItem['total_overtime_hours'] ?? 0), 2),
                ];
            }
        }

        // 2. Check employees in payroll but not in export
        foreach ($payrollMap as $empId => $payItem) {
            if (! $exportedMap->has($empId)) {
                $discrepancies[] = [
                    'employee_id' => $empId,
                    'type' => 'unexpected_in_payroll',
                    'detail' => 'Employee is present in payroll pay register without approved attendance export.',
                    'exported_regular_hours' => 0,
                    'payroll_regular_hours' => $payItem['regular_hours'] ?? 0,
                ];
            }
        }

        $hasDiscrepancies = count($discrepancies) > 0;

        return HcmPayrollTimeReconciliation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $export->tenant_id,
            'payroll_time_export_id' => $export->id,
            'payroll_batch_id' => $payrollBatchId,
            'status' => $hasDiscrepancies ? 'discrepancy_detected' : 'balanced',
            'total_records_checked' => count($exportedRecords),
            'discrepant_records_count' => count($discrepancies),
            'discrepancies' => $discrepancies,
            'reconciled_by' => $reconciledById,
            'reconciled_at' => now(),
        ]);
    }
}