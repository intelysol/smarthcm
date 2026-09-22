<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Services;

use Flow\Packages\Billing\Domain\Enums\PricingModel;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingPrice;

class PricingEngine
{
    /**
     * Calculate price for a specific price component and quantity.
     *
     * @param BillingPrice $price
     * @param int $quantity
     * @return array{amount: float, unit_price: float, pricing_model: string, breakdown: array}
     */
    public function calculateComponentPrice(BillingPrice $price, int $quantity): array
    {
        $model = $price->pricing_model;
        $unitPrice = (float) $price->unit_price;
        $included = (int) $price->included_quantity;
        $overageRate = (float) $price->overage_price;
        $tiers = $price->tiers_config ?? [];

        $amount = 0.0;
        $breakdown = [];

        switch ($model) {
            case PricingModel::FLAT:
                $amount = $unitPrice;
                $breakdown[] = ['description' => 'Flat component fee', 'quantity' => 1, 'rate' => $unitPrice, 'subtotal' => $amount];
                break;

            case PricingModel::PER_SEAT:
            case PricingModel::PER_UNIT:
                if ($quantity <= $included) {
                    $amount = 0.0;
                    $breakdown[] = ['description' => "Included {$price->unit_name}s", 'quantity' => $quantity, 'rate' => 0.0, 'subtotal' => 0.0];
                } else {
                    $billableQty = $quantity - $included;
                    $rate = $overageRate > 0 ? $overageRate : $unitPrice;
                    $amount = round($billableQty * $rate, 2);
                    if ($included > 0) {
                        $breakdown[] = ['description' => "Included {$price->unit_name}s", 'quantity' => $included, 'rate' => 0.0, 'subtotal' => 0.0];
                    }
                    $breakdown[] = ['description' => "Billable {$price->unit_name}s", 'quantity' => $billableQty, 'rate' => $rate, 'subtotal' => $amount];
                }
                break;

            case PricingModel::TIERED:
            case PricingModel::VOLUME:
                // Volume pricing: all units are charged at the rate corresponding to the volume tier reached
                $applicableRate = $unitPrice;
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($quantity >= $from && $quantity <= $to) {
                        $applicableRate = (float) ($tier['unit_price'] ?? $unitPrice);
                        break;
                    }
                }
                $amount = round($quantity * $applicableRate, 2);
                $breakdown[] = ['description' => "Volume bracket rate ({$quantity} units)", 'quantity' => $quantity, 'rate' => $applicableRate, 'subtotal' => $amount];
                break;

            case PricingModel::GRADUATED:
                // Graduated pricing: units within each tier bracket are charged at that tier's rate
                $remaining = $quantity;
                $tierIndex = 1;
                foreach ($tiers as $tier) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    $tierCapacity = $to - $from + 1;
                    $tierUnits = min($remaining, $tierCapacity);
                    $tierRate = (float) ($tier['unit_price'] ?? $unitPrice);
                    $tierSubtotal = round($tierUnits * $tierRate, 2);

                    $amount += $tierSubtotal;
                    $breakdown[] = [
                        'description' => "Tier {$tierIndex} ({$from} - " . ($to === PHP_INT_MAX ? 'unlimited' : $to) . ")",
                        'quantity' => $tierUnits,
                        'rate' => $tierRate,
                        'subtotal' => $tierSubtotal,
                    ];

                    $remaining -= $tierUnits;
                    $tierIndex++;
                }
                if ($remaining > 0) {
                    $defaultSubtotal = round($remaining * $unitPrice, 2);
                    $amount += $defaultSubtotal;
                    $breakdown[] = ['description' => 'Remaining units above tiers', 'quantity' => $remaining, 'rate' => $unitPrice, 'subtotal' => $defaultSubtotal];
                }
                break;

            case PricingModel::OVERAGE:
                $billable = max(0, $quantity - $included);
                $amount = round($billable * $overageRate, 2);
                $breakdown[] = ['description' => 'Metered overage consumption', 'quantity' => $billable, 'rate' => $overageRate, 'subtotal' => $amount];
                break;

            default:
                $amount = round($quantity * $unitPrice, 2);
                $breakdown[] = ['description' => 'Default quantity rate', 'quantity' => $quantity, 'rate' => $unitPrice, 'subtotal' => $amount];
                break;
        }

        return [
            'amount' => round($amount, 2),
            'unit_price' => $unitPrice,
            'pricing_model' => $model->value,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate recurring total for a plan and quantities.
     *
     * @param BillingPlan $plan
     * @param array<string, int> $quantities Component key => quantity
     * @return array{subtotal: float, setup_fee: float, items: array}
     */
    public function calculatePlanTotal(BillingPlan $plan, array $quantities = []): array
    {
        $subtotal = (float) $plan->base_price;
        $items = [
            [
                'component_key' => 'base_platform',
                'description' => "Base Platform Subscription ({$plan->name})",
                'quantity' => 1,
                'unit_price' => (float) $plan->base_price,
                'amount' => (float) $plan->base_price,
            ]
        ];

        foreach ($plan->prices as $price) {
            $qty = $quantities[$price->component_key] ?? (int) $price->min_quantity;
            $res = $this->calculateComponentPrice($price, $qty);
            $subtotal += $res['amount'];
            $items[] = [
                'component_key' => $price->component_key,
                'description' => "Component: {$price->component_key}",
                'quantity' => $qty,
                'unit_price' => $res['unit_price'],
                'amount' => $res['amount'],
                'breakdown' => $res['breakdown'],
            ];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'setup_fee' => (float) $plan->setup_fee,
            'total' => round($subtotal + (float) $plan->setup_fee, 2),
            'items' => $items,
        ];
    }
}
