<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceTemplate;
use App\Domains\SelfService\Services\ServiceAutomationService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceAutomationAndDocumentFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_automated_document_fulfillment_and_auto_resolution(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-501',
            'employee_number' => 'EMP-501',
            'first_name' => 'Emma',
            'last_name' => 'Watson',
            'official_email' => 'emma@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-15',
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_LETTERS',
            'name' => 'Letters & Certificates',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-CERT-EMP',
            'name' => 'Employment Verification Letter',
            'status' => 'active',
        ]);

        $template = HrServiceTemplate::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'code' => 'TPL-CERT-EMP',
            'name' => 'Standard Employment Verification Letter',
            'template_type' => 'employment_certificate',
            'template_body' => 'To Whom It May Concern: This letter confirms that {{employee.name}} (ID: {{employee.number}}) joined our organization on {{employee.joining_date}} and is an employee in good standing.',
            'is_active' => true,
        ]);

        $request = HrServiceRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'hr_service_definition_id' => $service->id,
            'request_number' => 'REQ-202609-0030',
            'subject' => 'Request for Employment Letter',
            'status' => ServiceRequestStatus::SUBMITTED->value,
        ]);

        $automationService = app(ServiceAutomationService::class);
        $generatedDoc = $automationService->autoFulfill($request);

        $this->assertNotNull($generatedDoc);
        $this->assertEquals('issued', $generatedDoc->status);
        $this->assertStringContainsString('Emma Watson', $generatedDoc->rendered_content);
        $this->assertStringContainsString('EMP-501', $generatedDoc->rendered_content);

        // Verify request auto-resolved
        $request->refresh();
        $this->assertEquals(ServiceRequestStatus::RESOLVED->value, $request->status);
        $this->assertNotNull($request->resolved_at);
    }
}
