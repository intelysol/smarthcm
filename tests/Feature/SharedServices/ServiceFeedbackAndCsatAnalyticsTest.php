<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceFeedback;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\ServiceFeedbackService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceFeedbackAndCsatAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_csat_submission_and_aggregate_metrics(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-401',
            'employee_number' => 'EMP-401',
            'first_name' => 'David',
            'last_name' => 'Miller',
            'official_email' => 'david@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $employee2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-402',
            'employee_number' => 'EMP-402',
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'official_email' => 'diana@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_GENERAL',
            'name' => 'General Inquiries',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-GEN',
            'name' => 'General Policy Question',
            'status' => 'active',
        ]);

        $req1 = HrServiceRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee1->id,
            'hr_service_definition_id' => $service->id,
            'request_number' => 'REQ-202609-0020',
            'subject' => 'Remote work policy inquiry',
            'status' => ServiceRequestStatus::RESOLVED->value,
        ]);

        $req2 = HrServiceRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee2->id,
            'hr_service_definition_id' => $service->id,
            'request_number' => 'REQ-202609-0021',
            'subject' => 'Dress code inquiry',
            'status' => ServiceRequestStatus::RESOLVED->value,
        ]);

        $feedbackService = app(ServiceFeedbackService::class);

        // Submit 5-star rating
        $fb1 = $feedbackService->submitFeedback($req1, $employee1, [
            'rating' => 5,
            'timeliness_rating' => 5,
            'knowledge_rating' => 5,
            'helpfulness_rating' => 5,
            'comments' => 'Extremely fast response and clear answers!',
        ]);

        // Submit 4-star rating
        $fb2 = $feedbackService->submitFeedback($req2, $employee2, [
            'rating' => 4,
            'timeliness_rating' => 4,
            'knowledge_rating' => 4,
            'helpfulness_rating' => 4,
            'comments' => 'Good support.',
        ]);

        $this->assertDatabaseHas('hr_service_feedback', [
            'id' => $fb1->id,
            'rating' => 5,
            'satisfaction_level' => 'satisfied',
        ]);

        $this->assertDatabaseHas('hr_service_feedback', [
            'id' => $fb2->id,
            'rating' => 4,
            'satisfaction_level' => 'satisfied',
        ]);

        // Aggregate CSAT summary
        $summary = $feedbackService->getFeedbackSummary($tenant->id);

        $this->assertEquals(2, $summary['total_responses']);
        $this->assertEquals(4.5, $summary['average_csat']);
        $this->assertEquals(100.0, $summary['satisfaction_rate']);
        $this->assertEquals(1, $summary['breakdown']['5_star']);
        $this->assertEquals(1, $summary['breakdown']['4_star']);
    }
}
