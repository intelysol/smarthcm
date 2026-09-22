<?php

declare(strict_types=1);

namespace App\Domains\Billing\Support;

use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Services\EntitlementResolver;
use Flow\Packages\Billing\Services\UsageMeteringService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasFeature(string $tenantId, string $featureKey)
 * @method static ?int getLimit(string $tenantId, string $limitKey)
 * @method static bool checkLimit(string $tenantId, string $limitKey, int $currentUsage = 0)
 * @method static bool canAddEmployee(string $tenantId)
 * @method static array getAllEntitlements(string $tenantId)
 * @method static ?BillingSubscription getActiveSubscription(string $tenantId)
 * @method static bool recordUsage(string $tenantId, string $meterKey, float $quantity, string $idempotencyKey, string $source = 'system', array $metadata = [])
 * @method static float getCurrentUsage(string $tenantId, string $meterKey)
 * @method static array evaluateThreshold(string $tenantId, string $meterKey, ?string $entitlementKey = null)
 */
class CommercialFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EntitlementResolver::class;
    }

    public static function entitled(string $tenantId, string $featureOrLimitKey): bool
    {
        /** @var EntitlementResolver $resolver */
        $resolver = app(EntitlementResolver::class);

        return $resolver->hasFeature($tenantId, $featureOrLimitKey);
    }

    public static function meter(
        string $tenantId,
        string $meterKey,
        float $quantity,
        string $idempotencyKey,
        string $source = 'system',
        array $metadata = []
    ): bool {
        /** @var UsageMeteringService $meterService */
        $meterService = app(UsageMeteringService::class);

        return $meterService->recordUsage($tenantId, $meterKey, $quantity, $idempotencyKey, $source, $metadata);
    }
}
