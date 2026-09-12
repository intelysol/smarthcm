<?php

namespace Tests\Feature\EmployeeProfile;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\OrgChartService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgChartHierarchyAndLazyLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_chart_roots_children_and_focused_subtree(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        // CEO
        $ceo = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'CEO-01',
            'employee_number' => 'CEO-01',
            'first_name' => 'Gordon',
            'last_name' => 'Gekko',
            'official_email' => 'ceo@example.com',
            'employment_status' => 'active',
            'reporting_manager_id' => null,
            'joining_date' => now()->subYears(5)->toDateString(),
        ]);

        // VP
        $vp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'VP-01',
            'employee_number' => 'VP-01',
            'first_name' => 'Bud',
            'last_name' => 'Fox',
            'official_email' => 'vp@example.com',
            'employment_status' => 'active',
            'reporting_manager_id' => $ceo->id,
            'joining_date' => now()->subYears(3)->toDateString(),
        ]);

        // Analyst
        $analyst = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'ANALYST-01',
            'employee_number' => 'ANALYST-01',
            'first_name' => 'Carl',
            'last_name' => 'Fox',
            'official_email' => 'analyst@example.com',
            'employment_status' => 'active',
            'reporting_manager_id' => $vp->id,
            'joining_date' => now()->subYears(1)->toDateString(),
        ]);

        $service = new OrgChartService();

        // 1. Get Root Nodes
        $roots = $service->getRootNodes($tenant->id);
        $this->assertCount(1, $roots);
        $this->assertEquals('Gordon Gekko', $roots[0]['name']);
        $this->assertEquals(1, $roots[0]['span_of_control']);
        $this->assertTrue($roots[0]['has_children']);

        // 2. Expand Children of CEO
        $children = $service->getChildrenNodes($ceo->id, 1);
        $this->assertCount(1, $children);
        $this->assertEquals('Bud Fox', $children[0]['name']);
        $this->assertEquals(1, $children[0]['span_of_control']);

        // 3. Focused Subtree on VP
        $focused = $service->getFocusedSubtree($vp);
        $this->assertEquals($vp->id, $focused['focus_employee_id']);
        $this->assertNotNull($focused['manager_node']);
        $this->assertEquals('Gordon Gekko', $focused['manager_node']['name']);
        $this->assertCount(1, $focused['employee_node']['children']);
        $this->assertEquals('Carl Fox', $focused['employee_node']['children'][0]['name']);
    }
}
