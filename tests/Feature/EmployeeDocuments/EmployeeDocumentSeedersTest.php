<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\EmployeeDocumentDefaultTypesSeeder;
use Database\Seeders\EmployeeDocumentPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_execution_and_defaults_verification(): void
    {
        $tenant = Tenant::factory()->create();

        // 1. Run Permissions Seeder
        $this->seed(EmployeeDocumentPermissionSeeder::class);
        $this->assertTrue(Permission::where('name', 'employee_documents.view')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.upload')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.verify')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.reject')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.request')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.acknowledge')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.download')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.bulk_upload')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.view_er')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.view_medical')->exists());
        $this->assertTrue(Permission::where('name', 'employee_documents.view_compensation')->exists());

        // 2. Run Default Categories and Types Seeder
        $this->seed(EmployeeDocumentDefaultTypesSeeder::class);
        $this->assertEquals(22, HcmDocumentCategory::where('tenant_id', $tenant->id)->count());
        $this->assertTrue(HcmDocumentType::where('tenant_id', $tenant->id)->where('code', 'CNIC')->exists());
        $this->assertTrue(HcmDocumentType::where('tenant_id', $tenant->id)->where('code', 'PASSPORT')->exists());
        $this->assertTrue(HcmDocumentType::where('tenant_id', $tenant->id)->where('code', 'EMPLOYMENT_CONTRACT')->exists());
        $this->assertTrue(HcmDocumentType::where('tenant_id', $tenant->id)->where('code', 'OFFER_LETTER')->exists());
    }
}
