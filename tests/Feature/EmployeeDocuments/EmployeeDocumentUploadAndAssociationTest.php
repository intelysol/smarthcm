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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentUploadAndAssociationTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_upload_shared_association_and_personnel_file_grouping(): void
    {
        Storage::fake('local');

        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-DOC-1',
            'employee_number' => 'EMP-DOC-1',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'IDENTITY',
            'name' => 'Identity & Civil Status',
        ]);

        $docType = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'PASSPORT',
            'name' => 'International Passport',
            'requires_verification' => true,
            'expires' => true,
        ]);

        $file = UploadedFile::fake()->create('passport_michael.pdf', 500, 'application/pdf');

        $service = new EmployeeDocumentService();

        // 1. Upload & Associate Document
        $doc = $service->storeDocument($admin, $employee, $docType, $file, [
            'title' => 'Official Passport Document',
            'document_number' => 'PASS-987654',
            'issue_date' => now()->subYears(1)->toDateString(),
            'expiry_date' => now()->addYears(9)->toDateString(),
        ]);

        $this->assertInstanceOf(EmployeeDocument::class, $doc);
        $this->assertEquals('PASS-987654', $doc->document_number);
        $this->assertNotNull($doc->document_id);
        $this->assertEquals('pending', $doc->verification_status);

        // 2. Shared Document Management checks
        $sharedDoc = $doc->sharedDocument;
        $this->assertNotNull($sharedDoc);
        $this->assertEquals(1, $sharedDoc->current_version);
        $this->assertCount(1, $sharedDoc->versions);

        // 3. Digital Personnel File retrieval
        $personnelFile = $service->getPersonnelFile($employee, $admin);
        $this->assertEquals(1, $personnelFile['total_authorized_documents']);
        $this->assertArrayHasKey('IDENTITY', $personnelFile['categories']);
        $this->assertCount(1, $personnelFile['categories']['IDENTITY']['documents']);
    }
}
