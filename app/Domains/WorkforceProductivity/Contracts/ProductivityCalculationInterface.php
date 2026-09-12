<?php

namespace App\Domains\WorkforceProductivity\Contracts;

interface ProductivityCalculationInterface
{
    /**
     * Compute productivity rate safely (Output / Productive Hours).
     * Returns null (N/A) if hours <= 0 or output is null.
     */
    public function calculateRate(?float $output, ?float $hours): ?float;

    /**
     * Compute unit cost safely (Cost / Output).
     * Returns null if output <= 0.
     */
    public function calculateUnitCost(?float $cost, ?float $output): ?float;

    /**
     * Compute utilization rate safely (Productive Hours / Available Hours * 100).
     */
    public function calculateUtilization(?float $productiveHours, ?float $availableHours): ?float;
}
