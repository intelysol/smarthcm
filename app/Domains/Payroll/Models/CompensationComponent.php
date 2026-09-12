<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Payroll\Enums\CalculationType;
use App\Domains\Payroll\Enums\ComponentType;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompensationComponent extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'compensation_components';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'component_type',
        'calculation_type',
        'default_amount',
        'percentage',
        'formula',
        'is_taxable',
        'is_pensionable',
        'is_overtime_eligible',
        'is_recurring',
        'is_statutory',
        'is_active',
        'priority_order',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_amount' => 'decimal:4',
        'percentage' => 'decimal:4',
        'is_taxable' => 'boolean',
        'is_pensionable' => 'boolean',
        'is_overtime_eligible' => 'boolean',
        'is_recurring' => 'boolean',
        'is_statutory' => 'boolean',
        'is_active' => 'boolean',
        'priority_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isEarning(): bool
    {
        $type = ComponentType::tryFrom($this->component_type);
        return $type?->isEarning() ?? false;
    }

    public function isDeduction(): bool
    {
        $type = ComponentType::tryFrom($this->component_type);
        return $type?->isDeduction() ?? false;
    }
}
