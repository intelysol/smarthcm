<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Enums\HcmSnapshotType;
use App\Domains\Analytics\Models\HcmAnalyticsSnapshot;
use App\Domains\Analytics\Models\HcmAnalyticsSnapshotRun;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HcmWorkforceSnapshotService
{
    public function buildSnapshot(string $tenantId, string $date, string $type = 'daily'): HcmAnalyticsSnapshot
    {
        $run = HcmAnalyticsSnapshotRun::create([
            'tenant_id' => $tenantId,
            'snapshot_type' => $type,
            'snapshot_date' => $date,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $parsedDate = Carbon::parse($date);
            $startOfMonth = $parsedDate->copy()->startOfMonth()->toDateString();
            $endOfMonth = $parsedDate->copy()->endOfMonth()->toDateString();

            // 1. Point-in-time Employee Queries
            $employees = Employee::query()
                ->where('tenant_id', $tenantId)
                ->where('joining_date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('termination_date')->orWhere('termination_date', '>', $date);
                })
                ->with(['department', 'branch', 'jobGrade', 'employmentType'])
                ->get();

            $totalHeadcount = $employees->count();
            $activeHeadcount = $employees->where('employment_status', 'active')->count();
            $inactiveHeadcount = $totalHeadcount - $activeHeadcount;
            
            // FTE Calculation
            $fteTotal = $employees->sum(function ($emp) {
                return ($emp->employmentType?->code === 'PART_TIME') ? 0.5 : 1.0;
            });

            $contractorCount = $employees->filter(fn ($e) => ($e->employmentType?->code === 'CONTRACTOR'))->count();
            $partTimeCount = $employees->filter(fn ($e) => ($e->employmentType?->code === 'PART_TIME'))->count();
            $fullTimeCount = $totalHeadcount - $partTimeCount - $contractorCount;

            // 2. Movement Queries in Month
            $newHiresCount = Employee::where('tenant_id', $tenantId)
                ->whereBetween('joining_date', [$startOfMonth, $date])
                ->count();

            $terminations = Employee::where('tenant_id', $tenantId)
                ->whereBetween('termination_date', [$startOfMonth, $date])
                ->get();

            $terminationsCount = $terminations->count();
            $voluntaryExitsCount = $terminations->where('exit_type', 'voluntary')->count();
            $involuntaryExitsCount = $terminationsCount - $voluntaryExitsCount;

            // 3. Dimensional Breakdowns
            $byDepartment = $employees->groupBy(fn ($e) => $e->department?->department_name ?? 'Unassigned')
                ->map(fn ($group) => $group->count())
                ->toArray();

            $byBranch = $employees->groupBy(fn ($e) => $e->branch?->branch_name ?? 'HQ')
                ->map(fn ($group) => $group->count())
                ->toArray();

            $byJobGrade = $employees->groupBy(fn ($e) => $e->jobGrade?->grade_name ?? 'Standard')
                ->map(fn ($group) => $group->count())
                ->toArray();

            $byGender = $employees->groupBy(fn ($e) => $e->gender ?? 'Not Specified')
                ->map(fn ($group) => $group->count())
                ->toArray();

            $byTenureBand = $employees->groupBy(function ($e) use ($parsedDate) {
                if (! $e->joining_date) return 'Unknown';
                $years = $parsedDate->diffInYears($e->joining_date);
                if ($years < 1) return '< 1 Year';
                if ($years < 3) return '1 - 3 Years';
                if ($years < 5) return '3 - 5 Years';
                return '5+ Years';
            })->map(fn ($group) => $group->count())->toArray();

            $snapshot = HcmAnalyticsSnapshot::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'snapshot_type' => $type,
                    'snapshot_date' => $date,
                ],
                [
                    'headcount_total' => $totalHeadcount,
                    'headcount_active' => $activeHeadcount,
                    'headcount_inactive' => $inactiveHeadcount,
                    'fte_total' => $fteTotal,
                    'full_time_count' => $fullTimeCount,
                    'part_time_count' => $partTimeCount,
                    'contractor_count' => $contractorCount,
                    'new_hires_count' => $newHiresCount,
                    'transfers_in_count' => 0,
                    'transfers_out_count' => 0,
                    'promotions_count' => 0,
                    'terminations_count' => $terminationsCount,
                    'voluntary_exits_count' => $voluntaryExitsCount,
                    'involuntary_exits_count' => $involuntaryExitsCount,
                    'by_department' => $byDepartment,
                    'by_branch' => $byBranch,
                    'by_job_grade' => $byJobGrade,
                    'by_employment_type' => [
                        'full_time' => $fullTimeCount,
                        'part_time' => $partTimeCount,
                        'contractor' => $contractorCount,
                    ],
                    'by_tenure_band' => $byTenureBand,
                    'by_gender' => $byGender,
                    'payload' => [
                        'snapshot_version' => '2.23.0',
                        'generated_by' => 'HcmWorkforceSnapshotService',
                    ],
                    'calculated_at' => now(),
                ]
            );

            $run->update([
                'status' => 'completed',
                'records_processed' => $totalHeadcount,
                'completed_at' => now(),
            ]);

            return $snapshot;
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }
}
