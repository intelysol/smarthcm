<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobilityRelocationCase extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_relocation_cases';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'case_number',
        'status',
        'relocation_provider_name',
        'provider_reference',
        'target_move_date',
        'actual_move_date',
        'family_relocating',
        'relocating_dependent_ids',
        'notes',
    ];

    protected $casts = [
        'target_move_date' => 'date',
        'actual_move_date' => 'date',
        'family_relocating' => 'boolean',
        'relocating_dependent_ids' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MobilityRelocationItem::class, 'relocation_case_id');
    }
}
