<?php

namespace App\Domains\Career\Contracts;

use App\Domains\Organization\Models\Job;

interface JobTalentProvider
{
    /** @return array{skills: array, competencies: array, minimum_experience_years: float} */
    public function getJobRequirements(Job $job): array;
}
