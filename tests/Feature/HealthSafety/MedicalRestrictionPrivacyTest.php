<?php

declare(strict_types=1);

namespace Tests\Feature\HealthSafety;

use App\Domains\HealthSafety\Models\HcmMedicalRestriction;
use App\Domains\HealthSafety\Services\HealthSecurityService;
use App\Domains\HealthSafety\Services\MedicalRestrictionService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRestrictionPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinical_rationale_is_masked_for_regular_managers(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'General BU',
            'code' => 'BU-GEN',
            'status' => 'active',
        ]);
        $dept = Department::create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_code' => 'DEPT-GEN',
            'department_name' => 'General Dept',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-REST-01',
            'employee_number' => 'EMP-REST-01',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'official_email' => 'jane.doe@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $service = app(MedicalRestrictionService::class);
        $security = app(HealthSecurityService::class);

        $restriction = $service->createRestriction([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'restriction_type' => 'lifting_limit',
            'start_date' => now()->toDateString(),
            'operational_description' => 'No lifting exceeding 20 lbs. Avoid frequent twisting.',
            'medical_rationale_restricted' => 'L4-L5 lumbar disc herniation confirmed via MRI.',
        ]);

        // 1. Regular Manager (without view_clinical_data permission)
        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $masked = $security->maskMedicalRestriction($restriction, $managerUser);

        $this->assertEquals('No lifting exceeding 20 lbs. Avoid frequent twisting.', $masked->operational_description);
        $this->assertEquals('[RESTRICTED - MEDICAL ACCESS ONLY]', $masked->medical_rationale_restricted);

        // 2. Medical Officer
        $docUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $group = \App\Domains\Shared\Models\PermissionGroup::firstOrCreate(['name' => 'health_safety'], ['label' => 'Health & Safety']);
        $perm = Permission::firstOrCreate(['name' => 'hcm.health.view_clinical_data'], [
            'label' => 'View Clinical Data',
            'permission_group_id' => $group->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
        ]);
        $docUser->permissions()->syncWithoutDetaching([$perm->id]);

        $unmasked = $security->maskMedicalRestriction($restriction, $docUser);
        $this->assertEquals('L4-L5 lumbar disc herniation confirmed via MRI.', $unmasked->medical_rationale_restricted);
    }
}
