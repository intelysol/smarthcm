<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmDocumentType extends Model
{
    use HasUuids;

    protected $table = 'hcm_document_types';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'code',
        'name',
        'description',
        'requires_verification',
        'requires_acknowledgement',
        'expires',
        'default_validity_days',
        'retention_years',
        'confidentiality_level',
        'employee_visible',
        'manager_visible',
        'is_active',
        'metadata_schema',
    ];

    protected $casts = [
        'requires_verification' => 'boolean',
        'requires_acknowledgement' => 'boolean',
        'expires' => 'boolean',
        'employee_visible' => 'boolean',
        'manager_visible' => 'boolean',
        'is_active' => 'boolean',
        'default_validity_days' => 'integer',
        'retention_years' => 'integer',
        'metadata_schema' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HcmDocumentCategory::class, 'category_id');
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'document_type_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(EmployeeDocumentRequirement::class, 'document_type_id');
    }
}
