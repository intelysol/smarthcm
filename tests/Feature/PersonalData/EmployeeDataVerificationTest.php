<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\PersonalData\Models\HcmEmployeeIdentifier;
use App\Domains\PersonalData\Services\EmployeeDataVerificationService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDataVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_verification_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $verifier = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-VER-01',
            'employee_number' => 'EMP-VER-01',
            'first_name' => 'Andrew',
            'last_name' => 'Jackson',
            'official_email' => 'andrew@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $identifier = HcmEmployeeIdentifier::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'identifier_type' => 'passport',
            'identifier_value' => 'AB1234567',
            'masked_value' => 'AB****67',
            'is_primary' => true,
            'verification_status' => 'unverified',
        ]);

        $service = app(EmployeeDataVerificationService::class);

        // 1. Initiate verification
        $verification = $service->initiateVerification(
            $employee->id,
            'identifier',
            $identifier->id,
            'document'
        );

        $this->assertEquals('pending', $verification->status);

        // 2. Complete verification
        $completed = $service->completeVerification(
            $verification->id,
            'verified',
            'Passport copy verified against original.',
            $verifier
        );

        $this->assertEquals('verified', $completed->status);
        $this->assertEquals($verifier->id, $completed->verified_by);

        // Verify identifier updated
        $identifier->refresh();
        $this->assertEquals('verified', $identifier->verification_status);
    }
}
