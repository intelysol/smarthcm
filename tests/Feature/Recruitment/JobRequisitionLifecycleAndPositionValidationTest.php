<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Recruitment\Enums\RequisitionStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\JobRequisitionService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class JobRequisitionLifecycleAndPositionValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requisition_lifecycle_and_frozen_position_prevention(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-REQ']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);

        $activePosition = Position::create([
            'tenant_id' => $tenant->id,
            'department_id' => $dept->id,
            'title' => 'Software Architect',
            'code' => 'POS-ARCH-01',
            'status' => 'active',
        ]);

        $service = new JobRequisitionService();

        // 1. Create requisition for active position
        $requisition = $service->createRequisition([
            'tenant_id' => $tenant->id,
            'title' => 'Software Architect Hiring',
            'position_id' => $activePosition->id,
            'department_id' => $dept->id,
            'openings' => 2,
            'min_salary' => 120000.00,
            'max_salary' => 150000.00,
            'budget_amount' => 300000.00,
        ], $user->id);

        $this->assertInstanceOf(HcmRecruitmentRequisition::class, $requisition);
        $this->assertEquals(RequisitionStatus::DRAFT->value, $requisition->status);
        $this->assertStringStartsWith('REQ-', $requisition->requisition_number);

        // 2. Submit for review
        $submitted = $service->submitRequisition($requisition, $user->id);
        $this->assertEquals(RequisitionStatus::UNDER_REVIEW->value, $submitted->status);
        $this->assertCount(1, $submitted->approvals);

        // 3. Approve requisition
        $approved = $service->approveRequisition($submitted, $user->id, 'Approved for hiring');
        $this->assertEquals(RequisitionStatus::OPEN->value, $approved->status);
        $this->assertNotNull($approved->approved_at);

        // 4. Position validation: frozen position prevention
        $frozenPosition = Position::create([
            'tenant_id' => $tenant->id,
            'department_id' => $dept->id,
            'title' => 'Frozen Role',
            'code' => 'POS-FROZEN',
            'status' => 'frozen',
        ]);

        $this->expectException(ValidationException::class);
        $service->createRequisition([
            'tenant_id' => $tenant->id,
            'title' => 'Frozen Role Requisition',
            'position_id' => $frozenPosition->id,
            'department_id' => $dept->id,
        ], $user->id);
    }
}
