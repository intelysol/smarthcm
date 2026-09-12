<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentBulkItem extends Model
{
    use HasUuids;

    protected $table = 'employee_document_bulk_items';

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'employee_identifier',
        'resolved_employee_id',
        'file_name',
        'status',
        'validation_errors',
        'employee_document_id',
    ];

    protected $casts = [
        'validation_errors' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocumentBulkBatch::class, 'batch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'resolved_employee_id');
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }
}
