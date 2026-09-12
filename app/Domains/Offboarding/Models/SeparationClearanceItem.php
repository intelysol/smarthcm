<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationClearanceItem extends Model
{
    use HasUuids;

    protected $table = 'separation_clearance_items';

    protected $fillable = [
        'tenant_id',
        'separation_clearance_id',
        'title',
        'status',
        'is_blocking',
        'comments',
        'completed_at',
    ];

    protected $casts = [
        'is_blocking' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function clearance(): BelongsTo
    {
        return $this->belongsTo(SeparationClearance::class, 'separation_clearance_id');
    }
}
