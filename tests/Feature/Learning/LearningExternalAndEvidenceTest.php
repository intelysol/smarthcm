<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Services\LearningExternalService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningExternalAndEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_training_submission_and_verification_workflow(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'IT', 'department_code' => 'IT']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-EXT-01',
            'employee_number' => 'EMP-EXT-01',
            'first_name' => 'David',
            'last_name' => 'Miller',
            'official_email' => 'david.m@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $adminUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $service = app(LearningExternalService::class);

        // 1. Submit external course with certificate evidence
        $record = $service->submitExternalRecord([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'provider_name' => 'Coursera / Stanford University',
            'course_title' => 'Machine Learning Specialization',
            'completion_date' => '2026-02-15',
            'duration_hours' => 40,
            'credits_earned' => 4.00,
            'credential_id' => 'STF-ML-8841',
        ], [
            [
                'evidence_type' => 'certificate',
                'file_path' => 'evidence/certificates/stf-ml-8841.pdf',
                'file_name' => 'stanford_ml_cert.pdf',
                'file_size' => 2048576,
            ],
        ]);

        $this->assertEquals('pending_verification', $record->status);
        $this->assertCount(1, $record->evidences);

        // 2. Admin Verifies and Approves Record
        $verified = $service->verifyExternalRecord($record, $adminUser->id, true, 'Verified against official Stanford online verification link.');
        $this->assertEquals('verified', $verified->status);
        $this->assertEquals($adminUser->id, $verified->verified_by);
        $this->assertNotNull($verified->verified_at);
    }
}
