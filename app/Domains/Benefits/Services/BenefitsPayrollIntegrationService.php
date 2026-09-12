<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\LoanInstallment;
use App\Domains\Benefits\Models\RetirementEnrollment;
use App\Domains\Benefits\Models\SalaryAdvanceSchedule;
use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Models\PayrollInput;
use App\Domains\Payroll\Models\PayrollPeriod;

class BenefitsPayrollIntegrationService
{
    public function syncPayrollInputsForPeriod(PayrollPeriod $period, Employee $employee): array
    {
        $tenantId = $period->tenant_id;
        $periodDate = $period->end_date->toDateString();
        $generatedLines = [];

        $input = PayrollInput::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'payroll_period_id' => $period->id,
                'employee_id' => $employee->id,
            ],
            [
                'cutoff_date' => $period->cutoff_date ?? $period->end_date,
                'status' => 'draft',
            ]
        );

        // 1. Benefit Enrollments (Health / Life / Dental Insurance Deductions)
        $enrollments = BenefitEnrollment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'active'])
            ->whereDate('effective_from', '<=', $periodDate)
            ->where(function ($q) use ($periodDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $periodDate);
            })
            ->get();

        foreach ($enrollments as $enr) {
            if ((float) $enr->employee_contribution > 0) {
                $line = $input->lines()->create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employee->id,
                    'source_module' => 'benefits',
                    'source_entity_type' => BenefitEnrollment::class,
                    'source_entity_id' => $enr->id,
                    'input_type' => 'benefit_deduction',
                    'quantity' => 1,
                    'amount' => $enr->employee_contribution,
                    'currency' => $enr->currency,
                    'effective_date' => $periodDate,
                    'approval_status' => 'approved',
                    'notes' => "Benefit Deduction ({$enr->plan->name})",
                ]);
                $generatedLines[] = $line;
            }
        }

        // 2. Active Loan Installments Due in this period
        $loanInstallments = LoanInstallment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->where('status', 'scheduled')
            ->whereDate('due_date', '>=', $period->start_date->toDateString())
            ->whereDate('due_date', '<=', $period->end_date->toDateString())
            ->get();

        foreach ($loanInstallments as $inst) {
            $line = $input->lines()->create([
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'source_module' => 'loans',
                'source_entity_type' => LoanInstallment::class,
                'source_entity_id' => $inst->id,
                'input_type' => 'loan_installment',
                'quantity' => 1,
                'amount' => $inst->total_installment,
                'currency' => 'USD',
                'effective_date' => $periodDate,
                'approval_status' => 'approved',
                'notes' => "Loan Installment #{$inst->installment_number}",
            ]);
            $generatedLines[] = $line;
        }

        // 3. Salary Advance Schedules Due in this period
        $advances = SalaryAdvanceSchedule::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('advance', fn ($q) => $q->where('employee_id', $employee->id)->where('status', 'approved'))
            ->where('status', 'scheduled')
            ->whereDate('due_date', '>=', $period->start_date->toDateString())
            ->whereDate('due_date', '<=', $period->end_date->toDateString())
            ->get();

        foreach ($advances as $adv) {
            $line = $input->lines()->create([
                'tenant_id' => $tenantId,
                'employee_id' => $employee->id,
                'source_module' => 'advances',
                'source_entity_type' => SalaryAdvanceSchedule::class,
                'source_entity_id' => $adv->id,
                'input_type' => 'advance_repayment',
                'quantity' => 1,
                'amount' => $adv->amount,
                'currency' => 'USD',
                'effective_date' => $periodDate,
                'approval_status' => 'approved',
                'notes' => "Salary Advance Recovery",
            ]);
            $generatedLines[] = $line;
        }

        return $generatedLines;
    }
}
