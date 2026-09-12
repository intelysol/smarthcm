<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Payroll\Enums\PeriodStatus;
use App\Domains\Payroll\Enums\RunStatus;
use App\Domains\Payroll\Enums\RunType;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollRunService
{
    public function __construct(
        protected PayrollCalculationEngine $calculationEngine,
        protected PayrollValidationService $validationService,
        protected PayrollVarianceService $varianceService
    ) {}

    public function createRun(PayrollPeriod $period, array $data, ?User $actor = null): PayrollRun
    {
        if ($period->isLocked()) {
            throw ValidationException::withMessages([
                'period' => "Cannot create run in locked payroll period {$period->period_name}.",
            ]);
        }

        $tenantId = $period->tenant_id;
        $runNumber = 'RUN-' . strtoupper(uniqid());

        return PayrollRun::query()->create([
            'tenant_id' => $tenantId,
            'payroll_period_id' => $period->id,
            'run_number' => $data['run_number'] ?? $runNumber,
            'name' => $data['name'] ?? "{$period->period_name} Run",
            'run_type' => $data['run_type'] ?? RunType::REGULAR->value,
            'payroll_legal_entity_id' => $period->payroll_legal_entity_id,
            'currency' => $period->currency,
            'status' => RunStatus::DRAFT->value,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    /**
     * Execute payroll calculation for all eligible employees in the run.
     */
    public function calculateRun(PayrollRun $run, ?User $actor = null): PayrollRun
    {
        $tenantId = $run->tenant_id;
        $period = $run->period;

        if ($period->isLocked() || ! $run->isModifiable()) {
            throw ValidationException::withMessages([
                'run' => "Cannot calculate locked or non-draft payroll run {$run->run_number}.",
            ]);
        }

        $run->update(['status' => RunStatus::CALCULATING->value]);

        // 1. Fetch eligible employees for this tenant and period
        $employees = Employee::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($period) {
                $q->whereNull('termination_date')
                    ->orWhereDate('termination_date', '>=', $period->start_date->toDateString());
            })
            ->where(function ($q) use ($period) {
                $q->whereNull('joining_date')
                    ->orWhereDate('joining_date', '<=', $period->end_date->toDateString());
            })
            ->where('employment_status', 'active')
            ->get();

        $grossTotal = 0.0;
        $earningsTotal = 0.0;
        $deductionTotal = 0.0;
        $taxTotal = 0.0;
        $employerCostTotal = 0.0;
        $netTotal = 0.0;

        foreach ($employees as $employee) {
            $snapshot = $this->calculationEngine->calculateEmployeePayroll($run, $employee);
            $grossTotal += (float) $snapshot->gross_pay;
            $deductionTotal += (float) $snapshot->total_deductions;
            $taxTotal += (float) $snapshot->total_tax;
            $employerCostTotal += (float) $snapshot->total_employer_cost;
            $netTotal += (float) $snapshot->net_pay;
        }

        $run->update([
            'employee_count' => $employees->count(),
            'gross_total' => $grossTotal,
            'earnings_total' => $grossTotal,
            'deduction_total' => $deductionTotal,
            'tax_total' => $taxTotal,
            'employer_cost_total' => $employerCostTotal,
            'net_total' => $netTotal,
            'status' => RunStatus::CALCULATED->value,
            'calculated_at' => now(),
            'updated_by' => $actor?->id,
        ]);

        // 2. Run automated validation rules
        $this->validationService->validateRun($run);

        // 3. Compute variance against previous cycle
        $this->varianceService->computeVariances($run);

        return $run->fresh(['calculationSnapshots', 'exceptions', 'variances']);
    }

    public function submitForReview(PayrollRun $run, ?User $actor = null): PayrollRun
    {
        if ($run->status !== RunStatus::CALCULATED->value && $run->status !== RunStatus::UNDER_REVIEW->value) {
            throw ValidationException::withMessages([
                'run' => 'Only calculated payroll runs can be submitted for review.',
            ]);
        }

        $run->update([
            'status' => RunStatus::UNDER_REVIEW->value,
            'updated_by' => $actor?->id,
        ]);

        return $run;
    }

    public function approveRun(PayrollRun $run, User $approver): PayrollRun
    {
        if ($run->period->isLocked()) {
            throw ValidationException::withMessages([
                'run' => 'Cannot approve payroll run in locked period.',
            ]);
        }

        // Check blocking exceptions
        $blockingExceptions = $run->exceptions()->where('severity', 'blocking')->where('is_resolved', false)->count();
        if ($blockingExceptions > 0) {
            throw ValidationException::withMessages([
                'run' => "Cannot approve payroll run with {$blockingExceptions} unresolved blocking exceptions.",
            ]);
        }

        $run->update([
            'status' => RunStatus::APPROVED->value,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'updated_by' => $approver->id,
        ]);

        return $run;
    }

    public function lockRun(PayrollRun $run, User $actor): PayrollRun
    {
        $run->update([
            'status' => RunStatus::LOCKED->value,
            'locked_at' => now(),
            'updated_by' => $actor->id,
        ]);

        return $run;
    }
}
