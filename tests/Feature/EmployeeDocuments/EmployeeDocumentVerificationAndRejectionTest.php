<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentStatus;
use App\Domains\EmployeeDocuments\Enums\VerificationStatus;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentVerificationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmployeeDocumentVerificationAndRejectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_verification_rejection_reason_and_replacement(): void
    {
        $tenant = Tenant::factory()->create();
        $hrUser = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-VER-1',
            'employee_number' => 'EMP-VER-1',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'official_email' => 'pam@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'IDENTITY',
            'name' => 'Identity',
        ]);

        $docType = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'DRIVING_LICENSE',
            'name' => 'Driving License',
            'requires_verification' => true,
        ]);

        $docService = new EmployeeDocumentService();
        $verService = new EmployeeDocumentVerificationService();

        $doc = $docService->storeDocument($hrUser, $employee, $docType, null, [
            'title' => 'State Driving License',
            'document_number' => 'DL-908123',
        ]);

        // 1. Verify Document
        $verified = $verService->verify($doc, $hrUser, 'License image is clear and valid');
        $this->assertEquals(VerificationStatus::VERIFIED->value, $verified->verification_status);
        $this->assertEquals(DocumentStatus::VERIFIED->value, $verified->status);
        $this->assertNotNull($verified->verified_at);
        $this->assertCount(1, $verified->verifications);

        // 2. Reject Document without reason should fail
        try {
            $verService->reject($doc, $hrUser, '');
            $this->fail('Expected ValidationException when rejecting without a reason');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        // 3. Reject Document with valid reason
        $rejected = $verService->reject($doc, $hrUser, 'Document copy is blurry and expiry date is illegible');
        $this->assertEquals(VerificationStatus::REJECTED->value, $rejected->verification_status);
        $this->assertEquals(DocumentStatus::REJECTED->value, $rejected->status);
        $this->assertEquals('Document copy is blurry and expiry date is illegible', $rejected->rejection_reason);

        // 4. Replace Document
        $replaced = $docService->replaceDocument($rejected, $hrUser, null, [
            'document_number' => 'DL-908123-REPLACED',
            'change_notes' => 'Uploaded clear high-resolution scanned copy',
        ]);

        $this->assertEquals(VerificationStatus::PENDING->value, $replaced->verification_status);
        $this->assertEquals(DocumentStatus::SUBMITTED->value, $replaced->status);
        $this->assertNull($replaced->rejection_reason);
    }
}
