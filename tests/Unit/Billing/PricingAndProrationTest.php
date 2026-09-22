<?php

declare(strict_types=1);

namespace Tests\Unit\Billing;

use Carbon\Carbon;
use Flow\Packages\Billing\Domain\Enums\PricingModel;
use Flow\Packages\Billing\Domain\Models\BillingPrice;
use Flow\Packages\Billing\Services\PricingEngine;
use Flow\Packages\Billing\Services\ProrationCalculator;
use PHPUnit\Framework\TestCase;

class PricingAndProrationTest extends TestCase
{
    protected PricingEngine $pricingEngine;
    protected ProrationCalculator $prorationCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingEngine = new PricingEngine();
        $this->prorationCalculator = new ProrationCalculator();
    }

    public function test_pricing_flat_model(): void
    {
        $price = new BillingPrice([
            'pricing_model' => PricingModel::FLAT,
            'unit_price' => 50.00,
        ]);

        $res = $this->pricingEngine->calculateComponentPrice($price, 10);
        $this->assertSame(50.00, $res['amount']);
    }

    public function test_pricing_per_seat_with_included_units_and_overage(): void
    {
        $price = new BillingPrice([
            'pricing_model' => PricingModel::PER_SEAT,
            'unit_price' => 10.00,
            'included_quantity' => 5,
            'overage_price' => 12.00,
            'unit_name' => 'employee',
        ]);

        // Within included
        $resWithin = $this->pricingEngine->calculateComponentPrice($price, 4);
        $this->assertSame(0.00, $resWithin['amount']);

        // Overage: 10 seats = 5 included + 5 * 12.00 = 60.00
        $resOverage = $this->pricingEngine->calculateComponentPrice($price, 10);
        $this->assertSame(60.00, $resOverage['amount']);
    }

    public function test_pricing_volume_tiered_model(): void
    {
        $price = new BillingPrice([
            'pricing_model' => PricingModel::VOLUME,
            'unit_price' => 20.00,
            'tiers_config' => [
                ['from' => 1, 'to' => 10, 'unit_price' => 20.00],
                ['from' => 11, 'to' => 50, 'unit_price' => 15.00],
                ['from' => 51, 'to' => 100, 'unit_price' => 10.00],
            ],
        ]);

        // 30 units falls in tier 2 (15.00/unit for all units) -> 30 * 15 = 450.00
        $res = $this->pricingEngine->calculateComponentPrice($price, 30);
        $this->assertSame(450.00, $res['amount']);
    }

    public function test_pricing_graduated_model(): void
    {
        $price = new BillingPrice([
            'pricing_model' => PricingModel::GRADUATED,
            'unit_price' => 20.00,
            'tiers_config' => [
                ['from' => 1, 'to' => 10, 'unit_price' => 20.00], // 10 * 20 = 200
                ['from' => 11, 'to' => 30, 'unit_price' => 15.00], // 20 * 15 = 300
            ],
        ]);

        // 25 units: 10 units @ 20 = 200, 15 units @ 15 = 225 -> Total = 425.00
        $res = $this->pricingEngine->calculateComponentPrice($price, 25);
        $this->assertSame(425.00, $res['amount']);
    }

    public function test_proration_mid_cycle_upgrade(): void
    {
        $start = Carbon::parse('2026-06-01 00:00:00');
        $end = Carbon::parse('2026-07-01 00:00:00'); // 30 days
        $mid = Carbon::parse('2026-06-16 00:00:00'); // exactly halfway (15 days remaining)

        $currentPlan = 100.00;
        $newPlan = 200.00;

        $res = $this->prorationCalculator->calculate($currentPlan, $newPlan, $start, $end, $mid);

        $this->assertEqualsWithDelta(0.5, $res['remaining_ratio'], 0.001);
        $this->assertEqualsWithDelta(50.00, $res['unused_current_credit'], 0.01);
        $this->assertEqualsWithDelta(100.00, $res['new_plan_charge'], 0.01);
        $this->assertEqualsWithDelta(50.00, $res['net_adjustment'], 0.01);
        $this->assertSame(50.00, $res['immediate_charge']);
        $this->assertSame(0.00, $res['credit_issued']);
    }

    public function test_proration_mid_cycle_downgrade_produces_credit(): void
    {
        $start = Carbon::parse('2026-06-01 00:00:00');
        $end = Carbon::parse('2026-07-01 00:00:00');
        $mid = Carbon::parse('2026-06-16 00:00:00');

        $currentPlan = 300.00;
        $newPlan = 100.00;

        $res = $this->prorationCalculator->calculate($currentPlan, $newPlan, $start, $end, $mid);

        $this->assertEqualsWithDelta(150.00, $res['unused_current_credit'], 0.01);
        $this->assertEqualsWithDelta(50.00, $res['new_plan_charge'], 0.01);
        $this->assertEqualsWithDelta(-100.00, $res['net_adjustment'], 0.01);
        $this->assertSame(0.00, $res['immediate_charge']);
        $this->assertEqualsWithDelta(100.00, $res['credit_issued'], 0.01);
    }
}
