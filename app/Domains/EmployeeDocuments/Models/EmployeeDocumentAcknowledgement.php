<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentAcknowledgement extends Model
{
    use HasUuids;

    protected $table = 'employee_document_acknowledgements';

    protected $fillable = [
        'tenant_id',
        'employee_document_id',
        'employee_id',
        'document_version',
        'acknowledged_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'document_version' => 'integer',
        'acknowledged_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
