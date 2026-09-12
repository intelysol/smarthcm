<?php

namespace Tests\Unit\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Services\LearningCreditService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningCreditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_awarding_creates_ledger_transaction_and_updates_balance(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-040',
            'employee_code' => 'EMP-040',
            'first_name' => 'Farhan',
            'last_name' => 'Saeed',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(LearningCreditService::class);
        $this->assertEquals(0.0, $service->getBalance($employee));

        $tx = $service->awardCredits($employee, 15.0, 'course_completion', null, 'Completed Leadership Seminar');

        $this->assertEquals(15.0, $tx->credits);
        $this->assertEquals(15.0, $service->getBalance($employee));

        $this->assertDatabaseHas('learning_credit_transactions', [
            'employee_id' => $employee->id,
            'credits' => 15.0,
            'source_type' => 'course_completion',
        ]);
    }
}
