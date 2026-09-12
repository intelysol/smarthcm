<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentExpirationEvent extends Model
{
    use HasUuids;

    protected $table = 'employee_document_expiration_events';

    protected $fillable = [
        'tenant_id',
        'employee_document_id',
        'milestone',
        'recorded_at',
        'notified',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'notified' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }
}
