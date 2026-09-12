<?php

namespace App\Domains\Benefits\Enums;

enum BenefitCategoryType: string
{
    case HEALTH_INSURANCE = 'health_insurance';
    case LIFE_INSURANCE = 'life_insurance';
    case ACCIDENTAL_INSURANCE = 'accidental_insurance';
    case DENTAL = 'dental';
    case VISION = 'vision';
    case MEDICAL_ALLOWANCE = 'medical_allowance';
    case MEAL = 'meal';
    case TRANSPORT = 'transport';
    case COMMUNICATION = 'communication';
    case RETIREMENT = 'retirement';
    case WELLNESS = 'wellness';
    case EAP = 'eap';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HEALTH_INSURANCE => 'Health Insurance',
            self::LIFE_INSURANCE => 'Life Insurance',
            self::ACCIDENTAL_INSURANCE => 'Accidental Insurance',
            self::DENTAL => 'Dental Benefit',
            self::VISION => 'Vision Benefit',
            self::MEDICAL_ALLOWANCE => 'Medical Allowance',
            self::MEAL => 'Meal Voucher / Allowance',
            self::TRANSPORT => 'Transport Benefit',
            self::COMMUNICATION => 'Communication Benefit',
            self::RETIREMENT => 'Retirement / Pension',
            self::WELLNESS => 'Wellness Program',
            self::EAP => 'Employee Assistance Program',
            self::OTHER => 'Other Benefit',
        };
    }
}
