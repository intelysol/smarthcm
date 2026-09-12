<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityChange extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_changes';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'change_type',
        'previous_values',
        'proposed_values',
        'effective_date',
        'reason',
        'personnel_action_request_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'previous_values' => 'array',
        'proposed_values' => 'array',
        'effective_date' => 'date',
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

    public function personnelActionRequest(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
