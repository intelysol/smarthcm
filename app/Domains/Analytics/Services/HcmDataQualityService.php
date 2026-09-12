<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Models\HcmAnalyticsDataQualityCheck;
use App\Domains\Analytics\Models\HcmAnalyticsDataQualityResult;
use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\EmployeeCompensation;
use Illuminate\Support\Facades\DB;

class HcmDataQualityService
{
    public function runDataQualityValidation(string $tenantId): array
    {
        $checks = HcmAnalyticsDataQualityCheck::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        })->where('is_active', true)->get();

        $results = [];
        $totalScores = [];

        $totalEmployees = Employee::where('tenant_id', $tenantId)->count();

        foreach ($checks as $check) {
            $failedCount = 0;
            $sampleFailures = [];

            if ($check->check_type === 'missing_department') {
                $missingDept = Employee::where('tenant_id', $tenantId)->whereNull('department_id')->get(['id', 'employee_code', 'first_name', 'last_name']);
                $failedCount = $missingDept->count();
                $sampleFailures = $missingDept->take(5)->toArray();
            } elseif ($check->check_type === 'missing_manager') {
                $missingMgr = Employee::where('tenant_id', $tenantId)->whereNull('reporting_manager_id')->whereNull('current_manager_employee_id')->get(['id', 'employee_code', 'first_name', 'last_name']);
                $failedCount = $missingMgr->count();
                $sampleFailures = $missingMgr->take(5)->toArray();
            } elseif ($check->check_type === 'missing_salary') {
                $activeEmployees = Employee::where('tenant_id', $tenantId)->where('employment_status', 'active')->pluck('id');
                $employeesWithSalary = EmployeeCompensation::where('tenant_id', $tenantId)->whereIn('employee_id', $activeEmployees)->pluck('employee_id')->unique();
                $failedCount = $activeEmployees->diff($employeesWithSalary)->count();
            } elseif ($check->check_type === 'duplicate_employee') {
                $duplicates = Employee::where('tenant_id', $tenantId)
                    ->select('official_email', DB::raw('count(*) as count'))
                    ->groupBy('official_email')
                    ->having('count', '>', 1)
                    ->get();
                $failedCount = $duplicates->count();
            }

            $checkScore = $totalEmployees > 0 ? max(0, round((($totalEmployees - $failedCount) / $totalEmployees) * 100, 2)) : 100.00;
            $totalScores[] = $checkScore;

            $result = HcmAnalyticsDataQualityResult::create([
                'tenant_id' => $tenantId,
                'check_id' => $check->id,
                'failed_records_count' => $failedCount,
                'total_records_evaluated' => $totalEmployees,
                'quality_score' => $checkScore,
                'sample_failing_records' => $sampleFailures,
                'evaluated_at' => now(),
            ]);

            $results[] = [
                'check_code' => $check->code,
                'check_name' => $check->name,
                'severity' => $check->severity,
                'failed_records' => $failedCount,
                'total_evaluated' => $totalEmployees,
                'quality_score' => $checkScore,
            ];
        }

        $overallScore = count($totalScores) > 0 ? round(array_sum($totalScores) / count($totalScores), 2) : 100.00;

        return [
            'overall_data_quality_score' => $overallScore,
            'checks_evaluated_count' => count($checks),
            'results' => $results,
        ];
    }
}
