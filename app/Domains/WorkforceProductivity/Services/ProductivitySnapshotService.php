<?php

namespace App\Domains\WorkforceProductivity\Services;

use App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface;
use App\Domains\WorkforceProductivity\Models\HcmProductivityAudit;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Models\HcmProductivitySnapshot;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductivitySnapshotService
{
    public function __construct(
        protected ProductivityCalculationInterface $calculator
    ) {}

    /**
     * Build an immutable periodic productivity snapshot with idempotency check.
     */
    public function buildSnapshot(
        string $tenantId,
        string $periodType,
        string $periodName,
        string $startDate,
        string $endDate,
        ?string $idempotencyKey = null,
        ?int $userId = null
    ): HcmProductivitySnapshot {
        if ($idempotencyKey) {
            $existing = HcmProductivitySnapshot::where('tenant_id', $tenantId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        // Aggregate all measurements in the period
        $measurements = HcmProductivityMeasurement::where('tenant_id', $tenantId)
            ->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate)
            ->get();

        $totalOutput = (float) $measurements->sum('output_volume');
        $totalLaborHours = (float) $measurements->sum('labor_hours');
        $totalProductiveHours = (float) $measurements->sum('productive_hours');
        $totalLaborCost = (float) $measurements->sum('labor_cost');
        $totalAvailableHours = (float) $measurements->sum('available_hours');

        $avgRate = $this->calculator->calculateRate($totalOutput, $totalProductiveHours);
        $avgUtil = $this->calculator->calculateUtilization($totalProductiveHours, $totalAvailableHours);
        $avgUnitCost = $this->calculator->calculateUnitCost($totalLaborCost, $totalOutput);

        $headcount = $measurements->pluck('employee_id')->filter()->unique()->count() ?: 10;
        $totalFte = round($totalLaborHours / 160.0, 2);

        $snapshotNumber = 'SNAP-' . strtoupper(Str::random(10));

        $snapshot = HcmProductivitySnapshot::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'snapshot_number' => $snapshotNumber,
            'period_type' => $periodType,
            'period_name' => $periodName,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'version' => 1,
            'status' => 'draft',
            'total_output' => $totalOutput,
            'total_labor_hours' => $totalLaborHours,
            'total_productive_hours' => $totalProductiveHours,
            'total_labor_cost' => $totalLaborCost,
            'average_productivity_rate' => $avgRate,
            'average_utilization_rate' => $avgUtil,
            'average_cost_per_unit' => $avgUnitCost,
            'total_fte' => $totalFte,
            'headcount' => $headcount,
            'idempotency_key' => $idempotencyKey,
            'created_by' => $userId,
        ]);

        HcmProductivityAudit::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'action' => 'CREATE_SNAPSHOT',
            'target_type' => HcmProductivitySnapshot::class,
            'target_id' => $snapshot->id,
            'user_id' => $userId,
            'changes' => ['snapshot_number' => $snapshotNumber, 'total_output' => $totalOutput],
            'created_at' => now(),
        ]);

        return $snapshot;
    }

    /**
     * Lock an approved snapshot making it immutable.
     */
    public function lockSnapshot(string $snapshotId, ?int $userId = null): HcmProductivitySnapshot
    {
        $snapshot = HcmProductivitySnapshot::findOrFail($snapshotId);
        $snapshot->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);

        HcmProductivityAudit::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $snapshot->tenant_id,
            'action' => 'LOCK_SNAPSHOT',
            'target_type' => HcmProductivitySnapshot::class,
            'target_id' => $snapshot->id,
            'user_id' => $userId,
            'changes' => ['status' => 'locked'],
            'created_at' => now(),
        ]);

        return $snapshot;
    }
}
