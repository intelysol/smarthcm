<?php

namespace Tests\Feature\Compliance;

use App\Domains\Compliance\Models\HcmComplianceRequirement;
use App\Domains\Compliance\Models\HcmComplianceRequirementType;
use App\Domains\Compliance\Services\ComplianceRequirementService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceRequirementConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_update_compliance_requirement(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $type = HcmComplianceRequirementType::create([
            'tenant_id' => $tenant->id,
            'code' => 'WORK_PERMIT',
            'name' => 'Work Authorization Permit',
            'is_active' => true,
        ]);

        $service = app(ComplianceRequirementService::class);

        $req = $service->createRequirement((string) $tenant->id, [
            'requirement_type_id' => $type->id,
            'code' => 'US_I9_PERMIT',
            'name' => 'US Form I-9 Work Authorization',
            'country' => 'USA',
            'renewal_required' => true,
            'grace_period_days' => 15,
            'is_mandatory' => true,
            'verification_required' => true,
        ], $user);

        $this->assertDatabaseHas('hcm_compliance_requirements', [
            'id' => $req->id,
            'code' => 'US_I9_PERMIT',
            'name' => 'US Form I-9 Work Authorization',
            'country' => 'USA',
        ]);

        $updated = $service->updateRequirement((string) $req->id, [
            'name' => 'US Form I-9 Work Authorization (Updated)',
            'grace_period_days' => 30,
        ], $user);

        $this->assertEquals('US Form I-9 Work Authorization (Updated)', $updated->name);
        $this->assertEquals(30, $updated->grace_period_days);
    }
}
