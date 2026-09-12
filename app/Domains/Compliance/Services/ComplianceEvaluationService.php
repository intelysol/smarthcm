<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceExemption;
use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmEmployeeComplianceRequirement;
use App\Domains\Compliance\Models\HcmEmployeeComplianceSnapshot;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeRegistration;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComplianceEvaluationService
{
    public function __construct(
        protected ComplianceApplicabilityService $applicabilityService
    ) {}

    /**
     * Evaluate compliance for an employee and refresh their snapshot.
     */
    public function evaluateEmployee(string $employeeId): HcmEmployeeComplianceSnapshot
    {
        return DB::transaction(function () use ($employeeId) {
            $employee = Employee::findOrFail($employeeId);
            $tenantId = $employee->tenant_id;
            $today = Carbon::today();

            // 1. Determine applicable requirements
            $applicable = $this->applicabilityService->getApplicableRequirements($employee);

            // Sync assignments
            foreach ($applicable as $req) {
                HcmEmployeeComplianceRequirement::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'employee_id' => $employeeId,
                        'requirement_id' => $req->id,
                    ],
                    [
                        'status' => 'required',
                        'effective_from' => $today->toDateString(),
                    ]
                );
            }

            // Retrieve all current assigned requirements
            $assignments = HcmEmployeeComplianceRequirement::with(['requirement.type'])
                ->where('employee_id', $employeeId)
                ->get();

            $totalCount = $assignments->count();
            $compliantCount = 0;
            $expiringCount = 0;
            $expiredCount = 0;
            $pendingCount = 0;
            $exemptCount = 0;
            $scoreTotal = 0;
            $nextExpiring = null;

            foreach ($assignments as $assignment) {
                $req = $assignment->requirement;
                $status = $this->determineStatus($assignment, $employee, $today, $expiryDate);

                $assignment->update([
                    'status' => $status,
                    'fulfilled_at' => in_array($status, ['compliant', 'expiring', 'exempt']) ? ($assignment->fulfilled_at ?? now()) : null,
                ]);

                // Track next expiring item
                if ($expiryDate && Carbon::parse($expiryDate)->isFuture()) {
                    $daysRemaining = $today->diffInDays(Carbon::parse($expiryDate), false);
                    if ($nextExpiring === null || $daysRemaining < $nextExpiring['days_remaining']) {
                        $nextExpiring = [
                            'requirement_name' => $req?->name ?? 'Compliance Item',
                            'expiry_date' => Carbon::parse($expiryDate)->toDateString(),
                            'days_remaining' => (int) $daysRemaining,
                        ];
                    }
                }

                // Counters and Score calculation
                if ($status === 'compliant') {
                    $compliantCount++;
                    $scoreTotal += 100;
                } elseif ($status === 'exempt' || $status === 'waived') {
                    $exemptCount++;
                    $scoreTotal += 100;
                } elseif ($status === 'expiring') {
                    $expiringCount++;
                    $scoreTotal += 80;
                } elseif (in_array($status, ['pending', 'in_progress', 'submitted', 'under_review'])) {
                    $pendingCount++;
                    $scoreTotal += 50;
                } else { // expired, non_compliant, required
                    $expiredCount++;
                    $scoreTotal += 0;
                }
            }

            $overallScore = $totalCount > 0 ? round($scoreTotal / $totalCount, 2) : 100.00;

            // Determine overall status
            if ($expiredCount > 0) {
                $overallStatus = 'non_compliant';
            } elseif ($expiringCount > 0 || $pendingCount > 0) {
                $overallStatus = 'at_risk';
            } else {
                $overallStatus = 'compliant';
            }

            // Update or create snapshot
            return HcmEmployeeComplianceSnapshot::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'tenant_id' => $tenantId,
                    'overall_status' => $overallStatus,
                    'compliance_score' => $overallScore,
                    'total_requirements' => $totalCount,
                    'compliant_count' => $compliantCount,
                    'expiring_count' => $expiringCount,
                    'expired_count' => $expiredCount,
                    'pending_count' => $pendingCount,
                    'exempt_count' => $exemptCount,
                    'next_expiring_item' => $nextExpiring,
                    'calculated_at' => now(),
                ]
            );
        });
    }

    /**
     * Determine requirement status for an employee.
     */
    protected function determineStatus(
        HcmEmployeeComplianceRequirement $assignment,
        Employee $employee,
        Carbon $today,
        ?string &$expiryDateOut = null
    ): string {
        $expiryDateOut = null;
        $req = $assignment->requirement;
        if (!$req) {
            return 'not_required';
        }

        // 1. Check active approved exemption
        $exemption = HcmComplianceExemption::where('employee_id', $employee->id)
            ->where('requirement_id', $req->id)
            ->where('status', 'approved')
            ->whereDate('effective_from', '<=', $today->toDateString())
            ->whereDate('expiry_date', '>=', $today->toDateString())
            ->first();

        if ($exemption) {
            return 'exempt';
        }

        $typeCode = strtolower($req->type?->code ?? '');

        // 2. Work Permits
        if (str_contains($typeCode, 'permit')) {
            $permit = HcmEmployeeWorkPermit::where('employee_id', $employee->id)
                ->where('is_current', true)
                ->first();

            if ($permit) {
                $expiryDateOut = $permit->expiry_date?->toDateString();
                if ($permit->expiry_date && $permit->expiry_date->isPast()) {
                    return 'expired';
                }
                if ($permit->expiry_date && $today->diffInDays($permit->expiry_date, false) <= 60) {
                    return 'expiring';
                }
                return 'compliant';
            }
        }

        // 3. Visas / Residency
        if (str_contains($typeCode, 'visa') || str_contains($typeCode, 'residency')) {
            $visa = HcmEmployeeVisaRecord::where('employee_id', $employee->id)
                ->where('is_current', true)
                ->first();

            if ($visa) {
                $expiryDateOut = $visa->expiry_date?->toDateString();
                if ($visa->expiry_date && $visa->expiry_date->isPast()) {
                    return 'expired';
                }
                if ($visa->expiry_date && $today->diffInDays($visa->expiry_date, false) <= 60) {
                    return 'expiring';
                }
                return 'compliant';
            }
        }

        // 4. Professional / Occupational Licenses
        if (str_contains($typeCode, 'license')) {
            $license = HcmEmployeeLicense::where('employee_id', $employee->id)
                ->where('is_current', true)
                ->first();

            if ($license) {
                $expiryDateOut = $license->expiry_date?->toDateString();
                if ($license->expiry_date && $license->expiry_date->isPast()) {
                    return 'expired';
                }
                if ($license->expiry_date && $today->diffInDays($license->expiry_date, false) <= 60) {
                    return 'expiring';
                }
                return 'compliant';
            }
        }

        // 5. Registrations
        if (str_contains($typeCode, 'registration')) {
            $reg = HcmEmployeeRegistration::where('employee_id', $employee->id)
                ->first();

            if ($reg) {
                $expiryDateOut = $reg->expiry_date?->toDateString();
                if ($reg->expiry_date && $reg->expiry_date->isPast()) {
                    return 'expired';
                }
                return 'compliant';
            }
        }

        // 6. LMS Training integration
        if (str_contains($typeCode, 'training') || str_contains($typeCode, 'certification')) {
            if (DB::getSchemaBuilder()->hasTable('learning_certificates')) {
                $cert = DB::table('learning_certificates')
                    ->where('employee_id', $employee->id)
                    ->where('status', 'active')
                    ->first();

                if ($cert) {
                    $expiryDateOut = $cert->expires_at ?? null;
                    if ($expiryDateOut && Carbon::parse($expiryDateOut)->isPast()) {
                        return 'expired';
                    }
                    return 'compliant';
                }
            }
        }

        // If previously marked compliant / satisfied or fulfilled
        if ($assignment->status === 'compliant' || $assignment->fulfilled_at !== null) {
            return 'compliant';
        }

        // Default: If assignment is in progress or pending submission
        if (in_array($assignment->status, ['submitted', 'under_review'])) {
            return $assignment->status;
        }

        return 'non_compliant';
    }
}
