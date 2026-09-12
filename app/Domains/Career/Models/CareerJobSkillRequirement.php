<?php

namespace App\Domains\Career\Models;

use App\Domains\Organization\Models\Job;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'job_id', 'skill_id', 'required_level', 'importance', 'is_mandatory'
])]
class CareerJobSkillRequirement extends CareerModel
{
    protected function casts(): array
    {
        return [
            'required_level' => 'integer',
            'is_mandatory' => 'boolean',
        ];
    }

    /** @return BelongsTo<Job, CareerJobSkillRequirement> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /** @return BelongsTo<CareerSkill, CareerJobSkillRequirement> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'skill_id');
    }
}
