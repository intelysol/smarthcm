<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['assessment_id', 'question_type', 'question_text', 'explanation', 'points', 'sort_order', 'metadata'])]
class LearningQuestion extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<LearningAssessment, LearningQuestion> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(LearningAssessment::class, 'assessment_id');
    }

    /** @return HasMany<LearningQuestionOption> */
    public function options(): HasMany
    {
        return $this->hasMany(LearningQuestionOption::class, 'question_id')->orderBy('sort_order');
    }
}
