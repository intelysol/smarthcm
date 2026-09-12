<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityExpenseLink extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_expense_links';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'link_type',
        'entity_id',
        'amount',
        'currency',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
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
