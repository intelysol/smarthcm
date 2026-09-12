<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActionBulkItem extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_bulk_items';

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'employee_id',
        'payload',
        'validation_status',
        'validation_errors',
        'execution_status',
        'personnel_action_id',
        'execution_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'validation_errors' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionBulkBatch::class, 'batch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function personnelAction(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_id');
    }
}
