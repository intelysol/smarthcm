<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'scenario_id', 'succession_position_id', 'proposed_successor_id',
    'role_assignment', 'impact_analysis'
])]
class SuccessionScenarioCandidate extends CareerModel
{
    /** @return BelongsTo<SuccessionScenario, SuccessionScenarioCandidate> */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(SuccessionScenario::class, 'scenario_id');
    }

    /** @return BelongsTo<SuccessionPosition, SuccessionScenarioCandidate> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(SuccessionPosition::class, 'succession_position_id');
    }

    /** @return BelongsTo<Employee, SuccessionScenarioCandidate> */
    public function proposedSuccessor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'proposed_successor_id');
    }
}
