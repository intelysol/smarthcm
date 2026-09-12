<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityAssignmentBudget extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_assignment_budgets';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'budget_category',
        'approved_budget',
        'committed_amount',
        'actual_amount',
        'variance_amount',
        'currency',
    ];

    protected $casts = [
        'approved_budget' => 'decimal:4',
        'committed_amount' => 'decimal:4',
        'actual_amount' => 'decimal:4',
        'variance_amount' => 'decimal:4',
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
