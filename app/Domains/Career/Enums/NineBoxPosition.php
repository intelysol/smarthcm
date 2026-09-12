<?php

namespace App\Domains\Career\Enums;

enum NineBoxPosition: string
{
    case LowPerfLowPot = 'low_performance_low_potential';
    case LowPerfMedPot = 'low_performance_medium_potential';
    case LowPerfHighPot = 'low_performance_high_potential';

    case MedPerfLowPot = 'medium_performance_low_potential';
    case MedPerfMedPot = 'medium_performance_medium_potential';
    case MedPerfHighPot = 'medium_performance_high_potential';

    case HighPerfLowPot = 'high_performance_low_potential';
    case HighPerfMedPot = 'high_performance_medium_potential';
    case HighPerfHighPot = 'high_performance_high_potential';

    public function label(): string
    {
        return match ($this) {
            self::LowPerfLowPot => 'Risk / Underperformer',
            self::LowPerfMedPot => 'Dilemma / Inconsistent',
            self::LowPerfHighPot => 'Enigma / Rough Diamond',
            self::MedPerfLowPot => 'Effective / Solid Professional',
            self::MedPerfMedPot => 'Core Player / Key Contributor',
            self::MedPerfHighPot => 'High Potential / Growth Star',
            self::HighPerfLowPot => 'Trusted Professional / Subject Expert',
            self::HighPerfMedPot => 'High Performer / Future Leader',
            self::HighPerfHighPot => 'Star / Top Talent',
        };
    }
}
