<?php

namespace Tests\Feature\OrganizationDesign;

use App\Domains\Organization\Models\JobGrade;
use App\Domains\OrganizationDesign\Models\JobFamily;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\JobEvaluationService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobEvaluationScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_point_factor_job_evaluation_and_grade_recommendation(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $family = JobFamily::create([
            'tenant_id' => $tenant->id,
            'code' => 'FIN',
            'name' => 'Finance & Accounting',
        ]);

        $grade = JobGrade::create([
            'tenant_id' => $tenant->id,
            'grade_code' => 'GR-08',
            'grade_name' => 'Senior Manager Grade',
            'level' => 8,
            'minimum_salary' => 8000,
            'maximum_salary' => 12000,
        ]);

        $profile = JobProfile::create([
            'tenant_id' => $tenant->id,
            'job_family_id' => $family->id,
            'code' => 'FIN-MGR-01',
            'title' => 'Finance Operations Manager',
        ]);

        $evaluationService = app(JobEvaluationService::class);

        $eval = $evaluationService->evaluateJob($profile, [
            'knowledge_score' => 180,
            'problem_solving_score' => 170,
            'accountability_score' => 160,
            'impact_score' => 150,
            'leadership_score' => 120,
            'notes' => 'Comprehensive multi-country operational treasury oversight',
        ], $user);

        $this->assertEquals(780, $eval->total_points);
        $this->assertEquals('finalized', $eval->status);
        $this->assertEquals($grade->id, $eval->suggested_job_grade_id);
        $this->assertDatabaseHas('job_evaluations', [
            'id' => $eval->id,
            'total_points' => 780,
        ]);
    }
}
