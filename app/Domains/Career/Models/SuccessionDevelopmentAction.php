<?php

namespace App\Domains\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'candidate_id', 'action_type', 'title', 'description',
    'target_completion_date', 'status', 'completion_percentage'
])]
class SuccessionDevelopmentAction extends CareerModel
{
    protected function casts(): array
    {
        return [
            'target_completion_date' => 'date',
            'completion_percentage' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<SuccessionCandidate, SuccessionDevelopmentAction> */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(SuccessionCandidate::class, 'candidate_id');
    }
}
