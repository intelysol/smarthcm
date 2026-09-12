<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentRequestStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequest;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentRequestService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentRequestLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_request_creation_completion_and_cancellation(): void
    {
        $tenant = Tenant::factory()->create();
        $hrUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REQ-2',
            'employee_number' => 'EMP-REQ-2',
            'first_name' => 'Ryan',
            'last_name' => 'Howard',
            'official_email' => 'ryan@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CERTIFICATION',
            'name' => 'Certifications',
        ]);

        $docType = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'BUSINESS_LICENSE',
            'name' => 'Business License',
        ]);

        $reqService = new EmployeeDocumentRequestService();
        $docService = new EmployeeDocumentService();

        // 1. Create Document Request
        $request = $reqService->createRequest(
            $hrUser,
            $employee,
            $docType,
            now()->addDays(7)->toDateString(),
            'Please upload a copy of your current active business license.'
        );

        $this->assertInstanceOf(EmployeeDocumentRequest::class, $request);
        $this->assertEquals(DocumentRequestStatus::REQUESTED->value, $request->status);
        $this->assertEquals(now()->addDays(7)->toDateString(), $request->due_date->toDateString());

        // 2. Employee Uploads Document to complete request
        $doc = $docService->storeDocument($hrUser, $employee, $docType, null, [
            'title' => 'Active Business License 2026',
            'document_number' => 'LIC-2026-X',
        ]);

        $completed = $reqService->completeRequest($request, $doc);
        $this->assertEquals(DocumentRequestStatus::SUBMITTED->value, $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertEquals($doc->id, $completed->employee_document_id);

        // 3. Cancel a request
        $request2 = $reqService->createRequest($hrUser, $employee, $docType);
        $cancelled = $reqService->cancelRequest($request2, $hrUser, 'Position requirements changed');
        $this->assertEquals(DocumentRequestStatus::CANCELLED->value, $cancelled->status);
    }
}
