<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActionReversal extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_reversals';

    protected $fillable = [
        'tenant_id',
        'original_action_id',
        'reversal_action_id',
        'reason',
        'requested_by',
        'approved_by',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function originalAction(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'original_action_id');
    }

    public function reversalAction(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'reversal_action_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
