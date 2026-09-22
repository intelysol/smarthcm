<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Infrastructure;

use Flow\Packages\Billing\Contracts\PaymentProviderInterface;
use Flow\Packages\Billing\Services\BillingReconciliationService;
use Flow\Packages\Billing\Services\CommercialAnalyticsService;
use Flow\Packages\Billing\Services\CreditDiscountService;
use Flow\Packages\Billing\Services\EntitlementResolver;
use Flow\Packages\Billing\Services\InvoiceEngine;
use Flow\Packages\Billing\Services\PaymentManager;
use Flow\Packages\Billing\Services\PricingEngine;
use Flow\Packages\Billing\Services\ProductPlanService;
use Flow\Packages\Billing\Services\ProrationCalculator;
use Flow\Packages\Billing\Services\Providers\MockPaymentProvider;
use Flow\Packages\Billing\Services\Providers\StripeSandboxProvider;
use Flow\Packages\Billing\Services\SubscriptionLifecycleService;
use Flow\Packages\Billing\Services\UsageMeteringService;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PricingEngine::class);
        $this->app->singleton(ProrationCalculator::class);
        $this->app->singleton(EntitlementResolver::class);
        $this->app->singleton(MockPaymentProvider::class);
        $this->app->singleton(StripeSandboxProvider::class);

        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager(
                $app->make(MockPaymentProvider::class),
                $app->make(StripeSandboxProvider::class)
            );
        });

        $this->app->singleton(SubscriptionLifecycleService::class);
        $this->app->singleton(UsageMeteringService::class);
        $this->app->singleton(InvoiceEngine::class);
        $this->app->singleton(CreditDiscountService::class);
        $this->app->singleton(BillingReconciliationService::class);
        $this->app->singleton(CommercialAnalyticsService::class);
        $this->app->singleton(ProductPlanService::class);
    }

    public function boot(): void
    {
        // Auto-seed default commercial catalog if in non-production or if plans empty
        if ($this->app->environment('local', 'testing') && ! $this->app->runningInConsole()) {
            try {
                $this->app->make(ProductPlanService::class)->seedDefaultCatalog();
            } catch (\Throwable $e) {
                // Ignore during early bootstrap
            }
        }
    }
}
