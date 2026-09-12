<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmEmployeeDataBulkBatch extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_data_bulk_batches';

    protected $fillable = [
        'tenant_id',
        'batch_number',
        'category',
        'uploaded_by',
        'total_items',
        'valid_items',
        'error_items',
        'processed_items',
        'status',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'valid_items' => 'integer',
        'error_items' => 'integer',
        'processed_items' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HcmEmployeeDataBulkItem::class, 'batch_id');
    }
}
