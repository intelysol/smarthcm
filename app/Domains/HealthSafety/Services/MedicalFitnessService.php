<?php

namespace App\Domains\HealthSafety\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\HealthSafety\Models\HcmEmployeeHealthRequirement;
use App\Domains\HealthSafety\Models\HcmMedicalFitnessRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MedicalFitnessService
{
    /**
     * Record a fitness determination for an employee.
     */
    public function recordFitness(string|array $employeeIdOrData, array $data = [], ?User $actor = null): HcmMedicalFitnessRecord
    {
        if (is_array($employeeIdOrData)) {
            $data = $employeeIdOrData;
            $employeeId = $data['employee_id'];
        } else {
            $employeeId = $employeeIdOrData;
        }

        return DB::transaction(function () use ($employeeId, $data, $actor) {
            $employee = Employee::findOrFail($employeeId);

            // Mark previous current records as inactive
            HcmMedicalFitnessRecord::where('employee_id', $employeeId)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $effectiveFrom = isset($data['effective_from'])
                ? Carbon::parse($data['effective_from'])
                : (isset($data['effective_date']) ? Carbon::parse($data['effective_date']) : Carbon::today());

            $effectiveTo = isset($data['effective_to'])
                ? Carbon::parse($data['effective_to'])
                : (isset($data['valid_until']) ? Carbon::parse($data['valid_until']) : null);

            $status = $data['fitness_status'] ?? ($data['status'] ?? 'fit');

            $record = HcmMedicalFitnessRecord::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'assessment_id' => $data['assessment_id'] ?? null,
                'medical_provider_id' => $data['medical_provider_id'] ?? null,
                'fitness_status' => $status,
                'determined_date' => $data['determined_date'] ?? now()->toDateString(),
                'effective_from' => $effectiveFrom->toDateString(),
                'effective_to' => $effectiveTo?->toDateString(),
                'next_review_date' => $data['next_review_date'] ?? null,
                'certificate_reference' => $data['certificate_reference'] ?? ($data['certificate_number'] ?? null),
                'document_id' => $data['document_id'] ?? null,
                'has_restrictions' => (bool) ($data['has_restrictions'] ?? ($status === 'fit_with_restrictions')),
                'recorded_by' => $actor?->id,
                'summary_notes' => $data['summary_notes'] ?? ($data['operational_notes'] ?? null),
                'is_current' => true,
            ]);

            // Update associated health requirements if present
            if (!empty($data['health_requirement_id'])) {
                HcmEmployeeHealthRequirement::where('employee_id', $employeeId)
                    ->where('health_requirement_id', $data['health_requirement_id'])
                    ->update([
                        'status' => in_array($record->fitness_status, ['fit', 'fit_with_restrictions']) ? 'compliant' : 'expired',
                        'completed_date' => $record->determined_date->toDateString(),
                        'expiry_date' => $record->effective_to?->toDateString(),
                    ]);
            }

            return $record;
        });
    }

    /**
     * Get expiring fitness records within days ahead.
     */
    public function getExpiringRecords(string $tenantId, int $daysAhead = 30): Collection
    {
        $today = Carbon::today()->toDateString();
        $target = Carbon::today()->addDays($daysAhead)->toDateString();

        return HcmMedicalFitnessRecord::where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->whereNotNull('effective_to')
            ->whereBetween('effective_to', [$today, $target])
            ->get();
    }

    /**
     * Get current fitness status for an employee.
     */
    public function getCurrentFitness(string $employeeId): ?HcmMedicalFitnessRecord
    {
        return HcmMedicalFitnessRecord::with(['provider', 'restrictions'])
            ->where('employee_id', $employeeId)
            ->where('is_current', true)
            ->first();
    }

    /**
     * Alias for getCurrentFitness.
     */
    public function getActiveFitness(string $employeeId): ?HcmMedicalFitnessRecord
    {
        return $this->getCurrentFitness($employeeId);
    }

    /**
     * Get fitness history.
     */
    public function getFitnessHistory(string $employeeId): Collection
    {
        return HcmMedicalFitnessRecord::with('provider')
            ->where('employee_id', $employeeId)
            ->orderByDesc('effective_from')
            ->get();
    }
}

