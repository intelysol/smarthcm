<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'name', 'code', 'description', 'is_active'])]
class LearningCategory extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<LearningCourse> */
    public function courses(): HasMany
    {
        return $this->hasMany(LearningCourse::class, 'category_id');
    }
}
