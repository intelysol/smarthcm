<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCompensationComponent extends Model
{
    use HasUuids;

    protected $table = 'employee_compensation_components';

    protected $fillable = [
        'tenant_id',
        'employee_compensation_id',
        'compensation_component_id',
        'calculation_type',
        'amount',
        'percentage',
        'formula',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'percentage' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function compensation(): BelongsTo
    {
        return $this->belongsTo(EmployeeCompensation::class, 'employee_compensation_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(CompensationComponent::class, 'compensation_component_id');
    }
}
