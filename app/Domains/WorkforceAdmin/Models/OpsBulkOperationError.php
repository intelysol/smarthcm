<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsBulkOperationError extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'hcm_ops_bulk_operation_errors';

    protected $fillable = [
        'tenant_id',
        'bulk_operation_id',
        'bulk_operation_item_id',
        'employee_id',
        'error_code',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bulkOperation(): BelongsTo
    {
        return $this->belongsTo(OpsBulkOperation::class, 'bulk_operation_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OpsBulkOperationItem::class, 'bulk_operation_item_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
