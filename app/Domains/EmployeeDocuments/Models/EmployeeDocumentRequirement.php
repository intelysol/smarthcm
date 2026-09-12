<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentRequirement extends Model
{
    use HasUuids;

    protected $table = 'employee_document_requirements';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'document_type_id',
        'is_mandatory',
        'status',
        'employee_document_id',
        'due_date',
        'waived_by',
        'waived_at',
        'waiver_reason',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'due_date' => 'date',
        'waived_at' => 'datetime',
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

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function waiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
}
