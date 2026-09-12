<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\PayrollCalendar;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class PayrollCalendarService
{
    public function createCalendar(string $tenantId, array $data, ?User $actor = null): PayrollCalendar
    {
        if (! empty($data['is_default'])) {
            PayrollCalendar::query()->where('tenant_id', $tenantId)->update(['is_default' => false]);
        }

        return PayrollCalendar::query()->create([
            'tenant_id' => $tenantId,
            'payroll_legal_entity_id' => $data['payroll_legal_entity_id'] ?? null,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'frequency' => $data['frequency'] ?? 'monthly',
            'period_start_day' => $data['period_start_day'] ?? 1,
            'cutoff_day_offset' => $data['cutoff_day_offset'] ?? 25,
            'pay_day_offset' => $data['pay_day_offset'] ?? 5,
            'description' => $data['description'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    /**
     * Compute period dates (start, end, cutoff, pay date) for a given month/year.
     *
     * @return array{start_date: string, end_date: string, cutoff_date: string, payment_date: string}
     */
    public function calculatePeriodDates(PayrollCalendar $calendar, int $year, int $month): array
    {
        $start = CarbonImmutable::create($year, $month, min($calendar->period_start_day, 28));
        $end = $start->endOfMonth();

        $cutoffDay = min((int) $calendar->cutoff_day_offset, (int) $end->day);
        $cutoff = CarbonImmutable::create($year, $month, $cutoffDay);

        $payDate = $start->addMonth()->startOfMonth()->addDays(max(0, (int) $calendar->pay_day_offset - 1));

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'cutoff_date' => $cutoff->toDateString(),
            'payment_date' => $payDate->toDateString(),
        ];
    }
}
