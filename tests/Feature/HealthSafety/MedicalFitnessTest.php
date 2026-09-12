<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmMedicalFitnessRecord;
use App\Domains\HealthSafety\Services\MedicalFitnessService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalFitnessTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_fitness_and_expiring_records_detection(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Logistics BU',
            'code' => 'BU-LOG',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-LOG',
            'department_name' => 'Fleet Logistics',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-DRV-01',
            'employee_number' => 'EMP-DRV-01',
            'first_name' => 'Carlos',
            'last_name' => 'Santana',
            'official_email' => 'carlos@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(MedicalFitnessService::class);

        $record = $service->recordFitness([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'status' => 'fit',
            'effective_date' => now()->toDateString(),
            'valid_until' => now()->addDays(20)->toDateString(),
            'certificate_number' => 'DOT-MED-9988',
        ]);

        $this->assertEquals('fit', $record->status);

        // Check expiring records within 30 days
        $expiring = $service->getExpiringRecords($tenant->id, 30);
        $this->assertCount(1, $expiring);
        $this->assertEquals($record->id, $expiring->first()->id);
    }
}
