<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceAdmin\Enums\BulkOperationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OpsBulkOperation extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_bulk_operations';

    protected $fillable = [
        'tenant_id',
        'operation_number',
        'operation_type',
        'status',
        'reason',
        'effective_date',
        'proposed_changes',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'created_by',
        'approved_by',
        'approved_at',
        'executed_at',
    ];

    protected $casts = [
        'status' => BulkOperationStatus::class,
        'effective_date' => 'date',
        'proposed_changes' => 'array',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'successful_records' => 'integer',
        'failed_records' => 'integer',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpsBulkOperationItem::class, 'bulk_operation_id');
    }

    public function validation(): HasOne
    {
        return $this->hasOne(OpsBulkOperationValidation::class, 'bulk_operation_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(OpsBulkOperationError::class, 'bulk_operation_id');
    }
}
