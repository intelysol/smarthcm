<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\RequirementStatus;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentRequirementService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentRequirementAndCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_requirements_completeness_score_and_waiver(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REQ-1',
            'employee_number' => 'EMP-REQ-1',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'jim@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'IDENTITY',
            'name' => 'Identity',
        ]);

        $typeCnic = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'CNIC',
            'name' => 'National ID',
            'requires_verification' => true,
        ]);

        $typePassport = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'PASSPORT',
            'name' => 'Passport',
            'requires_verification' => true,
        ]);

        $reqService = new EmployeeDocumentRequirementService();
        $docService = new EmployeeDocumentService();

        // 1. Assign Requirements (CNIC mandatory, Passport mandatory)
        $req1 = $reqService->assignRequirement($employee, $typeCnic, true);
        $req2 = $reqService->assignRequirement($employee, $typePassport, true);

        $completeness = $reqService->evaluateCompleteness($employee);
        $this->assertEquals(2, $completeness['total_required']);
        $this->assertEquals(0, $completeness['verified_count']);
        $this->assertEquals(0.0, $completeness['completeness_score']);
        $this->assertEquals(2, $completeness['missing_count']);

        // 2. Upload CNIC
        $doc = $docService->storeDocument($admin, $employee, $typeCnic, null, [
            'title' => 'National Identity Document',
            'document_number' => 'CNIC-12345',
        ]);

        // Manually mark verified for testing
        $doc->update(['verification_status' => 'verified', 'status' => 'verified']);
        $req1->update(['status' => RequirementStatus::VERIFIED->value]);

        $completenessAfterOne = $reqService->evaluateCompleteness($employee);
        $this->assertEquals(1, $completenessAfterOne['verified_count']);
        $this->assertEquals(50.0, $completenessAfterOne['completeness_score']);

        // 3. Waive Passport requirement with authorized admin
        $reqService->waiveRequirement($req2, $admin, 'Employee is not required to travel internationally');

        $completenessAfterWaive = $reqService->evaluateCompleteness($employee);
        $this->assertEquals(2, $completenessAfterWaive['verified_count']); // verified + waived counts as met
        $this->assertEquals(100.0, $completenessAfterWaive['completeness_score']);
    }
}
