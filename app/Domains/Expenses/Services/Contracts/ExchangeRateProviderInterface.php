<?php

namespace App\Domains\Expenses\Services\Contracts;

interface ExchangeRateProviderInterface
{
    public function getExchangeRate(string $tenantId, string $fromCurrency, string $toCurrency, ?string $date = null): float;
}
