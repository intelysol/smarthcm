<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceAudit;
use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use App\Domains\Compliance\Models\HcmEmployeeComplianceSnapshot;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmployeeComplianceService
{
    public function __construct(
        protected ComplianceEvaluationService $evaluationService
    ) {}

    /**
     * Get aggregate compliance profile for an employee.
     */
    public function getComplianceProfile(string $employeeId): array
    {
        $employee = Employee::with(['department', 'designation', 'company'])->findOrFail($employeeId);

        // Ensure snapshot is up to date
        $snapshot = $this->evaluationService->evaluateEmployee($employeeId);

        $requirements = HcmEmployeeComplianceRequirement::with(['requirement.type'])
            ->where('employee_id', $employeeId)
            ->get();

        $permits = HcmEmployeeWorkPermit::where('employee_id', $employeeId)
            ->orderByDesc('effective_from')
            ->get();

        $visas = HcmEmployeeVisaRecord::where('employee_id', $employeeId)
            ->orderByDesc('effective_from')
            ->get();

        $licenses = HcmEmployeeLicense::where('employee_id', $employeeId)
            ->orderByDesc('effective_from')
            ->get();

        $audits = HcmComplianceAudit::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return [
            'employee' => $employee,
            'snapshot' => $snapshot,
            'requirements' => $requirements,
            'permits' => $permits,
            'visas' => $visas,
            'licenses' => $licenses,
            'timeline' => $audits,
        ];
    }

    /**
     * Get expiring items across tenant.
     */
    public function getUpcomingExpirations(string $tenantId, int $days = 60): Collection
    {
        $cutoff = Carbon::today()->addDays($days)->toDateString();
        $today = Carbon::today()->toDateString();
        $results = collect();

        // 1. Work Permits
        $permits = HcmEmployeeWorkPermit::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->whereBetween('expiry_date', [$today, $cutoff])
            ->get();

        foreach ($permits as $p) {
            $results->push([
                'type' => 'work_permit',
                'item_id' => $p->id,
                'employee_id' => $p->employee_id,
                'employee_name' => $p->employee?->fullName() ?? 'Employee',
                'title' => $p->permit_type . ' (' . $p->permit_number . ')',
                'expiry_date' => $p->expiry_date->toDateString(),
                'days_remaining' => Carbon::today()->diffInDays($p->expiry_date, false),
                'status' => $p->status,
            ]);
        }

        // 2. Visas
        $visas = HcmEmployeeVisaRecord::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->whereBetween('expiry_date', [$today, $cutoff])
            ->get();

        foreach ($visas as $v) {
            $results->push([
                'type' => 'visa',
                'item_id' => $v->id,
                'employee_id' => $v->employee_id,
                'employee_name' => $v->employee?->fullName() ?? 'Employee',
                'title' => $v->visa_type . ' (' . $v->visa_number . ')',
                'expiry_date' => $v->expiry_date->toDateString(),
                'days_remaining' => Carbon::today()->diffInDays($v->expiry_date, false),
                'status' => $v->status,
            ]);
        }

        // 3. Licenses
        $licenses = HcmEmployeeLicense::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->whereBetween('expiry_date', [$today, $cutoff])
            ->get();

        foreach ($licenses as $l) {
            $results->push([
                'type' => 'license',
                'item_id' => $l->id,
                'employee_id' => $l->employee_id,
                'employee_name' => $l->employee?->fullName() ?? 'Employee',
                'title' => $l->license_name . ' (' . $l->license_number . ')',
                'expiry_date' => $l->expiry_date->toDateString(),
                'days_remaining' => Carbon::today()->diffInDays($l->expiry_date, false),
                'status' => $l->status,
            ]);
        }

        return $results->sortBy('days_remaining')->values();
    }
}
