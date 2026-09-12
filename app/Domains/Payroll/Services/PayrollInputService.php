<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollBonus;
use App\Domains\Payroll\Models\PayrollInput;
use App\Domains\Payroll\Models\PayrollInputLine;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollInputService
{
    /**
     * Collect and normalize inputs for an employee in a given payroll period.
     */
    public function collectInputsForEmployee(PayrollPeriod $period, Employee $employee, ?User $actor = null): PayrollInput
    {
        $tenantId = $period->tenant_id;

        /** @var PayrollInput $input */
        $input = PayrollInput::query()->firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'payroll_period_id' => $period->id,
                'employee_id' => $employee->id,
            ],
            [
                'status' => 'collected',
                'collected_by' => $actor?->id,
                'collected_at' => now(),
            ]
        );

        // Clear existing collected lines for idempotency
        $input->lines()->delete();

        // 1. Time & Attendance Inputs (Approved Timesheet)
        $timesheet = Timesheet::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', $period->start_date->toDateString())
            ->whereDate('end_date', '>=', $period->end_date->toDateString())
            ->whereIn('status', ['hr_approved', 'locked', 'exported'])
            ->first();

        if ($timesheet) {
            $regHours = round($timesheet->total_regular_minutes / 60, 2);
            $otHours = round($timesheet->total_approved_overtime_minutes / 60, 2);
            $absenceHours = round($timesheet->total_absence_minutes / 60, 2);

            if ($regHours > 0) {
                $input->lines()->create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'source_module' => 'attendance',
                    'source_entity_type' => Timesheet::class,
                    'source_entity_id' => $timesheet->id,
                    'input_type' => 'regular_hours',
                    'quantity' => $regHours,
                    'effective_date' => $period->start_date->toDateString(),
                    'approval_status' => 'approved',
                    'notes' => "Approved regular working time ({$regHours}h)",
                ]);
            }

            if ($otHours > 0) {
                $input->lines()->create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'source_module' => 'attendance',
                    'source_entity_type' => Timesheet::class,
                    'source_entity_id' => $timesheet->id,
                    'input_type' => 'overtime_hours',
                    'quantity' => $otHours,
                    'effective_date' => $period->end_date->toDateString(),
                    'approval_status' => 'approved',
                    'notes' => "Approved overtime duration ({$otHours}h)",
                ]);
            }

            if ($absenceHours > 0) {
                $input->lines()->create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'source_module' => 'attendance',
                    'source_entity_type' => Timesheet::class,
                    'source_entity_id' => $timesheet->id,
                    'input_type' => 'unpaid_absence_hours',
                    'quantity' => $absenceHours,
                    'effective_date' => $period->end_date->toDateString(),
                    'approval_status' => 'approved',
                    'notes' => "Unpaid absence ({$absenceHours}h)",
                ]);
            }
        }

        // 2. Approved Bonuses
        $bonuses = PayrollBonus::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('effective_date', '>=', $period->start_date->toDateString())
            ->whereDate('effective_date', '<=', $period->end_date->toDateString())
            ->get();

        foreach ($bonuses as $b) {
            $input->lines()->create([
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'source_module' => 'bonuses',
                'source_entity_type' => PayrollBonus::class,
                'source_entity_id' => $b->id,
                'input_type' => 'bonus',
                'quantity' => 1,
                'amount' => $b->amount,
                'currency' => $b->currency,
                'effective_date' => $b->effective_date->toDateString(),
                'approval_status' => 'approved',
                'notes' => $b->title,
            ]);
        }

        // 3. Employee Loans (if employee_loans table exists)
        if (Schema::hasTable('employee_loans')) {
            $loans = DB::table('employee_loans')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->where('outstanding', '>', 0)
                ->get();

            foreach ($loans as $l) {
                $installment = min((float) $l->installment_amount, (float) $l->outstanding);
                if ($installment > 0) {
                    $input->lines()->create([
                        'tenant_id' => $tenantId,
                        'employee_id' => $employee->id,
                        'source_module' => 'loans',
                        'source_entity_type' => 'App\Domains\Payroll\Models\EmployeeLoan',
                        'source_entity_id' => $l->id,
                        'input_type' => 'loan_installment',
                        'quantity' => 1,
                        'amount' => $installment,
                        'effective_date' => $period->end_date->toDateString(),
                        'approval_status' => 'approved',
                        'notes' => "Loan deduction ({$l->loan_type})",
                        'metadata' => ['loan_id' => $l->id, 'outstanding' => (float) $l->outstanding],
                    ]);
                }
            }
        }

        return $input->fresh('lines');
    }
}
