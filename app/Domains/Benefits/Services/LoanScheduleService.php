<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Enums\InterestMethod;
use App\Domains\Benefits\Models\LoanApplication;
use App\Domains\Benefits\Models\LoanSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanScheduleService
{
    public function generateSchedule(
        LoanApplication $application,
        float $principal,
        int $tenureMonths,
        float $annualInterestRate,
        string $interestMethod,
        string $startDate,
        int $version = 1
    ): LoanSchedule {
        return DB::transaction(function () use ($application, $principal, $tenureMonths, $annualInterestRate, $interestMethod, $startDate, $version) {
            // Deactivate prior schedules if this is a new version
            $application->schedules()->update(['is_active' => false]);

            $totalInterest = 0.0;
            $installmentsData = [];

            $monthlyPrincipal = round($principal / max(1, $tenureMonths), 4);
            $runningBalance = $principal;
            $monthlyRate = ($annualInterestRate / 100) / 12;

            for ($i = 1; $i <= $tenureMonths; $i++) {
                $dueDate = Carbon::parse($startDate)->startOfMonth()->addMonthsNoOverflow($i)->endOfMonth()->toDateString();

                if ($interestMethod === InterestMethod::FLAT_RATE->value) {
                    $monthlyInterest = round(($principal * ($annualInterestRate / 100)) / $tenureMonths, 4);
                } elseif ($interestMethod === InterestMethod::REDUCING_BALANCE->value) {
                    $monthlyInterest = round($runningBalance * $monthlyRate, 4);
                } else {
                    $monthlyInterest = 0.0;
                }

                // Adjust last month rounding difference
                if ($i === $tenureMonths) {
                    $monthlyPrincipal = $runningBalance;
                }

                $totalInstallment = $monthlyPrincipal + $monthlyInterest;
                $newBalance = max(0, $runningBalance - $monthlyPrincipal);

                $installmentsData[] = [
                    'tenant_id' => $application->tenant_id,
                    'employee_id' => $application->employee_id,
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'principal_amount' => $monthlyPrincipal,
                    'interest_amount' => $monthlyInterest,
                    'total_installment' => $totalInstallment,
                    'paid_amount' => 0,
                    'balance_remaining' => $newBalance,
                    'status' => 'scheduled',
                ];

                $totalInterest += $monthlyInterest;
                $runningBalance = $newBalance;
            }

            $schedule = $application->schedules()->create([
                'tenant_id' => $application->tenant_id,
                'schedule_version' => $version,
                'total_principal' => $principal,
                'total_interest' => round($totalInterest, 4),
                'total_payable' => round($principal + $totalInterest, 4),
                'total_paid' => 0,
                'remaining_balance' => round($principal + $totalInterest, 4),
                'is_active' => true,
            ]);

            foreach ($installmentsData as $row) {
                $schedule->installments()->create($row);
            }

            return $schedule->fresh('installments');
        });
    }
}
