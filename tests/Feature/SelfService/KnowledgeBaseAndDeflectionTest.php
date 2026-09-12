<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Services\KnowledgeBaseService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseAndDeflectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_base_search_deflection_and_feedback(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        
        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-600',
            'employee_number' => 'EMP-600',
            'first_name' => 'Frank',
            'last_name' => 'Sinatra',
            'official_email' => 'frank@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $cat = HrKnowledgeCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'KB-BENEFITS',
            'name' => 'Medical Benefits & Wellness',
        ]);

        $svcCat = HrServiceCategory::create(['tenant_id' => $tenant->id, 'code' => 'CAT-MED', 'name' => 'Medical']);
        $service = HrServiceDefinition::create(['tenant_id' => $tenant->id, 'hr_service_category_id' => $svcCat->id, 'service_code' => 'SVC-MED-CLAIM', 'name' => 'Medical Reimbursement']);

        $article = HrKnowledgeArticle::create([
            'tenant_id' => $tenant->id,
            'hr_knowledge_category_id' => $cat->id,
            'hr_service_definition_id' => $service->id,
            'slug' => 'how-to-claim-medical-expenses',
            'title' => 'How to Submit Medical Expense Claims',
            'summary' => 'Comprehensive guide to filing health and dental insurance claims online.',
            'content' => 'Step 1: Obtain doctor invoice. Step 2: Fill claim form. Step 3: Attach prescriptions.',
            'keywords' => ['medical', 'hospital', 'reimbursement', 'health'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $kbService = app(KnowledgeBaseService::class);

        // 1. Search Articles
        $searchResults = $kbService->searchArticles($tenant->id, 'medical');
        $this->assertCount(1, $searchResults);
        $this->assertEquals('How to Submit Medical Expense Claims', $searchResults->first()->title);

        // 2. Deflection Suggestions for the linked Service
        $deflections = $kbService->getDeflectionSuggestions($tenant->id, $service->id);
        $this->assertCount(1, $deflections);
        $this->assertEquals($article->id, $deflections->first()->id);

        // 3. Record Feedback
        $feedback = $kbService->recordFeedback($article, $employee, true, 'Very helpful and clear!');
        $this->assertTrue($feedback->is_helpful);
        $this->assertEquals(1, $article->fresh()->helpful_count);
    }
}
