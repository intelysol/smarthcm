<?php

namespace App\Domains\TenantAdmin\Http\Controllers;

use App\Domains\TenantAdmin\Contracts\TenantAdministrationInterface;
use App\Domains\TenantAdmin\Models\HcmTenantConfiguration;
use App\Domains\TenantAdmin\Models\HcmTenantDelegation;
use App\Domains\TenantAdmin\Services\FeatureManagementService;
use App\Domains\TenantAdmin\Services\TenantBrandingService;
use App\Domains\TenantAdmin\Services\TenantConfigurationService;
use App\Domains\TenantAdmin\Services\TenantDataTransferService;
use App\Domains\TenantAdmin\Services\TenantLocalizationService;
use App\Domains\TenantAdmin\Services\TenantOnboardingService;
use App\Domains\TenantAdmin\Services\TenantSecurityAdminService;
use App\Domains\TenantAdmin\Services\TenantSetupHealthService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantAdminApiController extends Controller
{
    public function __construct(
        protected TenantAdministrationInterface $adminService,
        protected TenantOnboardingService $onboardingService,
        protected TenantConfigurationService $configService,
        protected FeatureManagementService $featureService,
        protected TenantBrandingService $brandingService,
        protected TenantLocalizationService $localizationService,
        protected TenantSetupHealthService $healthService,
        protected TenantSecurityAdminService $securityService,
        protected TenantDataTransferService $dataTransferService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $dashboard = $this->adminService->getAdminDashboard($tenantId);

        return response()->json($dashboard);
    }

    public function onboarding(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $wizard = $this->onboardingService->getOrStartWizard($tenantId);

        return response()->json(['wizard' => $wizard->toArray()]);
    }

    public function onboardingStep(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $step = (int) $request->input('step', 1);
        $data = $request->input('data', []);

        $wizard = $this->onboardingService->saveStep($tenantId, $step, $data);

        return response()->json(['wizard' => $wizard->toArray()]);
    }

    public function listConfigurations(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $category = $request->query('category');

        $query = HcmTenantConfiguration::where('tenant_id', $tenantId)->latest();
        if ($category) {
            $query->where('category', $category);
        }

        return response()->json(['configurations' => $query->get()]);
    }

    public function saveConfiguration(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID');

        $config = $this->configService->setConfiguration(
            $tenantId,
            $request->input('config_key'),
            $request->input('config_value'),
            $request->input('category', 'GENERAL'),
            $request->input('scope', 'TENANT'),
            $request->input('scope_id'),
            $request->input('reason', 'Administrative update'),
            $userId
        );

        return response()->json(['configuration' => $config->toArray()], 201);
    }

    public function resolveEffectiveConfiguration(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $key = $request->query('config_key');
        $scopeContext = $request->query('scope_context', []);

        $resolved = $this->configService->resolveEffectiveConfiguration($tenantId, $key, (array) $scopeContext);

        return response()->json($resolved);
    }

    public function rollbackConfiguration(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID');
        $key = $request->input('config_key');
        $version = (int) $request->input('target_version');

        $config = $this->configService->rollbackConfiguration($tenantId, $key, $version, 'Administrative rollback', $userId);

        return response()->json(['configuration' => $config->toArray()]);
    }

    public function features(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $features = $this->featureService->listFeatures($tenantId);

        return response()->json(['features' => $features]);
    }

    public function updateFeature(Request $request, string $featureKey): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $enabled = $request->boolean('is_enabled');

        if ($enabled) {
            $flag = $this->featureService->enableFeature($tenantId, $featureKey);
        } else {
            $flag = $this->featureService->disableFeature($tenantId, $featureKey);
        }

        return response()->json(['feature' => $flag->toArray()]);
    }

    public function setupHealth(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $health = $this->healthService->calculateHealth($tenantId);

        return response()->json(['setup_health' => $health->toArray()]);
    }

    public function branding(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $branding = $this->brandingService->getBranding($tenantId);

        return response()->json(['branding' => $branding->toArray()]);
    }

    public function updateBranding(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $branding = $this->brandingService->updateBranding($tenantId, $request->all());

        return response()->json(['branding' => $branding->toArray()]);
    }

    public function localization(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $localization = $this->localizationService->getLocalization($tenantId);

        return response()->json(['localization' => $localization->toArray()]);
    }

    public function updateLocalization(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $localization = $this->localizationService->updateLocalization($tenantId, $request->all());

        return response()->json(['localization' => $localization->toArray()]);
    }

    public function users(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $users = User::where('tenant_id', $tenantId)->get();

        return response()->json(['users' => $users]);
    }

    public function updateUserStatus(Request $request, string $userId): JsonResponse
    {
        $user = $this->securityService->updateUserStatus($userId, $request->input('status', 'ACTIVE'));

        return response()->json(['user' => $user->toArray()]);
    }

    public function roleTemplates(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $templates = $this->securityService->getRoleTemplates($tenantId);

        return response()->json(['role_templates' => $templates]);
    }

    public function delegations(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $delegations = HcmTenantDelegation::where('tenant_id', $tenantId)->latest()->get();

        return response()->json(['delegations' => $delegations]);
    }

    public function createDelegation(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $data = $request->all();
        $delegation = $this->securityService->createDelegation($tenantId, $data);

        return response()->json(['delegation' => $delegation->toArray()], 201);
    }

    public function systemHealth(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $health = $this->adminService->getSystemHealth($tenantId);

        return response()->json(['system_health' => $health]);
    }

    public function importData(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID');
        $domain = $request->input('domain', 'EMPLOYEES');
        $rows = $request->input('rows', []);

        $transfer = $this->dataTransferService->validateAndProcessImport($tenantId, $domain, $rows, $userId);

        return response()->json(['data_transfer' => $transfer->toArray()], 201);
    }

    public function exportData(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID', 'default-tenant');
        $userId = $request->header('X-User-ID');
        $domain = $request->input('domain', 'EMPLOYEES');

        $transfer = $this->dataTransferService->initiateExport($tenantId, $domain, $userId);

        return response()->json(['data_transfer' => $transfer->toArray()], 201);
    }
}
