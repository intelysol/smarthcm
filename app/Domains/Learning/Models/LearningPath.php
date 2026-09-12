<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['tenant_id', 'uuid', 'code', 'title', 'description', 'target_role', 'job_id', 'job_grade_id', 'status', 'rule_type'])]
class LearningPath extends LearningModel
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $path): void {
            $path->uuid ??= (string) Str::uuid();
        });
    }

    /** @return HasMany<LearningPathItem> */
    public function items(): HasMany
    {
        return $this->hasMany(LearningPathItem::class, 'learning_path_id')->orderBy('sort_order');
    }
}
