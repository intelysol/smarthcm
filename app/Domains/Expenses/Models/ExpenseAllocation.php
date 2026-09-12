<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseAllocation extends Model
{
    use HasUuids;

    protected $table = 'expense_allocations';

    protected $fillable = [
        'tenant_id',
        'expense_claim_line_id',
        'department_id',
        'cost_center_id',
        'project_id',
        'allocation_percentage',
        'allocated_amount',
        'currency',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'allocated_amount' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claimLine(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaimLine::class, 'expense_claim_line_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
