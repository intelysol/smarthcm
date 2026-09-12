<?php

namespace App\Domains\Compensation\Models;

use App\Domains\Platform\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationBudget extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'compensation_cycle_id',
        'scope_type',
        'scope_id',
        'budget_type',
        'currency',
        'allocated',
        'consumed',
        'holdback_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated' => 'decimal:2',
            'consumed' => 'decimal:2',
            'holdback_amount' => 'decimal:2',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(CompensationCycle::class, 'compensation_cycle_id');
    }

    public function remaining(): float
    {
        return (float) ($this->allocated - $this->consumed - $this->holdback_amount);
    }

    public function utilizationPercentage(): float
    {
        if ((float) $this->allocated <= 0) {
            return 0.0;
        }

        return round(((float) $this->consumed / (float) $this->allocated) * 100, 2);
    }
}
