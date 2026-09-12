<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Services\ProfessionalLicenseService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalLicenseLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_license_and_regulatory_registration(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-LIC-01',
            'employee_number' => 'EMP-LIC-01',
            'first_name' => 'Gregory',
            'last_name' => 'House',
            'official_email' => 'house@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(ProfessionalLicenseService::class);

        // 1. Professional License
        $license = $service->addLicense((string) $employee->id, [
            'license_name' => 'State Board Medical License',
            'license_number' => 'MD-NJ-10928',
            'issuing_authority' => 'New Jersey Board of Medical Examiners',
            'state_province' => 'NJ',
            'country' => 'USA',
            'issue_date' => now()->subYears(3)->toDateString(),
            'effective_from' => now()->subYears(3)->toDateString(),
            'expiry_date' => now()->addYear()->toDateString(),
        ], $user);

        $this->assertDatabaseHas('hcm_employee_licenses', [
            'id' => $license->id,
            'license_number' => 'MD-NJ-10928',
            'issuing_authority' => 'New Jersey Board of Medical Examiners',
        ]);

        // 2. Regulatory Registration
        $reg = $service->addRegistration((string) $employee->id, [
            'registration_type' => 'Federal DEA Registration',
            'authority_name' => 'Drug Enforcement Administration (DEA)',
            'registration_number' => 'DEA-9988221',
            'registration_date' => now()->subYear()->toDateString(),
            'expiry_date' => now()->addYears(2)->toDateString(),
        ], $user);

        $this->assertDatabaseHas('hcm_employee_registrations', [
            'id' => $reg->id,
            'registration_number' => 'DEA-9988221',
            'authority_name' => 'Drug Enforcement Administration (DEA)',
        ]);
    }
}
