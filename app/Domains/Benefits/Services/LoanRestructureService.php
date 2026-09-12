<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanRestructure;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanRestructureService
{
    public function __construct(
        protected LoanScheduleService $scheduleService
    ) {}

    public function restructure(
        LoanApplication $application,
        int $newTenureMonths,
        float $newInterestRate,
        string $reason,
        User $approver
    ): LoanRestructure {
        return DB::transaction(function () use ($application, $newTenureMonths, $newInterestRate, $reason, $approver) {
            $activeSchedule = $application->activeSchedule;
            $oldVersion = $activeSchedule ? $activeSchedule->schedule_version : 1;
            $remainingPrincipal = $activeSchedule ? (float) $activeSchedule->remaining_balance : (float) $application->approved_amount;

            $newVersion = $oldVersion + 1;

            // Generate new schedule version
            $newSchedule = $this->scheduleService->generateSchedule(
                $application,
                $remainingPrincipal,
                $newTenureMonths,
                $newInterestRate,
                $application->interest_method,
                now()->toDateString(),
                $newVersion
            );

            $restructure = LoanRestructure::create([
                'tenant_id' => $application->tenant_id,
                'loan_application_id' => $application->id,
                'restructure_type' => 'extend_tenure',
                'previous_schedule_version' => $oldVersion,
                'new_schedule_version' => $newVersion,
                'previous_balance' => $remainingPrincipal,
                'new_balance' => (float) $newSchedule->total_payable,
                'reason' => $reason,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $restructure;
        });
    }
}
