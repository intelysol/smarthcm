<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsChecklistInstance extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_checklist_instances';

    protected $fillable = [
        'tenant_id',
        'template_id',
        'employee_id',
        'reference_number',
        'status',
        'target_completion_date',
        'actual_completion_date',
    ];

    protected $casts = [
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OpsChecklistTemplate::class, 'template_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpsChecklistItem::class, 'checklist_instance_id');
    }
}
