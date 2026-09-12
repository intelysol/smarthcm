<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Organization\Models\Company;
use App\Domains\HR\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityCostAllocation extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_cost_allocations';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'entity_role',
        'company_id',
        'department_id',
        'cost_center_code',
        'allocation_percentage',
        'allocated_amount',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'allocated_amount' => 'decimal:4',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
