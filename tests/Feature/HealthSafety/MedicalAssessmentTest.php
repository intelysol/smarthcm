<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmMedicalAssessment;
use App\Domains\HealthSafety\Models\HcmMedicalProvider;
use App\Domains\HealthSafety\Services\MedicalAssessmentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_and_complete_medical_assessment(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Tech BU',
            'code' => 'BU-TECH',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-TECH',
            'department_name' => 'IT Operations',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-HLT-02',
            'employee_number' => 'EMP-HLT-02',
            'first_name' => 'David',
            'last_name' => 'Miller',
            'official_email' => 'david.m@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $provider = HcmMedicalProvider::create([
            'tenant_id' => $tenant->id,
            'name' => 'CarePlus Occupational Clinic',
            'provider_type' => 'clinic',
            'country' => 'US',
            'is_verified' => true,
        ]);

        $service = app(MedicalAssessmentService::class);

        // Schedule
        $assessment = $service->scheduleAssessment([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'assessment_type' => 'pre_placement',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'provider_id' => $provider->id,
        ]);

        $this->assertEquals('scheduled', $assessment->status);

        // Complete
        $completed = $service->completeAssessment($assessment, [
            'actual_date' => now()->toDateString(),
            'fitness_outcome' => 'fit',
            'medical_notes' => 'Patient has 20/20 corrected vision, normal cardiovascular metrics.',
            'examiner_name' => 'Dr. Lisa Stone',
        ]);

        $this->assertEquals('completed', $completed->status);
        $this->assertEquals('fit', $completed->fitness_outcome);
        $this->assertNotNull($completed->fitnessRecord);
        $this->assertEquals('fit', $completed->fitnessRecord->status);
    }
}
