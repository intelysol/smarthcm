<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\SalaryAdvance;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryAdvanceService
{
    public function requestAdvance(Employee $employee, array $data): SalaryAdvance
    {
        $advanceNumber = 'ADV-' . strtoupper(uniqid());

        return SalaryAdvance::create([
            'tenant_id' => $employee->tenant_id,
            'advance_number' => $advanceNumber,
            'employee_id' => $employee->id,
            'requested_amount' => (float) $data['requested_amount'],
            'approved_amount' => 0,
            'repayment_months' => (int) ($data['repayment_months'] ?? 1),
            'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function approveAdvance(SalaryAdvance $advance, float $approvedAmount, User $approver): SalaryAdvance
    {
        return DB::transaction(function () use ($advance, $approvedAmount, $approver) {
            $advance->update([
                'status' => 'approved',
                'approved_amount' => $approvedAmount,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Create recovery installments
            $months = max(1, $advance->repayment_months);
            $monthlyRecovery = round($approvedAmount / $months, 4);

            for ($i = 1; $i <= $months; $i++) {
                $dueDate = Carbon::parse($advance->effective_date)->startOfMonth()->addMonthsNoOverflow($i)->endOfMonth()->toDateString();
                $advance->schedules()->create([
                    'tenant_id' => $advance->tenant_id,
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'amount' => ($i === $months) ? ($approvedAmount - ($monthlyRecovery * ($months - 1))) : $monthlyRecovery,
                    'status' => 'scheduled',
                ]);
            }

            return $advance->fresh('schedules');
        });
    }
}
