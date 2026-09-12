<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeDataBulkItem extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_data_bulk_items';

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'employee_identifier',
        'resolved_employee_id',
        'payload',
        'status',
        'validation_errors',
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
        return $this->belongsTo(HcmEmployeeDataBulkBatch::class, 'batch_id');
    }

    public function resolvedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'resolved_employee_id');
    }
}
