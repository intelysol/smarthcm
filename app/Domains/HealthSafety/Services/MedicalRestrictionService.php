<?php

namespace App\Domains\HealthSafety\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\HealthSafety\Models\HcmMedicalRestriction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MedicalRestrictionService
{
    public function __construct(
        protected HealthSecurityService $securityService
    ) {}

    /**
     * Add an operational work restriction.
     */
    public function addRestriction(string|array $employeeIdOrData, array $data = [], ?User $actor = null): HcmMedicalRestriction
    {
        if (is_array($employeeIdOrData)) {
            $data = $employeeIdOrData;
            $employeeId = $data['employee_id'];
        } else {
            $employeeId = $employeeIdOrData;
        }

        $employee = Employee::findOrFail($employeeId);

        return HcmMedicalRestriction::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'fitness_record_id' => $data['fitness_record_id'] ?? null,
            'restriction_type' => $data['restriction_type'] ?? 'physical_workload',
            'title' => $data['title'] ?? 'Medical Restriction',
            'operational_description' => $data['operational_description'],
            'medical_rationale_restricted' => $data['medical_rationale_restricted'] ?? null,
            'effective_from' => $data['effective_from'] ?? ($data['start_date'] ?? now()->toDateString()),
            'effective_to' => $data['effective_to'] ?? ($data['end_date'] ?? null),
            'is_permanent' => (bool) ($data['is_permanent'] ?? false),
            'status' => 'active',
            'issued_by' => $actor?->id,
            'document_id' => $data['document_id'] ?? null,
        ]);
    }

    /**
     * Alias for addRestriction.
     */
    public function createRestriction(array|string $dataOrEmp, array $data = [], ?int $userId = null): HcmMedicalRestriction
    {
        $actor = $userId ? User::find($userId) : null;
        return $this->addRestriction($dataOrEmp, $data, $actor);
    }

    /**
     * Lift or resolve restriction.
     */
    public function liftRestriction(HcmMedicalRestriction|string $restrictionOrId, ?string $reason = null, ?int $userId = null): HcmMedicalRestriction
    {
        $id = $restrictionOrId instanceof HcmMedicalRestriction ? $restrictionOrId->id : $restrictionOrId;
        return $this->resolveRestriction($id, 'resolved');
    }

    /**
     * Get active restrictions for an employee, formatted according to viewer permissions.
     */
    public function getActiveRestrictions(string $employeeId, ?User $viewer = null): array
    {
        $records = HcmMedicalRestriction::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', Carbon::today()->toDateString());
            })
            ->get();

        return $records->map(function (HcmMedicalRestriction $item) use ($viewer) {
            return $this->securityService->filterRestrictionForViewer($item, $viewer);
        })->values()->all();
    }

    /**
     * Get employee restrictions as collection.
     */
    public function getEmployeeRestrictions(string $employeeId): Collection
    {
        return HcmMedicalRestriction::where('employee_id', $employeeId)
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Resolve or revoke a restriction.
     */
    public function resolveRestriction(string $restrictionId, string $status = 'resolved'): HcmMedicalRestriction
    {
        $restriction = HcmMedicalRestriction::findOrFail($restrictionId);
        $restriction->update([
            'status' => $status,
            'effective_to' => now()->toDateString(),
        ]);

        return $restriction->fresh();
    }
}

