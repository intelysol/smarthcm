<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentAudit extends Model
{
    use HasUuids;

    protected $table = 'employee_document_audits';

    protected $fillable = [
        'tenant_id',
        'employee_document_id',
        'actor_id',
        'event_name',
        'old_state',
        'new_state',
        'reason',
        'ip_address',
    ];

    protected $casts = [
        'old_state' => 'array',
        'new_state' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
