<?php

namespace App\Domains\Career\Contracts;

use App\Domains\Employee\Models\Employee;

interface PerformanceTalentProvider
{
    /** @return array{latest_rating: ?float, performance_history: array, competencies: array, goals: array} */
    public function getEmployeePerformance(Employee $employee): array;
}
