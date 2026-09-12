<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceDevelopmentAction extends Model
{
    use HasUuids;

    protected $fillable = [
        'plan_id',
        'title',
        'description',
        'owner_id',
        'due_date',
        'status',
        'completion_percentage',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completion_percentage' => 'decimal:2',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PerformanceDevelopmentPlan::class, 'plan_id');
    }
}
