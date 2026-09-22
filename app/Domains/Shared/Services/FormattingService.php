<?php

declare(strict_types=1);

namespace App\Domains\Shared\Services;

use App\Domains\TenantAdmin\Services\TenantLocalizationService;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class FormattingService
{
    public function __construct(
        protected ?TenantLocalizationService $localizationService = null
    ) {
        $this->localizationService = $localizationService ?? app(TenantLocalizationService::class);
    }

    /**
     * Resolve active tenant localization profile
     */
    protected function getActiveProfile(?string $tenantId = null): array
    {
        $tid = $tenantId ?? auth()->user()?->tenant_id;
        if ($tid && $this->localizationService) {
            try {
                $loc = $this->localizationService->getLocalization($tid);
                return [
                    'currency_symbol' => $loc->currency_symbol ?? '$',
                    'currency' => $loc->currency ?? 'USD',
                    'date_format' => $loc->date_format ?? 'Y-m-d',
                    'time_format' => $loc->time_format ?? 'H:i',
                    'timezone' => $loc->timezone ?? 'UTC',
                ];
            } catch (\Throwable $e) {
                // Fallback to sensible defaults
            }
        }

        return [
            'currency_symbol' => '$',
            'currency' => 'USD',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'timezone' => 'UTC',
        ];
    }

    /**
     * Format a date value
     */
    public function formatDate(mixed $value, ?string $tenantId = null): string
    {
        if (empty($value)) {
            return '—';
        }

        $profile = $this->getActiveProfile($tenantId);
        $date = $value instanceof CarbonInterface ? $value : Carbon::parse($value);

        if (!empty($profile['timezone'])) {
            $date = $date->setTimezone($profile['timezone']);
        }

        return $date->format($profile['date_format']);
    }

    /**
     * Format a time value
     */
    public function formatTime(mixed $value, ?string $tenantId = null): string
    {
        if (empty($value)) {
            return '—';
        }

        $profile = $this->getActiveProfile($tenantId);
        $date = $value instanceof CarbonInterface ? $value : Carbon::parse($value);

        if (!empty($profile['timezone'])) {
            $date = $date->setTimezone($profile['timezone']);
        }

        return $date->format($profile['time_format']);
    }

    /**
     * Format a datetime value
     */
    public function formatDateTime(mixed $value, ?string $tenantId = null): string
    {
        if (empty($value)) {
            return '—';
        }

        $profile = $this->getActiveProfile($tenantId);
        $date = $value instanceof CarbonInterface ? $value : Carbon::parse($value);

        if (!empty($profile['timezone'])) {
            $date = $date->setTimezone($profile['timezone']);
        }

        return $date->format($profile['date_format'] . ' ' . $profile['time_format']);
    }

    /**
     * Format currency amount
     */
    public function formatCurrency(float|int|string|null $amount, ?string $symbol = null, ?string $tenantId = null): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        $profile = $this->getActiveProfile($tenantId);
        $sym = $symbol ?? $profile['currency_symbol'];
        $num = is_numeric($amount) ? (float) $amount : 0.0;

        return $sym . ' ' . number_format($num, 2);
    }

    /**
     * Format standard number
     */
    public function formatNumber(float|int|string|null $value, int $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $num = is_numeric($value) ? (float) $value : 0.0;
        return number_format($num, $decimals);
    }

    /**
     * Format percentage
     */
    public function formatPercentage(float|int|string|null $value, int $decimals = 1): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $num = is_numeric($value) ? (float) $value : 0.0;
        return number_format($num, $decimals) . '%';
    }

    /**
     * Format duration in minutes into hours and minutes
     */
    public function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;

        if ($hours > 0 && $rem > 0) {
            return "{$hours}h {$rem}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }

        return "{$rem}m";
    }
}
