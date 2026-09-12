<?php

namespace Tests\Feature\OrganizationDesign;

use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\AdvisoryJobAiService;
use App\Domains\OrganizationDesign\Services\ArchitectureHealthAndGovernanceService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchitectureHealthAndAdvisoryAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_architecture_health_scanner_detects_anomalies(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $family = JobFamily::create([
            'tenant_id' => $tenant->id,
            'code' => 'ENG',
            'name' => 'Engineering',
        ]);

        $jobDefRetired = Job::create([
            'tenant_id' => $tenant->id,
            'job_family_id' => $family->id,
            'job_code' => 'JOB-LEGACY-01',
            'title' => 'Legacy Programmer',
            'status' => 'retired',
        ]);

        $profile1 = JobProfile::create([
            'tenant_id' => $tenant->id,
            'job_family_id' => $family->id,
            'code' => 'JP-ENG-01',
            'title' => 'Senior Software Engineer',
            'status' => 'published',
        ]);

        // Duplicate title variation: "Sr Software Engineer"
        $profile2 = JobProfile::create([
            'tenant_id' => $tenant->id,
            'job_family_id' => $family->id,
            'code' => 'JP-ENG-02',
            'title' => 'Sr Software Engineer',
            'status' => 'published',
        ]);

        // Position referencing retired job
        $profileRetired = JobProfile::create([
            'tenant_id' => $tenant->id,
            'job_id' => $jobDefRetired->id,
            'job_family_id' => $family->id,
            'code' => 'JP-LEGACY',
            'title' => 'Legacy Programmer',
            'status' => 'retired',
        ]);

        $positionOnRetired = Position::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'job_id' => $jobDefRetired->id,
            'code' => 'POS-LEGACY-10',
            'title' => 'Legacy Maintenance Seat',
        ]);

        $healthService = app(ArchitectureHealthAndGovernanceService::class);
        $result = $healthService->scanHealth($tenant->id);

        $this->assertGreaterThan(0, $result['total_issues_found']);
        $this->assertDatabaseHas('org_design_health_issues', [
            'tenant_id' => $tenant->id,
            'issue_type' => 'duplicate_title',
        ]);
        $this->assertDatabaseHas('org_design_health_issues', [
            'tenant_id' => $tenant->id,
            'issue_type' => 'retired_reference',
        ]);
    }

    public function test_advisory_job_ai_suggestions_and_guardrails(): void
    {
        $aiService = app(AdvisoryJobAiService::class);

        // 1. Profile drafting
        $draft = $aiService->generateProfileDraftSuggestions('Full Stack Engineer', 'Technology');
        $this->assertTrue($draft['is_advisory_only']);
        $this->assertNotEmpty($draft['suggested_skills']);
        $this->assertNotEmpty($draft['suggested_responsibilities']);

        // 2. Title standardization
        $titleCheck = $aiService->suggestTitleStandardization('Sr. Software Dev');
        $this->assertTrue($titleCheck['is_advisory_only']);
        $this->assertTrue($titleCheck['requires_standardization']);
        $this->assertEquals('Senior Software Developer', $titleCheck['suggested_standard_title']);
    }
}
