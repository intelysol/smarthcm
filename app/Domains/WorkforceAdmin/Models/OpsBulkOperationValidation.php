<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsBulkOperationValidation extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'hcm_ops_bulk_operation_validations';

    protected $fillable = [
        'tenant_id',
        'bulk_operation_id',
        'valid_count',
        'warning_count',
        'error_count',
        'impacted_domains',
        'validation_summary',
        'validated_at',
    ];

    protected $casts = [
        'valid_count' => 'integer',
        'warning_count' => 'integer',
        'error_count' => 'integer',
        'impacted_domains' => 'array',
        'validation_summary' => 'array',
        'validated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bulkOperation(): BelongsTo
    {
        return $this->belongsTo(OpsBulkOperation::class, 'bulk_operation_id');
    }
}
