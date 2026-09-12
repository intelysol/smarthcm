<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityExtension extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_extensions';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'extension_number',
        'current_end_date',
        'proposed_end_date',
        'extension_reason',
        'additional_estimated_cost',
        'currency',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'current_end_date' => 'date',
        'proposed_end_date' => 'date',
        'additional_estimated_cost' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
