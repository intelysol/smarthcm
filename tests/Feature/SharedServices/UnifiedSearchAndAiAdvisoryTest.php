<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\UnifiedServiceSearchAndAiService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedSearchAndAiAdvisoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_box_intent_search_and_knowledge_deflection(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_BENEFITS',
            'name' => 'Benefits & Wellness',
            'is_active' => true,
        ]);

        $kbCategory = HrKnowledgeCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'KB_BENEFITS',
            'name' => 'Benefits Knowledge',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-HEALTH-CLAIM',
            'name' => 'Medical Expense Claim',
            'description' => 'Submit medical reimbursement claim',
            'status' => 'active',
        ]);

        $article = HrKnowledgeArticle::create([
            'tenant_id' => $tenant->id,
            'hr_knowledge_category_id' => $kbCategory->id,
            'slug' => 'how-to-submit-medical-insurance-claims',
            'title' => 'How to submit medical insurance claims',
            'summary' => 'Step-by-step guide to insurance reimbursement policy.',
            'content' => 'All medical receipts must be submitted within 30 days of the doctor visit.',
            'status' => 'published',
            'views_count' => 150,
        ]);

        $searchAiService = app(UnifiedServiceSearchAndAiService::class);
        $result = $searchAiService->searchIntent($tenant->id, 'How do I claim insurance medical expense?');

        $this->assertEquals('benefits_inquiry', $result['detected_intent']);
        $this->assertTrue($result['deflection_suggested']);
        $this->assertNotEmpty($result['knowledge_articles']);
        $this->assertEquals($article->id, $result['knowledge_articles']->first()->id);
        $this->assertTrue($result['ai_assistance']['is_advisory_only']);
    }

    public function test_ai_advisory_agent_draft_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-601',
            'employee_number' => 'EMP-601',
            'first_name' => 'Frank',
            'last_name' => 'Sinatra',
            'official_email' => 'frank@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_PAYROLL',
            'name' => 'Payroll Inquiries',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-DIRECT-DEPOSIT',
            'name' => 'Direct Deposit Setup',
            'status' => 'active',
        ]);

        $request = HrServiceRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'hr_service_definition_id' => $service->id,
            'request_number' => 'REQ-202609-0040',
            'subject' => 'Update bank account details',
            'priority' => 'normal',
        ]);

        $searchAiService = app(UnifiedServiceSearchAndAiService::class);
        $advisory = $searchAiService->generateAgentAdvisory($request);

        $this->assertTrue($advisory['is_advisory_only']);
        $this->assertStringContainsString('Frank', $advisory['case_summary']);
        $this->assertStringContainsString('Frank', $advisory['suggested_response_draft']);
        $this->assertFalse($advisory['compliance_risk_flag']);
    }
}
