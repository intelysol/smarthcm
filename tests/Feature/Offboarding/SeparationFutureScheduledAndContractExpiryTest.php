<?php

namespace Tests\Feature\Offboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\Employment;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Jobs\DetectContractExpiriesJob;
use App\Domains\Offboarding\Jobs\ProcessEffectiveSeparationsJob;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Domains\Offboarding\Services\SeparationExecutionService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparationFutureScheduledAndContractExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_scheduled_separation_and_contract_expiry_scan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-FUT-29',
            'employee_number' => 'EMP-FUT-29',
            'first_name' => 'Angela',
            'last_name' => 'Martin',
            'official_email' => 'angela@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $employment = Employment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'company_id' => $company->id,
            'employment_number' => 'EMP-CON-100',
            'start_date' => now()->subMonths(11)->toDateString(),
            'effective_from' => now()->subMonths(11)->toDateString(),
            'end_date' => now()->addDays(45)->toDateString(), // Expiring in 45 days
            'status' => 'active',
        ]);

        $type = SeparationType::create([
            'tenant_id' => $tenant->id,
            'code' => 'CONTRACT_EXPIRY',
            'name' => 'Contract Expiry',
            'category' => 'expiry',
        ]);

        // 1. Contract Expiry Detection Job
        $expiryJob = new DetectContractExpiriesJob();
        $results = $expiryJob->handle();
        $this->assertNotEmpty($results);
        $this->assertEquals($employment->id, $results[0]['id']);

        // 2. Scheduled Future Separation
        $futureDate = now()->addDays(10)->toDateString();
        $futureRequest = SeparationRequest::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'separation_type_id' => $type->id,
            'request_number' => 'SEP-FUT-01',
            'status' => SeparationStatus::NOTICE_PERIOD->value,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'proposed_last_working_day' => $futureDate,
            'approved_last_working_day' => $futureDate,
            'effective_date' => $futureDate,
        ]);

        // ProcessEffectiveSeparationsJob: Arrival of effective date
        $futureRequest->update(['effective_date' => now()->toDateString()]);
        $processJob = new ProcessEffectiveSeparationsJob();
        $processJob->handle(new SeparationExecutionService());

        $this->assertEquals(SeparationStatus::EXITED->value, $futureRequest->fresh()->status);
        $this->assertEquals('separated', $employee->fresh()->employment_status);
    }
}
