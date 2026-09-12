<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Enums\PeriodStatus;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollPeriodLock;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class PayrollPeriodService
{
    public function createPeriod(string $tenantId, array $data, ?User $actor = null): PayrollPeriod
    {
        return PayrollPeriod::query()->create([
            'tenant_id' => $tenantId,
            'payroll_calendar_id' => $data['payroll_calendar_id'] ?? null,
            'payroll_legal_entity_id' => $data['payroll_legal_entity_id'] ?? null,
            'period_name' => $data['period_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'cutoff_date' => $data['cutoff_date'] ?? null,
            'payment_date' => $data['payment_date'] ?? null,
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'status' => PeriodStatus::OPEN->value,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    public function lockPeriod(PayrollPeriod $period, User $actor, ?string $reason = null): PayrollPeriod
    {
        if ($period->isLocked()) {
            throw ValidationException::withMessages([
                'period' => "Payroll period {$period->period_name} is already locked.",
            ]);
        }

        $prevStatus = $period->status;
        $period->update([
            'status' => PeriodStatus::LOCKED->value,
            'lock_date' => now(),
            'locked_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        PayrollPeriodLock::query()->create([
            'tenant_id' => $period->tenant_id,
            'payroll_period_id' => $period->id,
            'action' => 'locked',
            'previous_status' => $prevStatus,
            'new_status' => PeriodStatus::LOCKED->value,
            'reason' => $reason ?? 'Period cutoff lock enforced.',
            'acted_by' => $actor->id,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return $period;
    }

    public function reopenPeriod(PayrollPeriod $period, User $actor, string $reason): PayrollPeriod
    {
        if (! $period->isLocked()) {
            throw ValidationException::withMessages([
                'period' => "Payroll period {$period->period_name} is not locked.",
            ]);
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'An explicit audit reason is required to reopen a locked payroll period.',
            ]);
        }

        $prevStatus = $period->status;
        $period->update([
            'status' => PeriodStatus::UNDER_REVIEW->value,
            'lock_date' => null,
            'locked_by' => null,
            'updated_by' => $actor->id,
        ]);

        PayrollPeriodLock::query()->create([
            'tenant_id' => $period->tenant_id,
            'payroll_period_id' => $period->id,
            'action' => 'reopened',
            'previous_status' => $prevStatus,
            'new_status' => PeriodStatus::UNDER_REVIEW->value,
            'reason' => $reason,
            'acted_by' => $actor->id,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return $period;
    }
}
