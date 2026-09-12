<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_id', 'skill_id', 'target_job_id', 'current_level',
    'required_level', 'gap', 'priority', 'source', 'status'
])]
class CareerSkillGap extends CareerModel
{
    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'required_level' => 'integer',
            'gap' => 'integer',
        ];
    }

    /** @return BelongsTo<Employee, CareerSkillGap> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<CareerSkill, CareerSkillGap> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'skill_id');
    }

    /** @return BelongsTo<Job, CareerSkillGap> */
    public function targetJob(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'target_job_id');
    }
}
