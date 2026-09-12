<?php

namespace App\Domains\Career\Models;

use App\Domains\Organization\Models\Job;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'career_path_id', 'job_id', 'career_level', 'sequence',
    'minimum_experience_years', 'performance_min_rating', 'required_skills',
    'required_competencies', 'required_certifications'
])]
class CareerPathStep extends CareerModel
{
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'minimum_experience_years' => 'decimal:1',
            'performance_min_rating' => 'decimal:2',
            'required_skills' => 'array',
            'required_competencies' => 'array',
            'required_certifications' => 'array',
        ];
    }

    /** @return BelongsTo<CareerPath, CareerPathStep> */
    public function careerPath(): BelongsTo
    {
        return $this->belongsTo(CareerPath::class, 'career_path_id');
    }

    /** @return BelongsTo<Job, CareerPathStep> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }
}
