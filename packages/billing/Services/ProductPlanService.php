<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Flow\Packages\Billing\Domain\Enums\BillingInterval;
use Flow\Packages\Billing\Domain\Enums\EntitlementType;
use Flow\Packages\Billing\Domain\Enums\PricingModel;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingPlanEntitlement;
use Flow\Packages\Billing\Domain\Models\BillingPrice;
use Flow\Packages\Billing\Domain\Models\BillingProduct;
use Flow\Packages\Billing\Domain\Models\BillingProductVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductPlanService
{
    /**
     * Create or retrieve a commercial product.
     */
    public function findOrCreateProduct(string $code, string $name, string $description = ''): BillingProduct
    {
        return BillingProduct::firstOrCreate(
            ['code' => $code],
            [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }

    /**
     * Create or retrieve product version.
     */
    public function findOrCreateVersion(BillingProduct $product, string $version): BillingProductVersion
    {
        return BillingProductVersion::firstOrCreate(
            ['product_id' => $product->id, 'version' => $version],
            [
                'id' => (string) Str::uuid(),
                'is_active' => true,
            ]
        );
    }

    /**
     * Create a commercial plan with prices and entitlements.
     */
    public function createPlan(array $data, array $prices = [], array $entitlements = []): BillingPlan
    {
        return DB::transaction(function () use ($data, $prices, $entitlements) {
            $plan = BillingPlan::create([
                'id' => (string) Str::uuid(),
                'product_id' => $data['product_id'],
                'version_id' => $data['version_id'] ?? null,
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'billing_model' => $data['billing_model'] ?? 'flat',
                'billing_interval' => $data['billing_interval'] ?? BillingInterval::MONTHLY,
                'currency' => $data['currency'] ?? 'USD',
                'base_price' => $data['base_price'] ?? 0.00,
                'setup_fee' => $data['setup_fee'] ?? 0.00,
                'trial_period_days' => $data['trial_period_days'] ?? 14,
                'min_commitment_months' => $data['min_commitment_months'] ?? 0,
                'max_quantity' => $data['max_quantity'] ?? null,
                'status' => $data['status'] ?? 'active',
                'version' => 1,
            ]);

            foreach ($prices as $p) {
                BillingPrice::create([
                    'id' => (string) Str::uuid(),
                    'plan_id' => $plan->id,
                    'component_key' => $p['component_key'],
                    'pricing_model' => $p['pricing_model'] ?? PricingModel::FLAT,
                    'unit_name' => $p['unit_name'] ?? 'seat',
                    'unit_price' => $p['unit_price'] ?? 0.0000,
                    'included_quantity' => $p['included_quantity'] ?? 0,
                    'overage_price' => $p['overage_price'] ?? 0.0000,
                    'tiers_config' => $p['tiers_config'] ?? null,
                    'currency' => $plan->currency,
                ]);
            }

            foreach ($entitlements as $e) {
                BillingPlanEntitlement::create([
                    'id' => (string) Str::uuid(),
                    'plan_id' => $plan->id,
                    'entitlement_key' => $e['key'],
                    'entitlement_type' => $e['type'] ?? EntitlementType::BOOLEAN,
                    'limit_value' => $e['limit'] ?? null,
                    'is_enabled' => $e['enabled'] ?? true,
                ]);
            }

            return $plan->load(['prices', 'entitlements']);
        });
    }

    /**
     * Seed baseline platform commercial products & plans if not already present.
     */
    public function seedDefaultCatalog(): void
    {
        $hcm = $this->findOrCreateProduct('hcm', 'Enterprise HCM', 'Human Capital Management SaaS');
        $v1 = $this->findOrCreateVersion($hcm, '2026.1');

        // 1. Starter Plan ($49/mo, 10 employees)
        if (! BillingPlan::where('code', 'hcm-starter')->exists()) {
            $this->createPlan(
                [
                    'product_id' => $hcm->id,
                    'version_id' => $v1->id,
                    'name' => 'HCM Starter',
                    'code' => 'hcm-starter',
                    'description' => 'Ideal for small enterprises starting with Core HR and Attendance.',
                    'billing_model' => 'flat',
                    'billing_interval' => BillingInterval::MONTHLY,
                    'currency' => 'USD',
                    'base_price' => 49.00,
                    'trial_period_days' => 14,
                ],
                [
                    ['component_key' => 'additional_seat', 'pricing_model' => PricingModel::PER_SEAT, 'unit_name' => 'employee', 'unit_price' => 5.00, 'included_quantity' => 10],
                ],
                [
                    ['key' => 'employee_limit', 'type' => EntitlementType::LIMIT, 'limit' => 10, 'enabled' => true],
                    ['key' => 'user_limit', 'type' => EntitlementType::LIMIT, 'limit' => 3, 'enabled' => true],
                    ['key' => 'core_hr_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'attendance_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'payroll_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => false],
                    ['key' => 'recruitment_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => false],
                    ['key' => 'ai_concierge_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => false],
                ]
            );
        }

        // 2. Professional Plan ($199/mo, 50 employees, payroll + recruitment enabled)
        if (! BillingPlan::where('code', 'hcm-professional')->exists()) {
            $this->createPlan(
                [
                    'product_id' => $hcm->id,
                    'version_id' => $v1->id,
                    'name' => 'HCM Professional',
                    'code' => 'hcm-professional',
                    'description' => 'Complete HR, Attendance, Payroll, and Recruitment for growing businesses.',
                    'billing_model' => 'flat',
                    'billing_interval' => BillingInterval::MONTHLY,
                    'currency' => 'USD',
                    'base_price' => 199.00,
                    'trial_period_days' => 14,
                ],
                [
                    ['component_key' => 'additional_seat', 'pricing_model' => PricingModel::PER_SEAT, 'unit_name' => 'employee', 'unit_price' => 4.00, 'included_quantity' => 50],
                ],
                [
                    ['key' => 'employee_limit', 'type' => EntitlementType::LIMIT, 'limit' => 50, 'enabled' => true],
                    ['key' => 'user_limit', 'type' => EntitlementType::LIMIT, 'limit' => 15, 'enabled' => true],
                    ['key' => 'core_hr_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'attendance_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'payroll_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'recruitment_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'ai_concierge_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => false],
                ]
            );
        }

        // 3. Enterprise Suite ($599/mo, 200 employees, all modules, AI Concierge enabled)
        if (! BillingPlan::where('code', 'hcm-enterprise')->exists()) {
            $this->createPlan(
                [
                    'product_id' => $hcm->id,
                    'version_id' => $v1->id,
                    'name' => 'HCM Enterprise Suite',
                    'code' => 'hcm-enterprise',
                    'description' => 'Unrestricted access to all HCM modules, AI Concierge, and Advanced Analytics.',
                    'billing_model' => 'flat',
                    'billing_interval' => BillingInterval::MONTHLY,
                    'currency' => 'USD',
                    'base_price' => 599.00,
                    'trial_period_days' => 30,
                ],
                [
                    ['component_key' => 'additional_seat', 'pricing_model' => PricingModel::PER_SEAT, 'unit_name' => 'employee', 'unit_price' => 3.00, 'included_quantity' => 200],
                ],
                [
                    ['key' => 'employee_limit', 'type' => EntitlementType::LIMIT, 'limit' => 200, 'enabled' => true],
                    ['key' => 'user_limit', 'type' => EntitlementType::LIMIT, 'limit' => 50, 'enabled' => true],
                    ['key' => 'core_hr_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'attendance_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'payroll_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'recruitment_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'learning_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'performance_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'ai_concierge_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                    ['key' => 'advanced_analytics_enabled', 'type' => EntitlementType::BOOLEAN, 'enabled' => true],
                ]
            );
        }
    }
}
