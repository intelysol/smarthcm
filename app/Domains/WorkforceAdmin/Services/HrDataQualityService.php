<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityResult;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRule;
use App\Domains\WorkforceAdmin\Models\OpsDataQualityRun;
use Illuminate\Support\Str;

class HrDataQualityService
{
    /**
     * Run all active data quality rules for a tenant.
     */
    public function runQualityScan(string $tenantId): OpsDataQualityRun
    {
        $runNumber = 'DQR-' . strtoupper(Str::random(8));

        $rules = OpsDataQualityRule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $totalScanned = Employee::where('tenant_id', $tenantId)->count();
        $totalViolations = 0;

        $run = OpsDataQualityRun::create([
            'tenant_id' => $tenantId,
            'run_number' => $runNumber,
            'total_records_scanned' => $totalScanned,
            'total_violations_found' => 0,
            'overall_score' => 100.00,
        ]);

        foreach ($rules as $rule) {
            $failingEmployees = [];

            if ($rule->rule_code === 'MISSING_DEPARTMENT') {
                $failingEmployees = Employee::where('tenant_id', $tenantId)->whereNull('department_id')->get();
            } elseif ($rule->rule_code === 'MISSING_MANAGER') {
                $failingEmployees = Employee::where('tenant_id', $tenantId)->whereNull('reporting_manager_id')->get();
            }

            foreach ($failingEmployees as $emp) {
                OpsDataQualityResult::create([
                    'tenant_id' => $tenantId,
                    'run_id' => $run->id,
                    'rule_id' => $rule->id,
                    'employee_id' => $emp->id,
                    'entity_type' => 'Employee',
                    'entity_id' => $emp->id,
                    'violation_message' => "Rule violation: {$rule->name}",
                    'status' => 'open',
                ]);
                $totalViolations++;
            }
        }

        $overallScore = $totalScanned > 0 ? max(0, round((($totalScanned - $totalViolations) / $totalScanned) * 100, 2)) : 100.00;

        $run->update([
            'total_violations_found' => $totalViolations,
            'overall_score' => $overallScore,
        ]);

        return $run;
    }
}
