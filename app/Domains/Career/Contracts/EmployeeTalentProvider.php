<?php

namespace App\Domains\Career\Contracts;

use App\Domains\Employee\Models\Employee;

interface EmployeeTalentProvider
{
    /** @return array{id: string, name: string, department: ?string, job: ?string, tenure_years: float, joining_date: ?string} */
    public function getEmployeeSummary(Employee $employee): array;
}
