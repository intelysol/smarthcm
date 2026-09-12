<?php

namespace App\Domains\Career\Models;

use App\Domains\Performance\Models\Competency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'category_id', 'code', 'name', 'description', 'skill_type',
    'assessment_interval_months', 'status', 'version'
])]
class CareerSkill extends CareerModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'assessment_interval_months' => 'integer',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    /** @return BelongsTo<CareerSkillCategory, CareerSkill> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CareerSkillCategory::class, 'category_id');
    }

    /** @return HasMany<EmployeeSkill> */
    public function employeeSkills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class, 'skill_id');
    }

    /** @return HasMany<CareerJobSkillRequirement> */
    public function jobRequirements(): HasMany
    {
        return $this->hasMany(CareerJobSkillRequirement::class, 'skill_id');
    }

    /** @return BelongsToMany<Competency> */
    public function competencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Competency::class,
            'career_skill_competency_mappings',
            'skill_id',
            'competency_id'
        )->withPivot('weight')->withTimestamps();
    }
}
