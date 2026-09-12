<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSuggestion extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'employee_suggestions';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'is_anonymous',
        'category',
        'title',
        'description',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'votes_count',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'reviewed_at' => 'datetime',
            'votes_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(EmployeeSuggestionVote::class, 'suggestion_id');
    }
}
