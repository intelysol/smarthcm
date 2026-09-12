<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceFormDefinition;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\ServiceCatalogService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogAndDynamicFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_catalog_retrieval_and_dynamic_form_schema(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT-DOCS',
            'name' => 'HR Letters & Certificates',
            'icon' => 'fa-file-certificate',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-CERT',
            'name' => 'Employment Certificate',
            'description' => 'Official certificate of active employment.',
            'is_popular' => true,
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
            'form_name' => 'Certificate Request Form',
            'schema' => [
                ['key' => 'purpose', 'label' => 'Purpose', 'type' => 'text', 'required' => true],
                ['key' => 'addressee', 'label' => 'Addressed To', 'type' => 'text', 'required' => true],
            ],
        ]);

        $catalogService = app(ServiceCatalogService::class);

        // 1. Get Categories
        $categories = $catalogService->getCategories($tenant->id);
        $this->assertCount(1, $categories);
        $this->assertEquals('HR Letters & Certificates', $categories->first()->name);
        $this->assertCount(1, $categories->first()->services);

        // 2. Get Popular Services
        $popular = $catalogService->getPopularServices($tenant->id);
        $this->assertCount(1, $popular);
        $this->assertEquals('SVC-CERT', $popular->first()->service_code);

        // 3. Form schema resolution
        $serviceWithForm = $catalogService->getServiceWithForm($service);
        $this->assertArrayHasKey('form_schema', $serviceWithForm);
        $this->assertCount(2, $serviceWithForm['form_schema']);
        $this->assertEquals('purpose', $serviceWithForm['form_schema'][0]['key']);
    }
}
