<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceTemplate;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\RequestTemplateService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDocumentGenerationAndTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_template_placeholder_replacement_and_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-HQ']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $desig = Designation::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'designation_name' => 'Senior Architect', 'designation_code' => 'DES-SA']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'employee_code' => 'EMP-500',
            'employee_number' => 'EMP-500',
            'first_name' => 'Edward',
            'last_name' => 'Norton',
            'official_email' => 'edward@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-02-01',
        ]);

        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create(['tenant_id' => $tenant->id, 'code' => 'CAT-DOCS', 'name' => 'Certificates']);
        $service = HrServiceDefinition::create(['tenant_id' => $tenant->id, 'hr_service_category_id' => $category->id, 'service_code' => 'SVC-EXP', 'name' => 'Experience Certificate']);
        $version = HrServiceVersion::create(['tenant_id' => $tenant->id, 'hr_service_definition_id' => $service->id, 'version_number' => 1, 'effective_from' => '2026-01-01']);

        $template = HrServiceTemplate::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'code' => 'TPL-EXP',
            'name' => 'Experience Certificate Template',
            'template_type' => 'experience_letter',
            'template_body' => "This certifies that {{employee.name}} (ID: {{employee.code}}) works as {{employee.position}} in {{employee.department}}.",
            'requires_approval' => true,
        ]);

        $requestService = app(ServiceRequestService::class);
        $templateService = app(RequestTemplateService::class);

        $request = $requestService->createRequest($employee, $service, ['subject' => 'Experience Letter Request']);

        // 1. Generate Document from template
        $generatedDoc = $templateService->generateDocumentFromTemplate($request, $template);
        $this->assertEquals('pending_approval', $generatedDoc->status);
        $this->assertStringContainsString('Edward Norton', $generatedDoc->rendered_content);
        $this->assertStringContainsString('EMP-500', $generatedDoc->rendered_content);
        $this->assertStringContainsString('Senior Architect', $generatedDoc->rendered_content);
        $this->assertStringContainsString('Engineering', $generatedDoc->rendered_content);

        // 2. Approve Document
        $approvedDoc = $templateService->approveDocument($generatedDoc, $approver);
        $this->assertEquals('approved', $approvedDoc->status);
        $this->assertEquals($approver->id, $approvedDoc->approved_by_user_id);

        // 3. Employee Acknowledgement
        $acknowledgedDoc = $templateService->acknowledgeDocument($approvedDoc, '192.168.1.50');
        $this->assertEquals('issued', $acknowledgedDoc->status);
        $this->assertEquals('192.168.1.50', $acknowledgedDoc->acknowledged_ip);
    }
}
