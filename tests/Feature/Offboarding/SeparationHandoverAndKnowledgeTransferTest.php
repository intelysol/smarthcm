<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\HandoverStatus;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationHandoverService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationHandoverAndKnowledgeTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_handover_creation_items_and_manager_verification(): void
    {
        $tenant = Tenant::factory()->create();
        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-HND-1',
            'employee_number' => 'EMP-HND-1',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'jim@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $successor = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-HND-2',
            'employee_number' => 'EMP-HND-2',
            'first_name' => 'Clark',
            'last_name' => 'Green',
            'official_email' => 'clark@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'RESIGNATION',
            'name' => 'Resignation',
        ]);

        $request = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-HND-01',
            'status' => 'notice_period',
            'requested_by' => $managerUser->id,
            'requested_at' => now(),
            'proposed_last_working_day' => now()->addDays(15)->toDateString(),
            'effective_date' => now()->addDays(15)->toDateString(),
        ]);

        $handoverService = new SeparationHandoverService();

        // 1. Create Handover Record
        $record = $handoverService->createHandoverRecord($request, [
            'successor_employee_id' => $successor->id,
            'handover_date' => now()->addDays(10)->toDateString(),
            'handover_notes' => 'Complete portfolio transfer of Northeast client accounts',
            'items' => [
                ['title' => 'Dunder Mifflin Scranton Key Accounts list', 'category' => 'client'],
                ['title' => 'Lead pipeline spreadsheet and CRM access transfer', 'category' => 'project'],
            ],
        ]);

        $this->assertEquals(HandoverStatus::PENDING->value, $record->status);
        $this->assertCount(2, $record->items);

        // 2. Manager verifies handover
        $verified = $handoverService->verifyHandover($record, $managerUser);
        $this->assertEquals(HandoverStatus::VERIFIED->value, $verified->status);
        $this->assertNotNull($verified->manager_verified_at);
        $this->assertEquals(HandoverStatus::COMPLETED->value, $verified->items->first()->status);
    }
}
