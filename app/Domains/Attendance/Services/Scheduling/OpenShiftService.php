<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmOpenShift;
use App\Domains\Attendance\Models\HcmOpenShiftBid;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OpenShiftService
{
    public function __construct(
        protected ScheduleEligibilityService $eligibilityService
    ) {}

    /**
     * Create an open shift.
     */
    public function createOpenShift(
        RosterPeriod $period,
        string $date,
        ShiftDefinition $shift,
        int $slots = 1,
        ?string $requiredSkillId = null,
        ?string $departmentId = null
    ): HcmOpenShift {
        return HcmOpenShift::query()->create([
            'tenant_id' => $period->tenant_id,
            'roster_period_id' => $period->id,
            'roster_date' => $date,
            'shift_definition_id' => $shift->id,
            'department_id' => $departmentId ?? $period->department_id,
            'required_skill_id' => $requiredSkillId,
            'slots_total' => $slots,
            'slots_filled' => 0,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Submit an employee bid for an open shift.
     */
    public function submitBid(HcmOpenShift $openShift, Employee $employee): HcmOpenShiftBid
    {
        if ($openShift->status !== 'open' || $openShift->slots_filled >= $openShift->slots_total) {
            throw ValidationException::withMessages([
                'open_shift' => ['Open shift is no longer available for bidding.'],
            ]);
        }

        $existingBid = HcmOpenShiftBid::query()
            ->where('open_shift_id', $openShift->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existingBid) {
            throw ValidationException::withMessages([
                'bid' => ['Employee has already submitted a bid for this open shift.'],
            ]);
        }

        $eligibility = $this->eligibilityService->checkEligibility(
            employee: $employee,
            date: $openShift->roster_date->toDateString(),
            shift: $openShift->shift,
            requiredSkillId: $openShift->required_skill_id
        );

        if (! $eligibility['is_eligible']) {
            throw ValidationException::withMessages([
                'bid_eligibility' => $eligibility['hard_violations'],
            ]);
        }

        $score = 100.00 - (count($eligibility['soft_warnings']) * 10);

        return HcmOpenShiftBid::query()->create([
            'tenant_id' => $openShift->tenant_id,
            'open_shift_id' => $openShift->id,
            'employee_id' => $employee->id,
            'bid_status' => 'submitted',
            'eligibility_score' => max(50.00, $score),
            'eligibility_details' => $eligibility,
        ]);
    }

    /**
     * Award an open shift to a winning bidder.
     */
    public function awardBid(HcmOpenShiftBid $bid, User $manager): RosterAssignment
    {
        $openShift = $bid->openShift;
        if (! $openShift || $openShift->status !== 'open') {
            throw ValidationException::withMessages([
                'award' => ['Open shift is no longer open.'],
            ]);
        }

        $assignment = RosterAssignment::query()->create([
            'tenant_id' => $openShift->tenant_id,
            'roster_period_id' => $openShift->roster_period_id,
            'employee_id' => $bid->employee_id,
            'roster_date' => $openShift->roster_date,
            'shift_definition_id' => $openShift->shift_definition_id,
            'department_id' => $openShift->department_id,
            'location_id' => $openShift->location_id,
            'assignment_status' => 'scheduled',
            'is_published' => true,
            'notes' => 'Assigned via Open Shift bidding: ' . $openShift->id,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $bid->update(['bid_status' => 'selected']);
        $openShift->increment('slots_filled');

        if ($openShift->slots_filled >= $openShift->slots_total) {
            $openShift->update(['status' => 'filled']);
            // Decline remaining bids
            HcmOpenShiftBid::query()
                ->where('open_shift_id', $openShift->id)
                ->where('id', '!=', $bid->id)
                ->where('bid_status', 'submitted')
                ->update(['bid_status' => 'declined']);
        }

        return $assignment;
    }
}
