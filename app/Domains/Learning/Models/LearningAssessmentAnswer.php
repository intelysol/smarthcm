<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attempt_id', 'question_id', 'selected_option_id',
    'selected_option_ids', 'text_answer', 'numeric_answer',
    'is_correct', 'points_awarded'
])]
class LearningAssessmentAnswer extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'numeric_answer' => 'decimal:4',
            'is_correct' => 'boolean',
            'points_awarded' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<LearningAssessmentAttempt, LearningAssessmentAnswer> */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(LearningAssessmentAttempt::class, 'attempt_id');
    }

    /** @return BelongsTo<LearningQuestion, LearningAssessmentAnswer> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LearningQuestion::class, 'question_id');
    }

    /** @return BelongsTo<LearningQuestionOption, LearningAssessmentAnswer> */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(LearningQuestionOption::class, 'selected_option_id');
    }
}
