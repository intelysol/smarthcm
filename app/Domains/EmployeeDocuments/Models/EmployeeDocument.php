<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Documents\Models\Document;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_employee_documents';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'document_type_id',
        'document_id',
        'title',
        'document_number',
        'issue_date',
        'expiry_date',
        'status',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'confidentiality_level',
        'employee_visible',
        'manager_visible',
        'source',
        'related_type',
        'related_id',
        'metadata',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'employee_visible' => 'boolean',
        'manager_visible' => 'boolean',
        'metadata' => 'array',
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

    public function sharedDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(EmployeeDocumentVerification::class, 'employee_document_id');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(EmployeeDocumentAcknowledgement::class, 'employee_document_id');
    }

    public function expirationEvents(): HasMany
    {
        return $this->hasMany(EmployeeDocumentExpirationEvent::class, 'employee_document_id');
    }

    public function requirement(): HasOne
    {
        return $this->hasOne(EmployeeDocumentRequirement::class, 'employee_document_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(EmployeeDocumentAudit::class, 'employee_document_id');
    }
}
