<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDocumentBulkBatch extends Model
{
    use HasUuids;

    protected $table = 'employee_document_bulk_batches';

    protected $fillable = [
        'tenant_id',
        'batch_number',
        'document_type_id',
        'uploaded_by',
        'total_items',
        'valid_items',
        'warning_items',
        'error_items',
        'processed_items',
        'status',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'valid_items' => 'integer',
        'warning_items' => 'integer',
        'error_items' => 'integer',
        'processed_items' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(HcmDocumentType::class, 'document_type_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EmployeeDocumentBulkItem::class, 'batch_id');
    }
}
