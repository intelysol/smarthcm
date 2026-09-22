<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Models\HcmTenantLocalization;

class TenantLocalizationService
{
    protected array $countryProfiles = [
        'PK' => [
            'country_code' => 'PK',
            'language' => 'en',
            'timezone' => 'Asia/Karachi',
            'currency' => 'PKR',
            'currency_symbol' => 'Rs',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'week_start' => 'MONDAY',
            'fiscal_year_start_month' => 7, // July in Pakistan
            'tax_identifier_type' => 'CNIC',
            'national_id_mask' => '#####-#######-#',
            'is_pakistan_statutory_enabled' => true,
        ],
        'AE' => [
            'country_code' => 'AE',
            'language' => 'en',
            'timezone' => 'Asia/Dubai',
            'currency' => 'AED',
            'currency_symbol' => 'AED',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'week_start' => 'MONDAY',
            'fiscal_year_start_month' => 1,
            'tax_identifier_type' => 'EmiratesID',
            'national_id_mask' => '###-####-#######-#',
            'is_pakistan_statutory_enabled' => false,
        ],
        'SA' => [
            'country_code' => 'SA',
            'language' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'currency' => 'SAR',
            'currency_symbol' => 'SAR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'week_start' => 'SUNDAY',
            'fiscal_year_start_month' => 1,
            'tax_identifier_type' => 'NationalID',
            'national_id_mask' => '##########',
            'is_pakistan_statutory_enabled' => false,
        ],
        'GB' => [
            'country_code' => 'GB',
            'language' => 'en',
            'timezone' => 'Europe/London',
            'currency' => 'GBP',
            'currency_symbol' => '£',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'week_start' => 'MONDAY',
            'fiscal_year_start_month' => 4, // April in UK
            'tax_identifier_type' => 'NINO',
            'national_id_mask' => 'AA######A',
            'is_pakistan_statutory_enabled' => false,
        ],
        'US' => [
            'country_code' => 'US',
            'language' => 'en',
            'timezone' => 'America/New_York',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'date_format' => 'm/d/Y',
            'time_format' => 'h:i A',
            'week_start' => 'SUNDAY',
            'fiscal_year_start_month' => 1,
            'tax_identifier_type' => 'SSN',
            'national_id_mask' => '###-##-####',
            'is_pakistan_statutory_enabled' => false,
        ],
    ];

    public function getLocalization(string $tenantId): HcmTenantLocalization
    {
        return HcmTenantLocalization::firstOrCreate(
            ['tenant_id' => $tenantId],
            $this->countryProfiles['PK']
        );
    }

    public function updateLocalization(string $tenantId, array $data): HcmTenantLocalization
    {
        $localization = $this->getLocalization($tenantId);

        // If country code changed and profile exists, merge profile defaults
        $country = $data['country_code'] ?? $localization->country_code;
        $profile = $this->countryProfiles[$country] ?? $this->countryProfiles['PK'];

        $localization->update(array_merge($profile, array_filter($data)));

        return $localization->fresh();
    }
}
