<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentConfidentiality;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentSecurityService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentSecurityAndConfidentialityTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_isolation_er_confidentiality_and_secure_download(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $companyA = Company::factory()->create(['tenant_id' => $tenantA->id]);

        $empA = Employee::create([
            'tenant_id' => $tenantA->id,
            'company_id' => $companyA->id,
            'employee_code' => 'EMP-SEC-1',
            'employee_number' => 'EMP-SEC-1',
            'first_name' => 'Toby',
            'last_name' => 'Flenderson',
            'official_email' => 'toby@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $userTenantA = User::factory()->create(['tenant_id' => $tenantA->id, 'is_platform_admin' => false]);
        $userTenantB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $catEr = HcmDocumentCategory::create([
            'tenant_id' => $tenantA->id,
            'code' => 'EMPLOYEE_RELATIONS',
            'name' => 'Employee Relations',
        ]);

        $typeEr = HcmDocumentType::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $catEr->id,
            'code' => 'INVESTIGATION_REPORT',
            'name' => 'Disciplinary Investigation Report',
            'confidentiality_level' => DocumentConfidentiality::HIGHLY_RESTRICTED->value,
        ]);

        $docService = new EmployeeDocumentService();
        $securityService = new EmployeeDocumentSecurityService();

        $erDoc = $docService->storeDocument($userTenantA, $empA, $typeEr, null, [
            'title' => 'Confidential Workplace Investigation Case #402',
        ]);

        // 1. Cross-Tenant Isolation Blocked
        try {
            $securityService->authorizeAccess($userTenantB, $erDoc, 'view');
            $this->fail('Expected AuthorizationException for cross-tenant access');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Cross-tenant employee document access prohibited', $e->getMessage());
        }

        // 2. ER Document Hidden without explicit 'employee_documents.view_er' permission
        $this->assertFalse($securityService->canView($userTenantA, $erDoc));

        // 3. Secure download URL generated (HMAC signature)
        $downloadUrl = $securityService->getSecureDownloadUrl($erDoc);
        $this->assertStringContainsString('signature=', $downloadUrl);
        $this->assertStringNotContainsString('storage/app', $downloadUrl);
    }
}
