<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSuggestionVote extends EngagementModel
{
    protected $table = 'employee_suggestion_votes';

    protected $fillable = [
        'tenant_id',
        'suggestion_id',
        'employee_id',
    ];

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(EmployeeSuggestion::class, 'suggestion_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
