<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAcknowledgement;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentAcknowledgementService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentAcknowledgementTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_document_acknowledgement_and_version_tracking(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-ACK-1',
            'employee_number' => 'EMP-ACK-1',
            'first_name' => 'Stanley',
            'last_name' => 'Hudson',
            'official_email' => 'stanley@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);
        $employee->update(['user_id' => $user->id]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CONTRACT',
            'name' => 'Contracts',
        ]);

        $type = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'CODE_OF_CONDUCT',
            'name' => 'Company Code of Conduct Policy',
            'requires_acknowledgement' => true,
        ]);

        $docService = new EmployeeDocumentService();
        $ackService = new EmployeeDocumentAcknowledgementService();

        $doc = $docService->storeDocument($user, $employee, $type, null, [
            'title' => 'Global Employee Code of Conduct 2026',
        ]);

        // Acknowledge document
        $ack = $ackService->acknowledge(
            $doc,
            $employee,
            '192.168.1.100',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        );

        $this->assertInstanceOf(EmployeeDocumentAcknowledgement::class, $ack);
        $this->assertEquals(1, $ack->document_version);
        $this->assertEquals('192.168.1.100', $ack->ip_address);
        $this->assertEquals($employee->id, $ack->employee_id);
        $this->assertNotNull($ack->acknowledged_at);
        $this->assertCount(1, $doc->fresh()->acknowledgements);
    }
}
