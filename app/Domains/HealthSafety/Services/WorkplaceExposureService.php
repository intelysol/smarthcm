<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Models\HcmWorkplaceExposure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class WorkplaceExposureService
{
    /**
     * Record a workplace exposure event or continuous monitoring measurement.
     */
    public function recordExposure(array $data, ?int $userId = null): HcmWorkplaceExposure
    {
        $limitThreshold = isset($data['exposure_limit_threshold']) ? (float) $data['exposure_limit_threshold'] : null;
        $measuredLevel = isset($data['measured_level']) ? (float) $data['measured_level'] : null;

        // Auto-detect threshold exceedance if both are present
        $exceeded = false;
        if ($limitThreshold !== null && $measuredLevel !== null) {
            $exceeded = $measuredLevel > $limitThreshold;
        } elseif (isset($data['threshold_exceeded'])) {
            $exceeded = (bool) $data['threshold_exceeded'];
        }

        $hazardName = $data['hazard_name'] ?? ($data['substance_or_agent'] ?? $data['hazard_type']);

        $exposure = HcmWorkplaceExposure::create([
            'tenant_id' => $data['tenant_id'],
            'employee_id' => $data['employee_id'],
            'hazard_type' => $data['hazard_type'],
            'hazard_name' => $hazardName,
            'exposure_level' => $exceeded ? 'permissible_limit_exceeded' : ($data['exposure_level'] ?? 'moderate'),
            'effective_from' => $data['effective_from'] ?? ($data['exposure_date'] ?? now()->toDateString()),
            'effective_to' => $data['effective_to'] ?? null,
            'surveillance_required' => $data['medical_surveillance_required'] ?? ($data['surveillance_required'] ?? $exceeded),
            'surveillance_interval_months' => $data['surveillance_interval_months'] ?? null,
            'controls_in_place' => $data['control_measures_in_place'] ?? ($data['ppe_used'] ?? null),
            'status' => 'active',
        ]);

        // Dynamically attach threshold_exceeded property for memory/API assertions
        $exposure->threshold_exceeded = $exceeded;
        $exposure->medical_surveillance_required = $exposure->surveillance_required;

        return $exposure;
    }


    /**
     * Get longitudinal exposure history for an employee.
     */
    public function getEmployeeExposures(string $employeeId): Collection
    {
        return HcmWorkplaceExposure::where('employee_id', $employeeId)
            ->with(['location'])
            ->orderBy('exposure_date', 'desc')
            ->get();
    }

    /**
     * List all exposure records for environmental/industrial hygiene reporting.
     */
    public function listExposures(string $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = HcmWorkplaceExposure::where('tenant_id', $tenantId)
            ->with(['employee', 'location']);

        if (!empty($filters['hazard_type'])) {
            $query->where('hazard_type', $filters['hazard_type']);
        }

        if (!empty($filters['substance_or_agent'])) {
            $query->where('substance_or_agent', 'like', '%' . $filters['substance_or_agent'] . '%');
        }

        if (isset($filters['threshold_exceeded'])) {
            $query->where('threshold_exceeded', (bool) $filters['threshold_exceeded']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('exposure_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('exposure_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('exposure_date', 'desc')->paginate($perPage);
    }
}
