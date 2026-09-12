<?php

namespace App\Domains\Career\Contracts;

use App\Domains\Employee\Models\Employee;

interface InternalMobilityProvider
{
    /** @return array */
    public function findMatchingOpportunities(Employee $employee): array;
}
