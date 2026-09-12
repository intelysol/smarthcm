<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentRequest extends Model
{
    use HasUuids;

    protected $table = 'employee_document_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'document_type_id',
        'requested_by',
        'requested_at',
        'due_date',
        'status',
        'instructions',
        'completed_at',
        'employee_document_id',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(HcmDocumentType::class, 'document_type_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }
}
