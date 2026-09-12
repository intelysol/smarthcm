<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Services\WorkPermitService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkPermitLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_and_updates_work_permit(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-WP-01',
            'employee_number' => 'EMP-WP-01',
            'first_name' => 'Jean',
            'last_name' => 'Valjean',
            'official_email' => 'jean@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(WorkPermitService::class);

        $permit = $service->addPermit((string) $employee->id, [
            'permit_number' => 'WP-FR-99882',
            'country' => 'FRA',
            'permit_type' => 'Salarié En Mission',
            'issuing_authority' => 'Ministère de l’Intérieur',
            'issue_date' => now()->subMonths(6)->toDateString(),
            'effective_from' => now()->subMonths(6)->toDateString(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'job_restriction' => 'Full Employment',
        ], $user);

        $this->assertDatabaseHas('hcm_employee_work_permits', [
            'id' => $permit->id,
            'permit_number' => 'WP-FR-99882',
            'country' => 'FRA',
            'status' => 'active',
        ]);

        // Update permit
        $updated = $service->updatePermit((string) $permit->id, [
            'notes' => 'Renewed biometric validation',
        ], $user);

        $this->assertEquals('Renewed biometric validation', $updated->notes);
    }
}
