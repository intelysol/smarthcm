<?php

namespace App\Domains\HealthSafety\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\HealthSafety\Models\HcmReturnToWorkCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ReturnToWorkService
{
    /**
     * Initiate a return-to-work case.
     */
    public function openCase(string|array $employeeIdOrData, array $data = [], ?User $actor = null): HcmReturnToWorkCase
    {
        if (is_array($employeeIdOrData)) {
            $data = $employeeIdOrData;
            $employeeId = $data['employee_id'];
        } else {
            $employeeId = $employeeIdOrData;
        }

        $employee = Employee::findOrFail($employeeId);

        return HcmReturnToWorkCase::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'case_number' => 'RTW-' . strtoupper(Str::random(8)),
            'absence_reason' => $data['absence_reason'] ?? ($data['work_capacity_status'] ?? 'medical_illness'),
            'incident_or_absence_date' => $data['incident_or_absence_date'] ?? ($data['leave_start_date'] ?? null),
            'target_return_date' => $data['target_return_date'] ?? null,
            'return_phase' => $data['return_phase'] ?? 'assessment_pending',
            'status' => 'open',
            'case_manager_id' => $data['case_manager_id'] ?? ($data['coordinator_employee_id'] ?? $actor?->id),
            'plan_summary' => $data['plan_summary'] ?? ($data['notes'] ?? null),
            'clearance_document_id' => $data['clearance_document_id'] ?? null,
        ]);
    }

    /**
     * Alias for openCase.
     */
    public function initiateCase(array $data, ?int $userId = null): HcmReturnToWorkCase
    {
        $actor = $userId ? User::find($userId) : null;
        return $this->openCase($data, [], $actor);
    }

    /**
     * Create or update phased return to work plan.
     */
    public function createPlan(HcmReturnToWorkCase $case, array $planData, ?int $userId = null): HcmReturnToWorkCase
    {
        $summary = !empty($planData['phased_plan']) && is_array($planData['phased_plan'])
            ? json_encode($planData['phased_plan'])
            : ($planData['notes'] ?? $case->plan_summary);

        $case->update([
            'target_return_date' => $planData['target_return_date'] ?? $case->target_return_date,
            'plan_summary' => $summary,
            'return_phase' => 'graduated_hours',
            'status' => 'plan_active',
        ]);

        return $case->fresh();
    }

    /**
     * Complete a return to work case.
     */
    public function completeCase(HcmReturnToWorkCase $case, array $data, ?int $userId = null): HcmReturnToWorkCase
    {
        $case->update([
            'actual_return_date' => $data['actual_return_date'] ?? now()->toDateString(),
            'return_phase' => 'full_duties',
            'status' => 'completed',
            'plan_summary' => ($case->plan_summary ?? '') . "\nClosure notes: " . ($data['notes'] ?? 'Completed'),
        ]);

        return $case->fresh();
    }

    /**
     * Advance return-to-work phase.
     */
    public function advancePhase(string $caseId, string $newPhase, ?string $notes = null): HcmReturnToWorkCase
    {
        $case = HcmReturnToWorkCase::findOrFail($caseId);

        $updates = ['return_phase' => $newPhase];
        if ($notes) {
            $updates['plan_summary'] = $case->plan_summary . "\nPhase updated to {$newPhase}: {$notes}";
        }

        if ($newPhase === 'full_duties') {
            $updates['actual_return_date'] = now()->toDateString();
            $updates['status'] = 'completed';
        }

        $case->update($updates);
        return $case->fresh();
    }

    /**
     * List return to work cases for tenant.
     */
    public function listCases(string $tenantId, array $filters = []): Collection
    {
        $query = HcmReturnToWorkCase::with(['employee', 'caseManager'])
            ->where('tenant_id', $tenantId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get cases for an employee.
     */
    public function getEmployeeCases(string $employeeId): Collection
    {
        return HcmReturnToWorkCase::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }
}

