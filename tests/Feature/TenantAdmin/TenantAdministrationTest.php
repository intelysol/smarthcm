<?php

namespace Tests\Feature\TenantAdmin;

use App\Domains\Shared\Models\Tenant;
use App\Domains\TenantAdmin\Contracts\TenantAdministrationInterface;
use App\Domains\TenantAdmin\Models\HcmTenantConfiguration;
use App\Domains\TenantAdmin\Models\HcmTenantFeatureFlag;
use App\Domains\TenantAdmin\Services\FeatureManagementService;
use App\Domains\TenantAdmin\Services\TenantBrandingService;
use App\Domains\TenantAdmin\Services\TenantConfigurationService;
use App\Domains\TenantAdmin\Services\TenantDataTransferService;
use App\Domains\TenantAdmin\Services\TenantLocalizationService;
use App\Domains\TenantAdmin\Services\TenantOnboardingService;
use App\Domains\TenantAdmin\Services\TenantSecurityAdminService;
use App\Domains\TenantAdmin\Services\TenantSetupHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class TenantAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected TenantAdministrationInterface $adminService;
    protected TenantOnboardingService $onboardingService;
    protected TenantConfigurationService $configService;
    protected FeatureManagementService $featureService;
    protected TenantBrandingService $brandingService;
    protected TenantLocalizationService $localizationService;
    protected TenantSetupHealthService $healthService;
    protected TenantSecurityAdminService $securityService;
    protected TenantDataTransferService $dataTransferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Apex Systems International',
            'slug' => 'apex-systems',
            'tenant_code' => 'APX-ENT-01',
            'status' => 'active',
        ]);

        $this->adminService = app(TenantAdministrationInterface::class);
        $this->onboardingService = app(TenantOnboardingService::class);
        $this->configService = app(TenantConfigurationService::class);
        $this->featureService = app(FeatureManagementService::class);
        $this->brandingService = app(TenantBrandingService::class);
        $this->localizationService = app(TenantLocalizationService::class);
        $this->healthService = app(TenantSetupHealthService::class);
        $this->securityService = app(TenantSecurityAdminService::class);
        $this->dataTransferService = app(TenantDataTransferService::class);
    }

    public function test_tenant_onboarding_wizard_step_by_step(): void
    {
        // 1. Initial State
        $wizard = $this->onboardingService->getOrStartWizard($this->tenant->id);
        $this->assertEquals('IN_PROGRESS', $wizard->status);
        $this->assertEquals(1, $wizard->current_step);
        $this->assertEquals(0, $wizard->progress_pct);

        // Step 1: Company
        $wizard = $this->onboardingService->saveStep($this->tenant->id, 1, [
            'legal_name' => 'Apex Systems Global Corp',
            'timezone' => 'Asia/Karachi',
            'currency' => 'PKR',
        ]);
        $this->assertEquals(20, $wizard->progress_pct);
        $this->assertEquals(2, $wizard->current_step);

        // Step 2: Organization
        $wizard = $this->onboardingService->saveStep($this->tenant->id, 2, [
            'departments' => ['Engineering', 'People Operations', 'Finance'],
        ]);
        $this->assertEquals(40, $wizard->progress_pct);
        $this->assertEquals(3, $wizard->current_step);
        $this->assertEquals(3, DB::table('departments')->where('tenant_id', $this->tenant->id)->count());

        // Step 3: Workforce
        $wizard = $this->onboardingService->saveStep($this->tenant->id, 3, [
            'employment_types' => ['FULL_TIME', 'CONTRACTOR'],
        ]);
        $this->assertEquals(60, $wizard->progress_pct);

        // Step 4: Security
        $wizard = $this->onboardingService->saveStep($this->tenant->id, 4, [
            'mfa_enforced' => true,
        ]);
        $this->assertEquals(80, $wizard->progress_pct);

        // Step 5: Modules
        $wizard = $this->onboardingService->saveStep($this->tenant->id, 5, [
            'modules' => ['CORE_HR', 'ORG_DESIGN', 'LEAVE', 'ATTENDANCE'],
        ]);
        $this->assertEquals(100, $wizard->progress_pct);
        $this->assertEquals('COMPLETED', $wizard->status);
        $this->assertNotNull($wizard->completed_at);
    }

    public function test_hierarchical_configuration_resolution_and_rollback(): void
    {
        $deptId = (string) Str::uuid();

        // 1. Set Tenant scope working hours = 8.0
        $this->configService->setConfiguration(
            $this->tenant->id,
            'standard_working_hours',
            8.0,
            'TIME',
            'TENANT'
        );

        // 2. Set Department scope override = 7.5
        $this->configService->setConfiguration(
            $this->tenant->id,
            'standard_working_hours',
            7.5,
            'TIME',
            'DEPARTMENT',
            $deptId
        );

        // 3. Resolve for Department -> Override takes precedence (7.5)
        $deptResolved = $this->configService->resolveEffectiveConfiguration(
            $this->tenant->id,
            'standard_working_hours',
            ['DEPARTMENT' => $deptId]
        );
        $this->assertTrue($deptResolved['resolved']);
        $this->assertEquals(7.5, $deptResolved['effective_value']);
        $this->assertEquals('DEPARTMENT', $deptResolved['resolved_scope']);

        // 4. Resolve without Department context -> Falls back to Tenant scope (8.0)
        $tenantResolved = $this->configService->resolveEffectiveConfiguration(
            $this->tenant->id,
            'standard_working_hours'
        );
        $this->assertTrue($tenantResolved['resolved']);
        $this->assertEquals(8.0, $tenantResolved['effective_value']);
        $this->assertEquals('TENANT', $tenantResolved['resolved_scope']);

        // 5. Update Tenant scope to 9.0 (version 2), then rollback to version 1
        $this->configService->setConfiguration(
            $this->tenant->id,
            'standard_working_hours',
            9.0,
            'TIME',
            'TENANT'
        );

        $rolledBack = $this->configService->rollbackConfiguration(
            $this->tenant->id,
            'standard_working_hours',
            1
        );
        $this->assertEquals(8.0, $rolledBack->config_value['value'] ?? $rolledBack->config_value);
    }

    public function test_feature_flags_and_dependency_validation(): void
    {
        // 1. Attempt to enable PAYROLL without CORE_HR / ORG_DESIGN -> Throws InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required active dependencies');
        $this->featureService->enableFeature($this->tenant->id, 'PAYROLL');
    }

    public function test_feature_flags_can_be_enabled_when_dependencies_met(): void
    {
        // 1. Enable predecessors
        $this->featureService->enableFeature($this->tenant->id, 'CORE_HR');
        $this->featureService->enableFeature($this->tenant->id, 'ORG_DESIGN');

        // 2. Enable PAYROLL -> Succeeds
        $flag = $this->featureService->enableFeature($this->tenant->id, 'PAYROLL');
        $this->assertTrue($flag->is_enabled);

        // 3. List features shows PAYROLL active with can_activate = true
        $features = $this->featureService->listFeatures($this->tenant->id);
        $this->assertTrue($features['PAYROLL']['is_enabled']);
        $this->assertTrue($features['PAYROLL']['can_activate']);
    }

    public function test_localization_and_pakistan_statutory_foundation(): void
    {
        // 1. Default Localization (Pakistan profile)
        $loc = $this->localizationService->getLocalization($this->tenant->id);
        $this->assertEquals('PK', $loc->country_code);
        $this->assertEquals('PKR', $loc->currency);
        $this->assertEquals('Rs', $loc->currency_symbol);
        $this->assertEquals('Asia/Karachi', $loc->timezone);
        $this->assertEquals('CNIC', $loc->tax_identifier_type);
        $this->assertEquals('#####-#######-#', $loc->national_id_mask);
        $this->assertEquals(7, $loc->fiscal_year_start_month); // July
        $this->assertTrue($loc->is_pakistan_statutory_enabled);

        // 2. Switch to UAE profile
        $uaeLoc = $this->localizationService->updateLocalization($this->tenant->id, [
            'country_code' => 'AE',
        ]);
        $this->assertEquals('AE', $uaeLoc->country_code);
        $this->assertEquals('AED', $uaeLoc->currency);
        $this->assertEquals('EmiratesID', $uaeLoc->tax_identifier_type);
        $this->assertFalse($uaeLoc->is_pakistan_statutory_enabled);
    }

    public function test_branding_and_administrative_delegation(): void
    {
        // 1. Branding
        $branding = $this->brandingService->updateBranding($this->tenant->id, [
            'company_name' => 'Apex Worldwide Tech',
            'primary_color' => '#6366f1',
            'portal_title' => 'Apex Workforce Cloud',
        ]);
        $this->assertEquals('Apex Worldwide Tech', $branding->company_name);
        $this->assertEquals('#6366f1', $branding->primary_color);

        // 2. Administrative Delegation
        $delegatorId = (string) Str::uuid();
        $delegateId = (string) Str::uuid();

        $delegation = $this->securityService->createDelegation($this->tenant->id, [
            'delegator_user_id' => $delegatorId,
            'delegate_user_id' => $delegateId,
            'scope' => 'APPROVALS',
            'reason' => 'Annual leave coverage',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
        ]);

        $this->assertNotNull($delegation->id);
        $this->assertTrue($delegation->isCurrentlyActive());
    }

    public function test_governed_data_import_and_export(): void
    {
        // 1. Data Import: 2 valid rows, 1 invalid row
        $rows = [
            ['employee_code' => 'EMP-101', 'first_name' => 'Zain', 'last_name' => 'Ahmed'],
            ['employee_code' => '', 'first_name' => 'InvalidRow', 'last_name' => 'MissingCode'],
            ['employee_code' => 'EMP-102', 'first_name' => 'Fatima', 'last_name' => 'Khan'],
        ];

        $transfer = $this->dataTransferService->validateAndProcessImport(
            $this->tenant->id,
            'EMPLOYEES',
            $rows
        );

        $this->assertEquals(3, $transfer->total_rows);
        $this->assertEquals(2, $transfer->valid_rows);
        $this->assertEquals(1, $transfer->invalid_rows);
        $this->assertCount(1, $transfer->error_report);

        // Verify valid employees were inserted
        $this->assertEquals(2, DB::table('employees')->where('tenant_id', $this->tenant->id)->count());

        // 2. Data Export
        $export = $this->dataTransferService->initiateExport($this->tenant->id, 'EMPLOYEES');
        $this->assertEquals(2, $export->total_rows);
        $this->assertEquals('COMPLETED', $export->status);
    }

    public function test_admin_api_endpoints_and_web_dashboard(): void
    {
        // 1. GET /api/admin/dashboard
        $dashResponse = $this->withHeaders(['X-Tenant-ID' => $this->tenant->id])
            ->getJson('/api/admin/dashboard');

        $dashResponse->assertStatus(200)
            ->assertJsonStructure([
                'tenant',
                'kpis',
                'onboarding',
                'setup_health',
                'features',
                'branding',
                'localization',
                'system_health',
            ]);

        // 2. GET /api/admin/setup-health
        $healthResponse = $this->withHeaders(['X-Tenant-ID' => $this->tenant->id])
            ->getJson('/api/admin/setup-health');

        $healthResponse->assertStatus(200)
            ->assertJsonPath('setup_health.overall_score', fn ($score) => $score > 50);

        // 3. Render Web Dashboard
        $webResponse = $this->get('/admin/dashboard?tenant_id=' . $this->tenant->id);
        $webResponse->assertStatus(200);
        $webResponse->assertSee('Enterprise Administration', false);
        $webResponse->assertSee('Setup Health Index', false);
        $webResponse->assertSee('Localization', false);
    }
}
