<?php
namespace App\Domains\Performance\Contracts;
interface PerformanceSuccessionProvider { /** @return array<string,mixed>|null */ public function performanceProfile(string $employeeId, string $cycleId): ?array; }
