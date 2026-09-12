<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['module_id', 'title', 'summary', 'sort_order', 'estimated_duration_minutes'])]
class LearningCourseLesson extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'estimated_duration_minutes' => 'integer',
        ];
    }

    /** @return BelongsTo<LearningCourseModule, LearningCourseLesson> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningCourseModule::class, 'module_id');
    }

    /** @return HasMany<LearningItem> */
    public function items(): HasMany
    {
        return $this->hasMany(LearningItem::class, 'lesson_id')->orderBy('sort_order');
    }
}
