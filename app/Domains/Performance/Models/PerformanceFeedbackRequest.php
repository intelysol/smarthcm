<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerformanceFeedbackRequest extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'employee_id',
        'requested_from_employee_id',
        'requested_by',
        'relationship_type',
        'feedback_identity_hidden',
        'status',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'feedback_identity_hidden' => 'boolean',
            'due_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_from_employee_id');
    }

    public function response(): HasOne
    {
        return $this->hasOne(PerformanceFeedbackResponse::class, 'request_id');
    }
}
