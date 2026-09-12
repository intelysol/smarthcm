<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsChecklistItem extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_checklist_items';

    protected $fillable = [
        'tenant_id',
        'checklist_instance_id',
        'category',
        'title',
        'status',
        'assigned_to',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(OpsChecklistInstance::class, 'checklist_instance_id');
    }

    public function checklistInstance(): BelongsTo
    {
        return $this->belongsTo(OpsChecklistInstance::class, 'checklist_instance_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
