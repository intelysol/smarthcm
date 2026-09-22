<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Models\HcmTenantFeatureFlag;
use InvalidArgumentException;

class FeatureManagementService
{
    /**
     * Mandatory module dependencies
     */
    protected array $dependencyMatrix = [
        'PAYROLL' => ['CORE_HR', 'ORG_DESIGN'],
        'LEAVE' => ['CORE_HR'],
        'ATTENDANCE' => ['CORE_HR'],
        'RECRUITMENT' => ['CORE_HR', 'ORG_DESIGN'],
        'PERFORMANCE' => ['CORE_HR'],
        'LEARNING' => ['CORE_HR'],
        'EXPENSES' => ['CORE_HR'],
        'BENEFITS' => ['CORE_HR', 'PAYROLL'],
        'AI_CONCIERGE' => ['AI_PLATFORM', 'RESPONSIBLE_AI'],
        'AI_OPERATIONS' => ['RESPONSIBLE_AI', 'AI_CONCIERGE'],
        'WORKFORCE_INTELLIGENCE' => ['CORE_HR', 'ANALYTICS'],
    ];

    public function listFeatures(string $tenantId): array
    {
        $features = [
            'CORE_HR' => ['name' => 'Core HR & Employee Master', 'desc' => 'Core personnel data and profile management'],
            'ORG_DESIGN' => ['name' => 'Organization Design & Hierarchy', 'desc' => 'Legal entities, departments, and positions'],
            'LEAVE' => ['name' => 'Absence & Leave Management', 'desc' => 'Time-off requests, balances, and accrual rules'],
            'ATTENDANCE' => ['name' => 'Time & Attendance Tracking', 'desc' => 'Clock-in, biometric tracking, shift schedules'],
            'PAYROLL' => ['name' => 'Payroll & Compensation', 'desc' => 'Statutory salary processing and disbursements'],
            'RECRUITMENT' => ['name' => 'Recruitment & ATS', 'desc' => 'Candidate pipeline, interviews, offer letters'],
            'PERFORMANCE' => ['name' => 'Performance & Goals', 'desc' => 'Appraisals, OKRs, reviews, and continuous feedback'],
            'LEARNING' => ['name' => 'Learning & Development', 'desc' => 'Courses, certifications, compliance training'],
            'EXPENSES' => ['name' => 'Expense Management', 'desc' => 'Reimbursements, receipts, corporate expense flows'],
            'BENEFITS' => ['name' => 'Employee Benefits', 'desc' => 'Medical plans, insurance, retirement programs'],
            'ANALYTICS' => ['name' => 'Workforce Analytics', 'desc' => 'Cross-domain reporting and dashboards'],
            'AI_PLATFORM' => ['name' => 'Workforce AI Platform', 'desc' => 'Enterprise LLM routing and tool capabilities'],
            'RESPONSIBLE_AI' => ['name' => 'Responsible AI Governance', 'desc' => 'Model risk management, bias monitoring, kill switches'],
            'AI_CONCIERGE' => ['name' => 'Employee AI Concierge', 'desc' => 'Conversational HR assistant and self-service copilot'],
            'AI_OPERATIONS' => ['name' => 'AI Operations & Intelligence', 'desc' => 'Telemetry, golden evaluations, and regression detection'],
            'WORKFORCE_INTELLIGENCE' => ['name' => 'Workforce Intelligence Command Center', 'desc' => 'Executive cross-domain cockpit'],
        ];

        $tenantFlags = HcmTenantFeatureFlag::where('tenant_id', $tenantId)->get()->keyBy('feature_key');

        $result = [];
        foreach ($features as $key => $meta) {
            $flag = $tenantFlags->get($key);
            $isEnabled = $flag ? $flag->is_enabled : false;
            $deps = $this->dependencyMatrix[$key] ?? [];

            // Check if any required dependencies are disabled
            $missingDeps = [];
            foreach ($deps as $depKey) {
                $depFlag = $tenantFlags->get($depKey);
                $depEnabled = $depFlag && $depFlag->is_enabled;
                if (!$depEnabled) {
                    $missingDeps[] = $depKey;
                }
            }

            $result[$key] = [
                'feature_key' => $key,
                'name' => $meta['name'],
                'description' => $meta['desc'],
                'is_enabled' => $isEnabled,
                'dependencies' => $deps,
                'missing_dependencies' => $missingDeps,
                'can_activate' => empty($missingDeps),
            ];
        }

        return $result;
    }

    public function enableFeature(string $tenantId, string $featureKey): HcmTenantFeatureFlag
    {
        $deps = $this->dependencyMatrix[$featureKey] ?? [];
        if (!empty($deps)) {
            $tenantFlags = HcmTenantFeatureFlag::where('tenant_id', $tenantId)->get()->keyBy('feature_key');
            $missing = [];
            foreach ($deps as $depKey) {
                $depFlag = $tenantFlags->get($depKey);
                $depEnabled = $depFlag && $depFlag->is_enabled;
                if (!$depEnabled) {
                    $missing[] = $depKey;
                }
            }

            if (!empty($missing)) {
                throw new InvalidArgumentException(
                    "Cannot enable {$featureKey}: Missing required active dependencies: " . implode(', ', $missing)
                );
            }
        }

        return HcmTenantFeatureFlag::updateOrCreate(
            ['tenant_id' => $tenantId, 'feature_key' => $featureKey],
            [
                'name' => $featureKey,
                'is_enabled' => true,
                'dependencies' => $deps,
                'rollout_scope' => 'TENANT',
            ]
        );
    }

    public function disableFeature(string $tenantId, string $featureKey): HcmTenantFeatureFlag
    {
        return HcmTenantFeatureFlag::updateOrCreate(
            ['tenant_id' => $tenantId, 'feature_key' => $featureKey],
            [
                'name' => $featureKey,
                'is_enabled' => false,
                'rollout_scope' => 'TENANT',
            ]
        );
    }
}
