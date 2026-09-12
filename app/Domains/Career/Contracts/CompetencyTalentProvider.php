<?php

namespace App\Domains\Career\Contracts;

use App\Domains\Employee\Models\Employee;

interface CompetencyTalentProvider
{
    /** @return array<string, array{id: string, name: string, rating: float, level: int}> */
    public function getEmployeeCompetencies(Employee $employee): array;
}
