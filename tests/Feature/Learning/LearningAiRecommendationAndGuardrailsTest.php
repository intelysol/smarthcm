<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Services\LearningAiRecommendationService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningAiRecommendationAndGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_learning_recommendation_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Cybersecurity', 'department_code' => 'SEC']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-AI-01',
            'employee_number' => 'EMP-AI-01',
            'first_name' => 'Eva',
            'last_name' => 'Green',
            'official_email' => 'eva.g@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        LearningCourse::create([
            'tenant_id' => $tenant->id,
            'title' => 'Cybersecurity Threat Modeling',
            'description' => 'Comprehensive threat modeling and network vulnerability assessments.',
            'code' => 'SEC-201',
            'delivery_type' => 'online',
            'status' => 'published',
            'duration_minutes' => 360,
        ]);

        $service = app(LearningAiRecommendationService::class);

        // 1. Course Recommendations for Skill Gap
        $results = $service->getRecommendationsForEmployee($employee, ['Threat Modeling']);
        $this->assertTrue($results['is_advisory']);
        $this->assertStringContainsString('Passed: Non-autonomous AI HCM Compliance Verified', $results['guardrail_audit']);
        $this->assertNotEmpty($results['recommendations']);
        $this->assertEquals('Cybersecurity Threat Modeling', $results['recommendations'][0]['title']);

        // 2. Disciplinary Query Blocked by Safety Guardrails
        $blockedResult = $service->answerLearningQuery($tenant->id, 'Should we terminate this employee for not completing training?');
        $this->assertEquals('blocked_by_guardrails', $blockedResult['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $blockedResult['error']);
    }
}
