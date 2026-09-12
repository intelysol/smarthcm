<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Models\HcmAnalyticsSnapshot;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;

class HcmWorkforceAnalyticsService
{
    public function getHeadcountSummary(string $tenantId, ?string $asOfDate = null, array $filters = []): array
    {
        $date = $asOfDate ?? now()->toDateString();

        $query = Employee::query()
            ->where('tenant_id', $tenantId)
            ->where('joining_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('termination_date')->orWhere('termination_date', '>', $date);
            });

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        if (!empty($filters['manager_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('reporting_manager_id', $filters['manager_id'])
                  ->orWhere('current_manager_employee_id', $filters['manager_id']);
            });
        }

        $employees = $query->with(['department', 'branch', 'jobGrade', 'employmentType'])->get();
        $totalHeadcount = $employees->count();
        $activeHeadcount = $employees->where('employment_status', 'active')->count();
        $inactiveHeadcount = $totalHeadcount - $activeHeadcount;

        $fteTotal = $employees->sum(function ($emp) {
            return ($emp->employmentType?->code === 'PART_TIME') ? 0.5 : 1.0;
        });

        $contractors = $employees->filter(fn ($e) => ($e->employmentType?->code === 'CONTRACTOR'))->count();
        $partTime = $employees->filter(fn ($e) => ($e->employmentType?->code === 'PART_TIME'))->count();
        $fullTime = $totalHeadcount - $partTime - $contractors;

        $byDepartment = $employees->groupBy(fn ($e) => $e->department?->department_name ?? 'Unassigned')
            ->map(fn ($g) => $g->count())->toArray();

        $byBranch = $employees->groupBy(fn ($e) => $e->branch?->branch_name ?? 'HQ')
            ->map(fn ($g) => $g->count())->toArray();

        return [
            'as_of_date' => $date,
            'total_headcount' => $totalHeadcount,
            'active_headcount' => $activeHeadcount,
            'inactive_headcount' => $inactiveHeadcount,
            'fte_total' => round($fteTotal, 2),
            'full_time' => $fullTime,
            'part_time' => $partTime,
            'contractors' => $contractors,
            'by_department' => $byDepartment,
            'by_branch' => $byBranch,
        ];
    }

    public function getTurnoverAnalytics(string $tenantId, string $startDate, string $endDate, array $filters = []): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Calculate average headcount in period
        $startHeadcount = $this->getHeadcountSummary($tenantId, $startDate, $filters)['total_headcount'];
        $endHeadcount = $this->getHeadcountSummary($tenantId, $endDate, $filters)['total_headcount'];
        $avgHeadcount = ($startHeadcount + $endHeadcount) > 0 ? ($startHeadcount + $endHeadcount) / 2 : 1;

        $exitsQuery = Employee::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('termination_date')
            ->whereDate('termination_date', '>=', $startDate)
            ->whereDate('termination_date', '<=', $endDate);

        if (!empty($filters['department_id'])) {
            $exitsQuery->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['branch_id'])) {
            $exitsQuery->where('branch_id', $filters['branch_id']);
        }

        $exits = $exitsQuery->get();
        $totalExits = $exits->count();
        $voluntaryExits = $exits->filter(fn ($e) => ($e->exit_type ?? 'voluntary') === 'voluntary')->count();
        $involuntaryExits = $totalExits - $voluntaryExits;

        // Regrettable turnover: high performers or critical talent
        $regrettableExits = $exits->filter(fn ($e) => ($e->is_high_performer ?? false))->count();

        // Calculate rates %
        $turnoverRate = round(($totalExits / $avgHeadcount) * 100, 2);
        $voluntaryTurnoverRate = round(($voluntaryExits / $avgHeadcount) * 100, 2);
        $involuntaryTurnoverRate = round(($involuntaryExits / $avgHeadcount) * 100, 2);

        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'average_headcount' => round($avgHeadcount, 1),
            'total_exits' => $totalExits,
            'voluntary_exits' => $voluntaryExits,
            'involuntary_exits' => $involuntaryExits,
            'regrettable_exits' => $regrettableExits,
            'turnover_rate_percent' => $turnoverRate,
            'voluntary_turnover_rate_percent' => $voluntaryTurnoverRate,
            'involuntary_turnover_rate_percent' => $involuntaryTurnoverRate,
        ];
    }
}
