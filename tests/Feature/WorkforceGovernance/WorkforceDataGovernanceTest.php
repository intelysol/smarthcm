<?php

namespace Tests\Feature\WorkforceGovernance;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceGovernance\Contracts\WorkforceDataGovernanceInterface;
use App\Domains\WorkforceGovernance\Models\HcmGovAsset;
use App\Domains\WorkforceGovernance\Services\DataContractGovernanceService;
use App\Domains\WorkforceGovernance\Services\DataLineageService;
use App\Domains\WorkforceGovernance\Services\KpiRegistryGovernanceService;
use App\Domains\WorkforceGovernance\Services\MasterDataReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkforceDataGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_catalog_asset_and_execute_quality_run(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $governance = app(WorkforceDataGovernanceInterface::class);

        // 1. Catalog Asset
        $asset = $governance->registerAsset([
            'tenant_id' => $tenant->id,
            'asset_code' => 'AST-EMP',
            'name' => 'Employee Master Personnel Dataset',
            'domain' => 'CORE_HR',
            'business_definition' => 'Authoritative baseline employee master records',
            'system_of_record' => 'Core HR',
            'business_owner' => 'VP of People Operations',
            'technical_owner' => 'HCM Platform Team',
        ]);

        $this->assertNotNull($asset->id);
        $this->assertEquals('AST-EMP', $asset->asset_code);

        $companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $companyId,
            'tenant_id' => $tenant->id,
            'name' => 'Acme Corporation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed an employee with missing department to test quality issue detection
        DB::table('employees')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'company_id' => $companyId,
            'employee_code' => 'EMP-999',
            'employee_number' => 'EMP-999',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => null, // Deliberate rule violation
            'employment_status' => 'ACTIVE',
            'joining_date' => '2025-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Execute Quality Run
        $run = $governance->executeQualityRun($tenant->id);
        $this->assertEquals('COMPLETED', $run->status);
        $this->assertGreaterThan(0, $run->total_issues_found);

        // 3. Issue Management Workflow
        $issue = $run->issues()->first();
        $this->assertNotNull($issue);
        $this->assertEquals('CRITICAL', $issue->severity);

        // Assign issue
        $assigned = $governance->assignIssue($issue->id, 'steward-uuid-123');
        $this->assertEquals('ASSIGNED', $assigned->status);

        // Resolve issue
        $resolved = $governance->resolveIssue($issue->id, 'user-admin', 'Assigned to Engineering department in Core HR');
        $this->assertEquals('RESOLVED', $resolved->status);

        // 4. Test API Dashboard Endpoint
        $response = $this->withHeaders(['X-Tenant-ID' => $tenant->id])
            ->getJson('/api/hcm/data-governance/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'overall_quality_score',
            'total_governed_assets',
            'open_quality_issues',
            'dimension_scores',
        ]);
    }

    public function test_lineage_graph_recording_and_traversal(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $lineageService = app(DataLineageService::class);

        // Source node
        $lineageService->recordNode([
            'tenant_id' => $tenant->id,
            'node_code' => 'SRC-TIMESHEET',
            'name' => 'Attendance Timesheet Entries',
            'node_type' => 'SOURCE_TABLE',
            'domain' => 'ATTENDANCE',
        ]);

        // Transform / Calculation node
        $lineageService->recordNode([
            'tenant_id' => $tenant->id,
            'node_code' => 'CALC-PROD-HOURS',
            'name' => 'Productive Labor Hours Aggregator',
            'node_type' => 'CALCULATION',
            'domain' => 'PRODUCTIVITY',
        ]);

        // Target KPI node
        $lineageService->recordNode([
            'tenant_id' => $tenant->id,
            'node_code' => 'KPI-COST-PER-FTE',
            'name' => 'Cost per Labor FTE Metric',
            'node_type' => 'KPI',
            'domain' => 'WORKFORCE_INTELLIGENCE',
        ]);

        // Edges
        $lineageService->recordEdge($tenant->id, 'SRC-TIMESHEET', 'CALC-PROD-HOURS', 'TRANSFORM', 'Sum daily regular and overtime hours');
        $lineageService->recordEdge($tenant->id, 'CALC-PROD-HOURS', 'KPI-COST-PER-FTE', 'CALCULATE', 'Divide total labor cost by aggregate productive FTEs');

        // Traverse Lineage
        $graph = $lineageService->getLineage($tenant->id, 'CALC-PROD-HOURS');
        $this->assertNotEmpty($graph['upstream']);
        $this->assertNotEmpty($graph['downstream']);
        $this->assertEquals('SRC-TIMESHEET', $graph['upstream'][0]['source_node']['node_code']);
        $this->assertEquals('KPI-COST-PER-FTE', $graph['downstream'][0]['target_node']['node_code']);

        // Test API Lineage Traversal
        $response = $this->withHeaders(['X-Tenant-ID' => $tenant->id])
            ->getJson('/api/hcm/data-governance/lineage/CALC-PROD-HOURS');

        $response->assertStatus(200);
        $response->assertJsonPath('current_node.node_code', 'CALC-PROD-HOURS');
    }

    public function test_reconciliation_kpi_registry_and_contract_compatibility(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $companyId,
            'tenant_id' => $tenant->id,
            'name' => 'Acme Corporation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'company_id' => $companyId,
            'employee_code' => 'EMP-100',
            'employee_number' => 'EMP-100',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'department_id' => null,
            'employment_status' => 'ACTIVE',
            'joining_date' => '2025-02-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Reconciliation
        $recService = app(MasterDataReconciliationService::class);
        $recResult = $recService->reconcileEmployeeHeadcount($tenant->id, [
            ['employee_code' => 'EMP-100', 'name' => 'Alice Smith'],
            ['employee_code' => 'EMP-UNKNOWN', 'name' => 'Ghost Employee'],
        ]);

        $this->assertEquals('DISCREPANCY_DETECTED', $recResult->status);
        $this->assertEquals(1, $recResult->matched_count);

        // 2. Governed KPI Registry & Certification
        $kpiService = app(KpiRegistryGovernanceService::class);
        $kpi = $kpiService->registerKpi([
            'tenant_id' => $tenant->id,
            'kpi_code' => 'HEADCOUNT',
            'name' => 'Total Workforce Headcount',
            'business_definition' => 'Count of all active employees across authorized business units',
            'technical_formula' => 'COUNT(active_employees)',
            'business_owner' => 'VP HR',
            'technical_owner' => 'HR Data Engineering',
            'source_module' => 'CORE_HR',
        ]);
        $this->assertEquals('UNCERTIFIED', $kpi->certification_status);

        $certified = $kpiService->certifyKpi($kpi->id, 'chief-people-officer');
        $this->assertEquals('CERTIFIED', $certified->certification_status);

        // 3. Data Contract Compatibility & Breaking Change Detection
        $contractService = app(DataContractGovernanceService::class);
        $contract = $contractService->publishContract([
            'tenant_id' => $tenant->id,
            'contract_code' => 'CTR-PAYROLL-COST',
            'name' => 'Payroll Cost Allocation Contract',
            'producer_module' => 'PAYROLL',
            'consumer_modules' => ['WORKFORCE_COST', 'FINANCE'],
            'version' => 1,
            'schema_contract' => [
                'required_fields' => ['employee_id', 'period', 'gross_pay', 'cost_center_code'],
            ],
        ]);
        $this->assertEquals('HEALTHY', $contract->status);

        // Compatible payload
        $validCheck = $contractService->validateCompatibility($tenant->id, 'CTR-PAYROLL-COST', [
            'employee_id' => 'EMP-100',
            'period' => '2026-09',
            'gross_pay' => 5000.00,
            'cost_center_code' => 'CC-900',
        ]);
        $this->assertTrue($validCheck['compatible']);

        // Incompatible payload (missing cost_center_code)
        $invalidCheck = $contractService->validateCompatibility($tenant->id, 'CTR-PAYROLL-COST', [
            'employee_id' => 'EMP-100',
            'period' => '2026-09',
            'gross_pay' => 5000.00,
        ]);
        $this->assertFalse($invalidCheck['compatible']);
        $this->assertEquals('BREAKING_CHANGE_DETECTED', $invalidCheck['status']);
        $this->assertContains('cost_center_code', $invalidCheck['missing_required_fields']);
    }
}
