<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_data_and_governance_apis(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-API-01',
            'employee_number' => 'EMP-API-01',
            'first_name' => 'James',
            'last_name' => 'Polk',
            'official_email' => 'polk@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        // 1. Get personal data bundle
        $res = $this->actingAs($user)->getJson("/api/v1/hcm/employees/{$employee->id}/personal-data");
        $res->assertOk();
        $res->assertJsonPath('status', 'success');
        $res->assertJsonPath('data.personal_data.first_name', 'James');

        // 2. Update personal data
        $updateRes = $this->actingAs($user)->putJson("/api/v1/hcm/employees/{$employee->id}/personal-data", [
            'preferred_name' => 'Jim Polk',
            'blood_group' => 'B+',
        ]);
        $updateRes->assertOk();
        $updateRes->assertJsonPath('data.preferred_name', 'Jim Polk');

        // 3. Add address via API
        $addrRes = $this->actingAs($user)->postJson("/api/v1/hcm/employees/{$employee->id}/addresses", [
            'address_type' => 'home',
            'address_line_1' => 'Columbia Town Center',
            'city' => 'Columbia',
            'country' => 'USA',
            'is_current' => true,
        ]);
        $addrRes->assertCreated();

        // 4. Submit Bank Change Request via API
        $bankRes = $this->actingAs($user)->postJson("/api/v1/hcm/employees/{$employee->id}/bank-change-requests", [
            'bank_name' => 'First National Bank',
            'account_title' => 'James Polk',
            'account_number' => '9876543210',
        ]);
        $bankRes->assertCreated();
        $requestId = $bankRes->json('data.id');

        // 5. Review Bank Change Request via API
        $reviewRes = $this->actingAs($user)->postJson("/api/v1/hcm/bank-change-requests/{$requestId}/review", [
            'action' => 'approve',
        ]);
        $reviewRes->assertOk();
        $reviewRes->assertJsonPath('data.status', 'applied_to_payroll');

        // 6. Data Quality Recalculate API
        $qRes = $this->actingAs($user)->postJson("/api/v1/hcm/employees/{$employee->id}/data-quality/recalculate");
        $qRes->assertOk();
        $qRes->assertJsonStructure(['status', 'data' => ['scores', 'issues']]);

        // 7. Duplicate Detection API
        $dupRes = $this->actingAs($user)->getJson("/api/v1/hcm/personal-data/duplicates");
        $dupRes->assertOk();
        $dupRes->assertJsonPath('meta.is_advisory', true);
        $dupRes->assertJsonPath('meta.automated_merges_prohibited', true);
    }
}
