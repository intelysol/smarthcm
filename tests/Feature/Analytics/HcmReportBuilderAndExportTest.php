<?php

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Services\HcmReportBuilderService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmReportBuilderAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_builder_query_execution_and_csv_generation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept1 = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG']);
        $dept2 = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Marketing', 'department_code' => 'MKT']);
        $branch = Branch::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_name' => 'HQ', 'branch_code' => 'BR-01']);

        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept1->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-RPT-1',
            'employee_number' => 'EMP-RPT-1',
            'first_name' => 'Dev',
            'last_name' => 'One',
            'official_email' => 'dev1@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept2->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-RPT-2',
            'employee_number' => 'EMP-RPT-2',
            'first_name' => 'Marketer',
            'last_name' => 'One',
            'official_email' => 'mkt1@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $reportService = app(HcmReportBuilderService::class);

        // 1. Run Dynamic Report Query
        $result = $reportService->executeReportQuery($tenant->id, 'DP_WORKFORCE', [
            'dimensions' => ['department', 'branch'],
            'metrics' => ['headcount', 'fte'],
            'filters' => [],
        ]);

        $this->assertEquals('DP_WORKFORCE', $result['dataset']);
        $this->assertEquals(2, $result['total_records_analyzed']);
        $this->assertEquals(2, $result['grouped_rows_count']);

        // 2. Generate CSV Export
        $csv = $reportService->generateCsvExport($result);
        $this->assertStringContainsString('group,headcount,active_count,fte', $csv);
        $this->assertStringContainsString('Engineering', $csv);
        $this->assertStringContainsString('Marketing', $csv);
    }
}
