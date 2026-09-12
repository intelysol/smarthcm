<?php

namespace App\Domains\Benefits\Enums;

enum LoanProductType: string
{
    case PERSONAL = 'personal';
    case EMERGENCY = 'emergency';
    case EDUCATION = 'education';
    case VEHICLE = 'vehicle';
    case HOUSING = 'housing';
    case MEDICAL = 'medical';
    case SALARY_ADVANCE = 'salary_advance';

    public function label(): string
    {
        return match ($this) {
            self::PERSONAL => 'Personal Loan',
            self::EMERGENCY => 'Emergency Loan',
            self::EDUCATION => 'Education Loan',
            self::VEHICLE => 'Vehicle Loan',
            self::HOUSING => 'Housing / Home Loan',
            self::MEDICAL => 'Medical Loan',
            self::SALARY_ADVANCE => 'Salary Advance',
        };
    }
}
