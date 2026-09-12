<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityRelocationItem extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_relocation_items';

    protected $fillable = [
        'tenant_id',
        'relocation_case_id',
        'item_type',
        'title',
        'status',
        'scheduled_date',
        'completed_date',
        'cost_estimate',
        'currency',
        'details',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost_estimate' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function relocationCase(): BelongsTo
    {
        return $this->belongsTo(MobilityRelocationCase::class, 'relocation_case_id');
    }
}
