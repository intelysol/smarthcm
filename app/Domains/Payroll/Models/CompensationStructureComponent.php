<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationStructureComponent extends Model
{
    use HasUuids;

    protected $table = 'compensation_structure_components';

    protected $fillable = [
        'tenant_id',
        'compensation_structure_id',
        'compensation_component_id',
        'calculation_type',
        'default_amount',
        'percentage',
        'formula',
        'sequence',
    ];

    protected $casts = [
        'default_amount' => 'decimal:4',
        'percentage' => 'decimal:4',
        'sequence' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(CompensationStructure::class, 'compensation_structure_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(CompensationComponent::class, 'compensation_component_id');
    }
}
