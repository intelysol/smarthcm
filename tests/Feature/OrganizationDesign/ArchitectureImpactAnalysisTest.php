<?php

namespace Tests\Feature\OrganizationDesign;

use App\Domains\Compensation\Models\CompensationBand;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Organization\Models\Position;
use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\ArchitectureImpactAnalysisService;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchitectureImpactAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_domain_impact_assessment_for_job_profile(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $family = JobFamily::create([
            'tenant_id' => $tenant->id,
            'code' => 'TECH',
            'name' => 'Technology',
        ]);

        $jobDef = Job::create([
            'tenant_id' => $tenant->id,
            'job_family_id' => $family->id,
            'job_code' => 'JOB-SWE-SR',
            'title' => 'Senior Software Engineer Definition',
            'status' => 'active',
        ]);

        $grade = JobGrade::create([
            'tenant_id' => $tenant->id,
            'grade_code' => 'G-07',
            'grade_name' => 'Grade 7',
            'level' => 7,
            'minimum_salary' => 6000,
            'maximum_salary' => 9000,
        ]);

        $profile = JobProfile::create([
            'tenant_id' => $tenant->id,
            'job_id' => $jobDef->id,
            'job_family_id' => $family->id,
            'job_grade_id' => $grade->id,
            'code' => 'JP-SWE-SR',
            'title' => 'Senior Software Engineer',
        ]);

        $position = Position::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'job_id' => $jobDef->id,
            'code' => 'POS-001',
            'position_code' => 'POS-001',
            'title' => 'Lead Backend Engineer Seat',
            'status' => 'active',
            'headcount' => 1,
            'filled_headcount' => 1,
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'current_position_id' => $position->id,
            'employee_code' => 'EMP-001',
            'employee_number' => 'EMP-001',
            'first_name' => 'Sara',
            'last_name' => 'Connor',
            'official_email' => 'sara@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-2026-001',
            'title' => 'Senior Software Engineer Backfill',
            'position_id' => $position->id,
            'status' => 'open',
            'employment_type' => 'full_time',
        ]);

        $band = CompensationBand::create([
            'tenant_id' => $tenant->id,
            'code' => 'BAND-G7',
            'name' => 'Grade 7 Standard Band',
            'job_grade_id' => $grade->id,
            'currency' => 'USD',
            'effective_from' => '2026-01-01',
            'minimum' => 6000,
            'midpoint' => 7500,
            'maximum' => 9000,
        ]);

        $impactService = app(ArchitectureImpactAnalysisService::class);
        $impact = $impactService->assessJobProfileImpact($profile);

        $this->assertEquals($profile->id, $impact['job_profile_id']);
        $this->assertEquals(1, $impact['affected_positions_count']);
        $this->assertEquals(1, $impact['affected_employees_count']);
        $this->assertEquals(1, $impact['affected_requisitions_count']);
        $this->assertEquals(1, $impact['affected_compensation_bands_count']);
    }
}
