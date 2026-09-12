<?php
namespace App\Domains\Performance\Contracts;
interface PerformanceLearningProvider { /** @return list<array<string,mixed>> */ public function recommendedLearning(string $employeeId, string $cycleId): array; }
