<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmWorkplaceExposure;
use App\Domains\HealthSafety\Services\WorkplaceExposureService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkplaceExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_exposure_and_auto_detect_threshold_exceeded(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Industrial BU',
            'code' => 'BU-IND',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-IND',
            'department_name' => 'Chemical Plant',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-EXP-01',
            'employee_number' => 'EMP-EXP-01',
            'first_name' => 'Walter',
            'last_name' => 'White',
            'official_email' => 'walter.w@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(WorkplaceExposureService::class);

        // 1. Below threshold
        $safeExposure = $service->recordExposure([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'hazard_type' => 'noise',
            'substance_or_agent' => 'Stamping Press Decibels (TWA)',
            'exposure_date' => now()->toDateString(),
            'measured_level' => 82.0,
            'unit_of_measure' => 'dBA',
            'exposure_limit_threshold' => 85.0,
        ]);

        $this->assertFalse($safeExposure->threshold_exceeded);
        $this->assertFalse($safeExposure->medical_surveillance_required);

        // 2. Above threshold
        $breachExposure = $service->recordExposure([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'hazard_type' => 'chemical',
            'substance_or_agent' => 'Toluene Vapors',
            'exposure_date' => now()->toDateString(),
            'measured_level' => 110.0,
            'unit_of_measure' => 'ppm',
            'exposure_limit_threshold' => 100.0,
        ]);

        $this->assertTrue($breachExposure->threshold_exceeded);
        $this->assertTrue($breachExposure->medical_surveillance_required);
    }
}
