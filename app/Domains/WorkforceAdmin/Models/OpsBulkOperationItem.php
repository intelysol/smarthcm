<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsBulkOperationItem extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_bulk_operation_items';

    protected $fillable = [
        'tenant_id',
        'bulk_operation_id',
        'employee_id',
        'current_values',
        'target_values',
        'status',
        'execution_error',
    ];

    protected $casts = [
        'current_values' => 'array',
        'target_values' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bulkOperation(): BelongsTo
    {
        return $this->belongsTo(OpsBulkOperation::class, 'bulk_operation_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
