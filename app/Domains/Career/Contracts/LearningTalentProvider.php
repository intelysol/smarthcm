<?php

namespace App\Domains\Career\Contracts;

use App\Domains\Employee\Models\Employee;

interface LearningTalentProvider
{
    /** @return array{completed_courses: array, certificates: array, total_credits: float, learning_hours: float} */
    public function getEmployeeLearning(Employee $employee): array;
}
