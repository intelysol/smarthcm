<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['question_id', 'option_text', 'is_correct', 'sort_order'])]
class LearningQuestionOption extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<LearningQuestion, LearningQuestionOption> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LearningQuestion::class, 'question_id');
    }
}
