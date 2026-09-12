<?php

declare(strict_types=1);

namespace Tests\Unit\Compensation;

use App\Domains\Compensation\Services\CompensationCalculationEngine;
use Tests\TestCase;

class CompensationCalculationEngineTest extends TestCase
{
    private CompensationCalculationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new CompensationCalculationEngine();
    }

    public function test_compa_ratio_calculation(): void
    {
        // 50,000 salary / 50,000 midpoint = 1.0
        $this->assertEquals(1.0, $this->engine->calculateCompaRatio(50000, 50000));

        // 60,000 salary / 50,000 midpoint = 1.2
        $this->assertEquals(1.2, $this->engine->calculateCompaRatio(60000, 50000));

        // 40,000 salary / 50,000 midpoint = 0.8
        $this->assertEquals(0.8, $this->engine->calculateCompaRatio(40000, 50000));

        // Zero midpoint should return 0.0 safely
        $this->assertEquals(0.0, $this->engine->calculateCompaRatio(50000, 0));
    }

    public function test_range_penetration_calculation(): void
    {
        // min = 40,000, max = 60,000, range = 20,000
        // At minimum (40,000) -> 0.0
        $this->assertEquals(0.0, $this->engine->calculateRangePenetration(40000, 40000, 60000));

        // At midpoint (50,000) -> 0.5
        $this->assertEquals(0.5, $this->engine->calculateRangePenetration(50000, 40000, 60000));

        // At maximum (60,000) -> 1.0
        $this->assertEquals(1.0, $this->engine->calculateRangePenetration(60000, 40000, 60000));

        // When max <= min, safely return 0.0
        $this->assertEquals(0.0, $this->engine->calculateRangePenetration(50000, 60000, 60000));
    }

    public function test_compa_ratio_bracket_determination(): void
    {
        $this->assertEquals('under_80', $this->engine->getCompaRatioBracket(0.78));
        $this->assertEquals('80_to_95', $this->engine->getCompaRatioBracket(0.88));
        $this->assertEquals('95_to_105', $this->engine->getCompaRatioBracket(1.00));
        $this->assertEquals('105_to_120', $this->engine->getCompaRatioBracket(1.15));
        $this->assertEquals('over_120', $this->engine->getCompaRatioBracket(1.25));
    }

    public function test_merit_matrix_guideline_lookup(): void
    {
        $grid = [
            ['rating' => 'exceeds', 'bracket' => '80_to_95', 'min_pct' => 5.0, 'target_pct' => 7.0, 'max_pct' => 9.0],
            ['rating' => 'exceeds', 'bracket' => '95_to_105', 'min_pct' => 4.0, 'target_pct' => 5.5, 'max_pct' => 7.0],
            ['rating' => 'meets', 'bracket' => '95_to_105', 'min_pct' => 2.5, 'target_pct' => 3.5, 'max_pct' => 4.5],
        ];

        // Match exact cell
        $result = $this->engine->lookupMeritMatrixGuideline($grid, 'exceeds', 0.90);
        $this->assertEquals('80_to_95', $result['bracket']);
        $this->assertEquals(5.0, $result['min_pct']);
        $this->assertEquals(7.0, $result['target_pct']);
        $this->assertEquals(9.0, $result['max_pct']);

        // Another cell
        $result2 = $this->engine->lookupMeritMatrixGuideline($grid, 'meets', 1.02);
        $this->assertEquals('95_to_105', $result2['bracket']);
        $this->assertEquals(3.5, $result2['target_pct']);
    }

    public function test_compute_salary_from_percentage(): void
    {
        $result = $this->engine->computeSalaryFromPercentage(100000, 5.5);
        $this->assertEquals(5500.0, $result['increase_amount']);
        $this->assertEquals(105500.0, $result['recommended_base_salary']);
    }

    public function test_bonus_calculation(): void
    {
        // 80,000 base, 10% target, 1.2 individual, 1.1 company
        // Target = 8,000. Final = 8,000 * 1.2 * 1.1 = 10,560.0
        $bonus = $this->engine->calculateIncentiveBonus(80000, 10, 1.2, 1.1);
        $this->assertEquals(10560.0, $bonus);
    }
}
