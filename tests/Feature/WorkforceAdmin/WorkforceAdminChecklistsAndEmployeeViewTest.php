<?php

namespace Tests\Feature\WorkforceAdmin;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Models\OpsChecklistTemplate;
use App\Domains\WorkforceAdmin\Services\CrossDomainImpactAnalysisService;
use App\Domains\WorkforceAdmin\Services\HrChecklistService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceAdminChecklistsAndEmployeeViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_checklist_template_instantiation_and_item_completion(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-CHK-01',
            'employee_number' => 'EMP-CHK-01',
            'first_name' => 'Angela',
            'last_name' => 'Martin',
            'official_email' => 'angela@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $template = OpsChecklistTemplate::create([
            'tenant_id' => $tenant->id,
            'code' => 'ONBOARDING_CHECKLIST',
            'name' => 'Standard Employee Onboarding',
            'trigger_type' => 'new_hire',
            'default_items' => [
                ['category' => 'equipment', 'title' => 'Issue laptop and security badge'],
                ['category' => 'payroll', 'title' => 'Setup direct deposit bank details'],
            ],
            'is_active' => true,
        ]);

        $checklistService = app(HrChecklistService::class);
        $instance = $checklistService->instantiateChecklist($template, $employee->id, now()->addDays(7)->toDateString(), $user);

        $this->assertEquals('open', $instance->status);
        $this->assertEquals(2, $instance->items()->count());

        $firstItem = $instance->items()->first();
        $checklistService->completeItem($firstItem, $user);

        $instance->refresh();
        $this->assertEquals('in_progress', $instance->status);

        $secondItem = $instance->items()->where('id', '!=', $firstItem->id)->first();
        $checklistService->completeItem($secondItem, $user);

        $instance->refresh();
        $this->assertEquals('completed', $instance->status);
        $this->assertNotNull($instance->actual_completion_date);
    }

    public function test_cross_domain_impact_analysis_generates_risk_and_domain_actions(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-IMP-01',
            'employee_number' => 'EMP-IMP-01',
            'first_name' => 'Oscar',
            'last_name' => 'Martinez',
            'official_email' => 'oscar@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $impactService = app(CrossDomainImpactAnalysisService::class);
        $impact = $impactService->analyzeImpact('bulk_department_update', [
            'department_id' => (string) \Illuminate\Support\Str::uuid(),
        ], $employee->id);

        $this->assertEquals('bulk_department_update', $impact['operation_type']);
        $this->assertGreaterThanOrEqual(3, $impact['impacted_domains_count']);

        $domains = collect($impact['impacts'])->pluck('domain')->toArray();
        $this->assertContains('payroll', $domains);
        $this->assertContains('benefits', $domains);
        $this->assertContains('expenses', $domains);
    }

    public function test_employee_operational_view_api_endpoint(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-VIEW-01',
            'employee_number' => 'EMP-VIEW-01',
            'first_name' => 'Kevin',
            'last_name' => 'Malone',
            'official_email' => 'kevin@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/hcm/workforce-admin/employees/{$employee->id}/operational-view");
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($employee->id, $data['employee']['id']);
        $this->assertArrayHasKey('lifecycle', $data);
        $this->assertArrayHasKey('compliance_and_documents', $data);
        $this->assertArrayHasKey('payroll_and_benefits', $data);
        $this->assertArrayHasKey('operational_exceptions', $data);
        $this->assertArrayHasKey('checklists', $data);
        $this->assertArrayHasKey('governance_status', $data);
    }
}
