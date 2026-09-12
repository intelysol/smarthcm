<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmEmployeeAvailability;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\RosterConflictEngine;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningRequirement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScheduleEligibilityService
{
    public function __construct(
        protected RosterConflictEngine $conflictEngine
    ) {}

    /**
     * Comprehensive eligibility inspection for an employee on a given date and shift.
     *
     * @return array{
     *     is_eligible: bool,
     *     hard_violations: array<int, string>,
     *     soft_warnings: array<int, string>,
     *     qualification_details: array<string, mixed>
     * }
     */
    public function checkEligibility(
        Employee $employee,
        string $date,
        ?ShiftDefinition $shift = null,
        ?string $requiredSkillId = null,
        int $minSkillProficiency = 1,
        ?string $requiredPositionId = null,
        ?string $currentAssignmentId = null
    ): array {
        $hardViolations = [];
        $softWarnings = [];
        $qualificationDetails = [];

        $carbonDate = CarbonImmutable::parse($date);
        $dateStr = $carbonDate->toDateString();
        $tenantId = $employee->tenant_id;

        // 1. Employment Status Check
        if ($employee->employment_status && in_array(strtolower($employee->employment_status), ['terminated', 'resigned', 'suspended', 'inactive'], true)) {
            $hardViolations[] = "Employee is inactive ({$employee->employment_status}) on {$dateStr}.";
        }

        // 2. Approved Leave Check
        $hasApprovedLeave = false;
        $leaveTable = Schema::hasTable('leave_applications') ? 'leave_applications' : (Schema::hasTable('leave_requests') ? 'leave_requests' : null);
        if ($leaveTable) {
            $hasApprovedLeave = DB::table($leaveTable)
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $dateStr)
                ->where('end_date', '>=', $dateStr)
                ->exists();
        }

        if ($hasApprovedLeave) {
            $hardViolations[] = "Employee has approved leave on {$dateStr}.";
        }

        // 3. Employee Availability Block Check
        $availability = HcmEmployeeAvailability::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where(function ($q) use ($dateStr, $carbonDate) {
                $q->where('date', $dateStr)
                    ->orWhere(function ($sub) use ($carbonDate) {
                        $sub->where('is_recurring', true)
                            ->where('recurring_day_of_week', $carbonDate->dayOfWeek);
                    });
            })
            ->first();

        if ($availability) {
            if ($availability->availability_type === 'unavailable') {
                $hardViolations[] = "Employee is marked unavailable on {$dateStr}.";
            } elseif ($availability->availability_type === 'restricted') {
                $softWarnings[] = "Employee has restricted availability on {$dateStr}.";
            }
        }

        // 4. Skills & Proficiency Check
        if ($requiredSkillId) {
            $employeeSkill = EmployeeSkill::query()
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->where('skill_id', $requiredSkillId)
                ->first();

            if (! $employeeSkill) {
                $hardViolations[] = "Employee does not possess required skill [ID: {$requiredSkillId}].";
                $qualificationDetails['skill_matched'] = false;
            } elseif ($employeeSkill->current_level < $minSkillProficiency) {
                $hardViolations[] = "Employee skill proficiency ({$employeeSkill->current_level}) is below required level ({$minSkillProficiency}).";
                $qualificationDetails['skill_matched'] = false;
                $qualificationDetails['current_level'] = $employeeSkill->current_level;
            } else {
                $qualificationDetails['skill_matched'] = true;
                $qualificationDetails['current_level'] = $employeeSkill->current_level;
            }
        }

        // 5. Position Match Check (if position requirement specified)
        if ($requiredPositionId && $employee->position_id !== $requiredPositionId) {
            $softWarnings[] = "Employee primary position does not match required shift position.";
            $qualificationDetails['position_matched'] = false;
        } else {
            $qualificationDetails['position_matched'] = true;
        }

        // 6. Mandatory Compliance / Certification Expiry Check
        if (Schema::hasTable('learning_requirements')) {
            $expiredRequirements = LearningRequirement::query()
                ->where('tenant_id', $tenantId)
                ->where(function ($q) use ($employee) {
                    $q->where('target_id', $employee->id)
                        ->orWhere('employee_id', $employee->id);
                })
                ->where('status', 'expired')
                ->exists();

            if ($expiredRequirements) {
                $hardViolations[] = "Employee has mandatory certifications or compliance requirements that are expired.";
            }
        }

        // 7. Base Roster Conflicts (Overlaps, Rest Period, Max Weekly Hours)
        if ($shift) {
            $conflicts = $this->conflictEngine->detectConflicts($employee, $dateStr, $shift, $currentAssignmentId);
            foreach ($conflicts as $conflict) {
                if ($conflict['severity'] === 'blocking') {
                    $hardViolations[] = $conflict['message'];
                } else {
                    $softWarnings[] = $conflict['message'];
                }
            }
        }

        return [
            'is_eligible' => empty($hardViolations),
            'hard_violations' => $hardViolations,
            'soft_warnings' => $softWarnings,
            'qualification_details' => $qualificationDetails,
        ];
    }
}
