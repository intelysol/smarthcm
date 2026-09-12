<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\Attendance\Models\AttendanceSession;
use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use App\Domains\Attendance\Models\HcmTimeAllocation;
use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollCalculationLine;
use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LaborCostAggregationService
{
    /**
     * Create an individual normalized workforce cost line.
     */
    public function createCostLine(string $tenantId, array $data): HcmWorkforceCostLine
    {
        return HcmWorkforceCostLine::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'snapshot_id' => $data['snapshot_id'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'cost_center_id' => $data['cost_center_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'job_id' => $data['job_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'worker_type' => $data['worker_type'] ?? 'employee',
            'cost_category' => $data['cost_category'] ?? 'direct_labor',
            'cost_nature' => $data['cost_nature'] ?? 'ACTUAL',
            'component_type' => $data['component_type'] ?? 'BASE_PAY',
            'source_domain' => $data['source_domain'] ?? 'manual',
            'source_record_id' => $data['source_record_id'] ?? null,
            'cost_date' => $data['cost_date'] ?? now()->toDateString(),
            'amount' => (float) ($data['amount'] ?? 0),
            'currency' => $data['currency'] ?? 'USD',
            'hours_worked' => (float) ($data['hours_worked'] ?? 0),
            'rate_per_hour' => (float) ($data['rate_per_hour'] ?? 0),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Ingests payroll results idempotently from a completed PayrollRun.
     */
    public function ingestPayrollRun(string $tenantId, string $payrollRunId): int
    {
        $payrollRun = PayrollRun::where('tenant_id', $tenantId)->find($payrollRunId);
        if (! $payrollRun) {
            return 0;
        }

        // Idempotency check: if lines already ingested for this payroll run, do not duplicate
        $existingCount = HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->where('source_domain', 'payroll')
            ->where('source_record_id', $payrollRunId)
            ->count();

        if ($existingCount > 0) {
            return $existingCount;
        }

        $linesCreated = 0;
        $snapshots = PayrollCalculationSnapshot::where('tenant_id', $tenantId)
            ->where('payroll_run_id', $payrollRunId)
            ->get();

        $payrollPeriod = $payrollRun->payroll_period_id ? \App\Domains\Payroll\Models\PayrollPeriod::find($payrollRun->payroll_period_id) : null;
        $costDate = $payrollPeriod?->start_date ? \Carbon\Carbon::parse($payrollPeriod->start_date)->toDateString() : ($payrollRun->calculated_at ? \Carbon\Carbon::parse($payrollRun->calculated_at)->toDateString() : now()->toDateString());

        foreach ($snapshots as $snapshot) {
            $employee = Employee::find($snapshot->employee_id);
            $departmentId = $employee?->department_id;
            $costCenterId = $employee?->cost_center_id;
            $positionId = $employee?->current_position_id;

            // 1. Base / Gross earnings (Direct labor)
            $this->createCostLine($tenantId, [
                'employee_id' => $snapshot->employee_id,
                'department_id' => $departmentId,
                'cost_center_id' => $costCenterId,
                'position_id' => $positionId,
                'cost_category' => 'direct_labor',
                'cost_nature' => 'ACTUAL',
                'component_type' => 'BASE_PAY',
                'source_domain' => 'payroll',
                'source_record_id' => $payrollRunId,
                'cost_date' => $costDate,
                'amount' => (float) $snapshot->gross_pay,
                'currency' => $snapshot->currency ?? $payrollRun->currency ?? 'USD',
            ]);
            $linesCreated++;

            // 2. Employer Burden (Taxes and Employer Contributions)
            if ((float) $snapshot->total_employer_cost > 0) {
                $this->createCostLine($tenantId, [
                    'employee_id' => $snapshot->employee_id,
                    'department_id' => $departmentId,
                    'cost_center_id' => $costCenterId,
                    'position_id' => $positionId,
                    'cost_category' => 'burden',
                    'cost_nature' => 'ACTUAL',
                    'component_type' => 'EMPLOYER_TAX',
                    'source_domain' => 'payroll',
                    'source_record_id' => $payrollRunId,
                    'cost_date' => $costDate,
                    'amount' => (float) $snapshot->total_employer_cost,
                    'currency' => $snapshot->currency ?? $payrollRun->currency ?? 'USD',
                ]);
                $linesCreated++;
            }
        }

        return $linesCreated;
    }

    /**
     * Ingest actual attendance overtime and time allocations.
     */
    public function ingestAttendanceOvertime(string $tenantId, Carbon $startDate, Carbon $endDate): int
    {
        $overtimeRecords = HcmOvertimeTierRecord::where('tenant_id', $tenantId)
            ->whereDate('overtime_date', '>=', $startDate->toDateString())
            ->whereDate('overtime_date', '<=', $endDate->toDateString())
            ->get();

        $linesCreated = 0;
        foreach ($overtimeRecords as $ot) {
            $idempotencyKey = "ot-{$ot->id}";
            $exists = HcmWorkforceCostLine::where('tenant_id', $tenantId)
                ->where('source_domain', 'attendance')
                ->where('source_record_id', $idempotencyKey)
                ->exists();

            if (! $exists) {
                $employee = Employee::find($ot->employee_id);
                $totalMinutes = $ot->total_overtime_minutes ?: ($ot->tier_1_minutes + $ot->tier_2_minutes + $ot->tier_3_minutes);
                $otHours = round($totalMinutes / 60, 2);
                $otAmount = (float) ($ot->overtime_amount ?? ($otHours * 37.50));

                $this->createCostLine($tenantId, [
                    'employee_id' => $ot->employee_id,
                    'department_id' => $employee?->department_id,
                    'cost_center_id' => $employee?->cost_center_id,
                    'cost_category' => 'direct_labor',
                    'cost_nature' => 'ACTUAL',
                    'component_type' => 'OVERTIME',
                    'source_domain' => 'attendance',
                    'source_record_id' => $idempotencyKey,
                    'cost_date' => $ot->overtime_date,
                    'amount' => $otAmount,
                    'hours_worked' => $otHours,
                    'currency' => 'USD',
                ]);
                $linesCreated++;
            }
        }

        return $linesCreated;
    }

    /**
     * Build an immutable versioned workforce cost snapshot for a period.
     */
    public function buildSnapshot(
        string $tenantId,
        string $periodName,
        Carbon $startDate,
        Carbon $endDate,
        string $currency = 'USD',
        ?int $userId = null
    ): HcmWorkforceCostSnapshot {
        // Idempotency: find existing snapshot with same tenant and period_name
        $latest = HcmWorkforceCostSnapshot::where('tenant_id', $tenantId)
            ->where('period_name', $periodName)
            ->orderByDesc('version')
            ->first();

        $version = $latest ? $latest->version + 1 : 1;
        $idempotencyKey = md5("{$tenantId}-{$periodName}-v{$version}");

        // Aggregate cost lines within period
        $linesQuery = HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->whereDate('cost_date', '>=', $startDate->toDateString())
            ->whereDate('cost_date', '<=', $endDate->toDateString());

        $totalCost = (float) $linesQuery->sum('amount');
        $directLabor = (float) (clone $linesQuery)->where('cost_category', 'direct_labor')->sum('amount');
        $indirectLabor = (float) (clone $linesQuery)->where('cost_category', 'indirect_labor')->sum('amount');
        $burden = (float) (clone $linesQuery)->where('cost_category', 'burden')->sum('amount');
        $overtime = (float) (clone $linesQuery)->where('component_type', 'OVERTIME')->sum('amount');
        $benefits = (float) (clone $linesQuery)->whereIn('component_type', ['BENEFITS', 'INSURANCE', 'RETIREMENT'])->sum('amount');
        $contractor = (float) (clone $linesQuery)->where('cost_category', 'contractor')->sum('amount');
        $absence = (float) (clone $linesQuery)->where('component_type', 'ABSENCE')->sum('amount');
        $vacancy = (float) (clone $linesQuery)->where('component_type', 'VACANCY')->sum('amount');

        $headcount = (int) (clone $linesQuery)->distinct('employee_id')->count('employee_id');
        $laborHours = (float) (clone $linesQuery)->sum('hours_worked');
        $fte = $headcount > 0 ? (float) $headcount : 1.00;

        $snapshot = HcmWorkforceCostSnapshot::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'snapshot_number' => 'SNAP-' . strtoupper(Str::random(10)),
            'period_name' => $periodName,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'version' => $version,
            'status' => 'locked',
            'currency' => $currency,
            'total_workforce_cost' => $totalCost,
            'total_direct_labor' => $directLabor,
            'total_indirect_labor' => $indirectLabor,
            'total_burden' => $burden,
            'total_overtime_cost' => $overtime,
            'total_benefits_cost' => $benefits,
            'total_contractor_cost' => $contractor,
            'total_absence_cost' => $absence,
            'total_vacancy_cost' => $vacancy,
            'total_fte' => $fte,
            'total_headcount' => $headcount,
            'total_labor_hours' => $laborHours,
            'idempotency_key' => $idempotencyKey,
            'locked_at' => now(),
            'created_by' => $userId,
        ]);

        // Attach lines to snapshot
        HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->whereDate('cost_date', '>=', $startDate->toDateString())
            ->whereDate('cost_date', '<=', $endDate->toDateString())
            ->whereNull('snapshot_id')
            ->update(['snapshot_id' => $snapshot->id]);

        return $snapshot;
    }
}