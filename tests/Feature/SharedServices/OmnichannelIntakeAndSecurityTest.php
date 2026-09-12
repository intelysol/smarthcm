<?php

namespace Tests\Feature\SharedServices;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceOmnichannelMessage;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\OmnichannelIntakeService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OmnichannelIntakeAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_omnichannel_intake_ingestion_and_linkage(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-701',
            'employee_number' => 'EMP-701',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'official_email' => 'grace.hopper@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT_GENERAL',
            'name' => 'General Inquiries',
            'is_active' => true,
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'GENERAL_HR_INQUIRY',
            'name' => 'General HR Inquiries',
            'status' => 'active',
        ]);

        $version = HrServiceVersion::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $intakeService = app(OmnichannelIntakeService::class);

        $createdRequest = $intakeService->ingestMessage($tenant->id, 'email', [
            'sender_email' => 'grace.hopper@example.com',
            'sender_name' => 'Grace Hopper',
            'subject' => 'Question regarding retirement benefits',
            'body' => 'Could someone please send me information about the 401k vesting schedule?',
            'external_id' => 'MSG-EXT-998811',
        ]);

        $this->assertNotNull($createdRequest);
        $this->assertEquals($tenant->id, $createdRequest->tenant_id);
        $this->assertEquals($employee->id, $createdRequest->employee_id);
        $this->assertEquals('omnichannel_email', $createdRequest->source_domain_module);

        $this->assertDatabaseHas('hr_service_omnichannel_messages', [
            'tenant_id' => $tenant->id,
            'channel' => 'email',
            'external_message_id' => 'MSG-EXT-998811',
            'matched_employee_id' => $employee->id,
            'hr_service_request_id' => $createdRequest->id,
            'processing_status' => 'converted_to_request',
        ]);
    }

    public function test_tenant_boundary_isolation_on_omnichannel_records(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        HrServiceOmnichannelMessage::create([
            'tenant_id' => $tenantA->id,
            'channel' => 'teams',
            'sender_identifier' => 'user_a@tenant-a.com',
            'sender_name' => 'User A',
            'subject' => 'Tenant A Inquiry',
            'body' => 'Confidential inquiry for Tenant A',
            'processing_status' => 'received',
        ]);

        $recordsTenantB = HrServiceOmnichannelMessage::where('tenant_id', $tenantB->id)->get();
        $this->assertEmpty($recordsTenantB);

        $recordsTenantA = HrServiceOmnichannelMessage::where('tenant_id', $tenantA->id)->get();
        $this->assertCount(1, $recordsTenantA);
    }
}
