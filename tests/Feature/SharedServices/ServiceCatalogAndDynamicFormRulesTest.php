<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceFormDefinition;
use App\Domains\SelfService\Models\HrServiceFormRule;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\ServiceCatalogService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogAndDynamicFormRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_catalog_categories_and_active_services(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'PAYROLL_REQS',
            'name' => 'Payroll & Compensation Requests',
            'icon' => 'currency-dollar',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-PAYROLL-ADJ',
            'name' => 'Salary Adjustment Inquiry',
            'description' => 'Request review of recent compensation adjustments',
            'is_popular' => true,
            'status' => 'active',
        ]);

        $serviceCatalogService = app(ServiceCatalogService::class);
        $categories = $serviceCatalogService->getCategories($tenant->id);

        $this->assertCount(1, $categories);
        $this->assertEquals('Payroll & Compensation Requests', $categories->first()->name);
        $this->assertCount(1, $categories->first()->services);
        $this->assertEquals('SVC-PAYROLL-ADJ', $categories->first()->services->first()->service_code);
    }

    public function test_dynamic_form_rules_and_conditional_schema(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'BENEFITS_REQS',
            'name' => 'Benefits Administration',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-BEN-ENROLL',
            'name' => 'Health Insurance Enrollment',
            'status' => 'active',
        ]);

        $version = HrServiceVersion::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $formDef = HrServiceFormDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_version_id' => $version->id,
            'form_name' => 'Insurance Plan Selection',
            'schema' => [
                ['key' => 'coverage_type', 'label' => 'Coverage Type', 'type' => 'select', 'options' => ['individual', 'family']],
                ['key' => 'dependent_count', 'label' => 'Number of Dependents', 'type' => 'number', 'required' => false],
            ],
        ]);

        $rule = HrServiceFormRule::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'source_field_key' => 'coverage_type',
            'operator' => 'equals',
            'trigger_values' => ['family'],
            'action' => 'show',
            'target_field_key' => 'dependent_count',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('hr_service_form_rules', [
            'id' => $rule->id,
            'source_field_key' => 'coverage_type',
            'target_field_key' => 'dependent_count',
            'action' => 'show',
        ]);

        $serviceCatalogService = app(ServiceCatalogService::class);
        $result = $serviceCatalogService->getServiceWithForm($service);

        $this->assertEquals($version->id, $result['version']->id);
        $this->assertCount(2, $result['form_schema']);
        $this->assertEquals('coverage_type', $result['form_schema'][0]['key']);
    }
}
