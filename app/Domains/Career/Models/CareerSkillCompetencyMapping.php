<?php

namespace App\Domains\Career\Models;

use App\Domains\Performance\Models\Competency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'skill_id', 'competency_id', 'weight'
])]
class CareerSkillCompetencyMapping extends CareerModel
{
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<CareerSkill, CareerSkillCompetencyMapping> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(CareerSkill::class, 'skill_id');
    }

    /** @return BelongsTo<Competency, CareerSkillCompetencyMapping> */
    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }
}
