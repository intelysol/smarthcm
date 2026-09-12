<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\ExpenseExchangeRate;
use App\Domains\Expenses\Services\Contracts\ExchangeRateProviderInterface;
use Carbon\Carbon;

class ExchangeRateService implements ExchangeRateProviderInterface
{
    public function getExchangeRate(string $tenantId, string $fromCurrency, string $toCurrency, ?string $date = null): float
    {
        if (strtoupper($fromCurrency) === strtoupper($toCurrency)) {
            return 1.000000;
        }

        $queryDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $rate = ExpenseExchangeRate::query()
            ->where('tenant_id', $tenantId)
            ->where('from_currency', strtoupper($fromCurrency))
            ->where('to_currency', strtoupper($toCurrency))
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $queryDate)
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($rate) {
            return (float) $rate->rate;
        }

        // Try inverse rate
        $inverse = ExpenseExchangeRate::query()
            ->where('tenant_id', $tenantId)
            ->where('from_currency', strtoupper($toCurrency))
            ->where('to_currency', strtoupper($fromCurrency))
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $queryDate)
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($inverse && (float) $inverse->rate > 0) {
            return round(1.0 / (float) $inverse->rate, 6);
        }

        return 1.000000; // default 1:1 fallback
    }

    public function convertAmount(float $amount, float $exchangeRate): float
    {
        return round($amount * $exchangeRate, 4);
    }
}
