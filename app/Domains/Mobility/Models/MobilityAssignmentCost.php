<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityAssignmentCost extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_assignment_costs';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'cost_category',
        'cost_name',
        'source_amount',
        'source_currency',
        'exchange_rate',
        'exchange_rate_date',
        'converted_amount',
        'converted_currency',
        'frequency',
        'effective_from',
        'effective_to',
        'notes',
    ];

    protected $casts = [
        'source_amount' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'exchange_rate_date' => 'date',
        'converted_amount' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }
}
