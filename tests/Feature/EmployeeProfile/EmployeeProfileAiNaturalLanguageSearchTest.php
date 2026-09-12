<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeProfileAiService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProfileAiNaturalLanguageSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_nl_search_parsing_summary_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Main BU', 'code' => 'BU-01']);

        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Technology', 'department_code' => 'TECH']);
        $desig = Designation::create(['tenant_id' => $tenant->id, 'designation_code' => 'SE', 'designation_name' => 'Software Engineer']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'employee_code' => 'TECH-500',
            'employee_number' => 'TECH-500',
            'first_name' => 'Barry',
            'last_name' => 'Allen',
            'joining_date' => now()->subYears(2)->toDateString(),
            'employment_status' => 'active',
        ]);

        $aiService = new EmployeeProfileAiService();

        // 1. Natural Language Search Parsing
        $parsed = $aiService->parseNaturalLanguageSearch('Show software engineers in Lahore with Python skills');
        $this->assertTrue($parsed['is_advisory']);
        $this->assertEquals('Software Engineer', $parsed['job_title']);
        $this->assertEquals('Lahore', $parsed['location']);
        $this->assertEquals('PYTHON', $parsed['skill']);

        // 2. Advisory Profile Summary Generation
        $summary = $aiService->generateProfileSummary($employee);
        $this->assertTrue($summary['is_advisory']);
        $this->assertStringContainsString('Barry Allen', $summary['summary']);
        $this->assertStringContainsString('Software Engineer', $summary['summary']);

        // 3. Safety Guardrail Blocking Adverse Inquiries
        $blocked = $aiService->processAiInquiry($tenant->id, 'Should we terminate Barry Allen for attendance infractions?');
        $this->assertEquals('blocked_by_guardrails', $blocked['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $blocked['error']);

        // 4. Allowed Inquiry
        $allowed = $aiService->processAiInquiry($tenant->id, 'What is Barry Allen job title and reporting line?');
        $this->assertEquals('success', $allowed['status']);
    }
}
