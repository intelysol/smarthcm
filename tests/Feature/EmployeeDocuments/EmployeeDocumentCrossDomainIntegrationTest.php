<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeDocumentCrossDomainIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_domain_associations_lifecycle_and_offboarding(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-INT-1',
            'employee_number' => 'EMP-INT-1',
            'first_name' => 'Oscar',
            'last_name' => 'Martinez',
            'official_email' => 'oscar@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $catLifecycle = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'LIFECYCLE',
            'name' => 'Lifecycle & Job Changes',
        ]);

        $typePromo = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $catLifecycle->id,
            'code' => 'PROMOTION_LETTER',
            'name' => 'Promotion Letter',
        ]);

        $docService = new EmployeeDocumentService();

        // 1. Associate Promotion Letter from Epic 2.28
        $personnelActionId = (string) Str::uuid();
        $promoDoc = $docService->storeDocument($admin, $employee, $typePromo, null, [
            'title' => 'Promotion to Senior Financial Analyst',
            'related_type' => 'App\\Domains\\Lifecycle\\Models\\PersonnelAction',
            'related_id' => $personnelActionId,
        ]);

        $this->assertEquals('App\\Domains\\Lifecycle\\Models\\PersonnelAction', $promoDoc->related_type);
        $this->assertEquals($personnelActionId, $promoDoc->related_id);

        // 2. Query Personnel File and verify association
        $retrieved = EmployeeDocument::where('related_id', $personnelActionId)->first();
        $this->assertNotNull($retrieved);
        $this->assertEquals('Promotion to Senior Financial Analyst', $retrieved->title);
    }
}
