<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceFeedbackResponse extends PerformanceModel
{
    protected $fillable = [
        'tenant_id',
        'request_id',
        'rating',
        'response',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:4',
            'submitted_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PerformanceFeedbackRequest::class, 'request_id');
    }
}
