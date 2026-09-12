<?php
namespace App\Domains\Performance\Contracts;
interface PerformanceCompensationProvider { public function getFinalRating(string $employeeId, string $cycleId): ?float; public function getPerformanceScore(string $employeeId, string $cycleId): ?float; public function getEligiblePerformanceOutcome(string $employeeId, string $cycleId): ?array; }
