<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Enums\OnboardingStep;
use App\Domains\TenantAdmin\Models\HcmTenantOnboardingWizard;
use App\Domains\Shared\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TenantOnboardingService
{
    public function getOrStartWizard(string $tenantId): HcmTenantOnboardingWizard
    {
        return HcmTenantOnboardingWizard::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'status' => 'IN_PROGRESS',
                'current_step' => 1,
                'progress_pct' => 0,
                'step_data' => [],
                'completed_steps' => [],
            ]
        );
    }

    public function saveStep(string $tenantId, int $stepNumber, array $data): HcmTenantOnboardingWizard
    {
        $wizard = $this->getOrStartWizard($tenantId);
        $stepData = $wizard->step_data ?? [];
        $stepData[$stepNumber] = $data;

        $completedSteps = $wizard->completed_steps ?? [];
        if (!in_array($stepNumber, $completedSteps)) {
            $completedSteps[] = $stepNumber;
            sort($completedSteps);
        }

        // Apply Step changes to Tenant and sub-entities
        $this->applyStepChanges($tenantId, $stepNumber, $data);

        $nextStep = min(5, $stepNumber + 1);
        $progressPct = (int) (count($completedSteps) * 20);
        $isCompleted = count($completedSteps) >= 5;

        $wizard->update([
            'current_step' => $isCompleted ? 5 : $nextStep,
            'progress_pct' => $progressPct,
            'step_data' => $stepData,
            'completed_steps' => $completedSteps,
            'status' => $isCompleted ? 'COMPLETED' : 'IN_PROGRESS',
            'completed_at' => $isCompleted ? Carbon::now() : null,
        ]);

        return $wizard->fresh();
    }

    protected function applyStepChanges(string $tenantId, int $stepNumber, array $data): void
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        switch ($stepNumber) {
            case 1: // Company
                $tenant->update([
                    'legal_name' => $data['legal_name'] ?? $tenant->legal_name ?? $tenant->name,
                    'timezone' => $data['timezone'] ?? $tenant->timezone,
                    'currency' => $data['currency'] ?? $tenant->currency,
                    'country_code' => $data['country_code'] ?? 'PK',
                ]);
                break;

            case 2: // Organization
                if (!empty($data['departments']) && is_array($data['departments'])) {
                    $companyId = DB::table('companies')->where('tenant_id', $tenantId)->value('id');
                    if (!$companyId) {
                        $companyId = (string) \Illuminate\Support\Str::uuid();
                        DB::table('companies')->insert([
                            'id' => $companyId,
                            'tenant_id' => $tenantId,
                            'name' => $tenant->name ?? 'Default Company',
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $buId = DB::table('business_units')->where('tenant_id', $tenantId)->value('id');
                    if (!$buId) {
                        $buId = (string) \Illuminate\Support\Str::uuid();
                        DB::table('business_units')->insert([
                            'id' => $buId,
                            'tenant_id' => $tenantId,
                            'company_id' => $companyId,
                            'name' => 'General Business Unit',
                            'code' => 'BU-DEFAULT',
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    foreach ($data['departments'] as $deptName) {
                        $code = strtoupper(substr(str_replace(' ', '_', $deptName), 0, 10));
                        DB::table('departments')->updateOrInsert(
                            ['tenant_id' => $tenantId, 'department_code' => $code],
                            [
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'business_unit_id' => $buId,
                                'department_name' => $deptName,
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
                break;

            case 3: // Workforce
                // Employment rules configured in settings
                break;

            case 4: // Security
                // Admin roles configured
                break;

            case 5: // Modules
                if (!empty($data['modules']) && is_array($data['modules'])) {
                    $featureService = app(FeatureManagementService::class);
                    foreach ($data['modules'] as $moduleKey) {
                        $featureService->enableFeature($tenantId, $moduleKey);
                    }
                }
                break;
        }
    }
}
