<?php

namespace App\Domains\Payroll\Enums;

enum RoundingMethod: string
{
    case HALF_UP = 'half_up';
    case HALF_DOWN = 'half_down';
    case BANKERS = 'bankers';
    case FLOOR = 'floor';
    case CEIL = 'ceil';

    public function round(float $value, int $precision = 2): float
    {
        return match ($this) {
            self::HALF_UP => round($value, $precision, PHP_ROUND_HALF_UP),
            self::HALF_DOWN => round($value, $precision, PHP_ROUND_HALF_DOWN),
            self::BANKERS => round($value, $precision, PHP_ROUND_HALF_EVEN),
            self::FLOOR => floor($value * (10 ** $precision)) / (10 ** $precision),
            self::CEIL => ceil($value * (10 ** $precision)) / (10 ** $precision),
        };
    }
}
