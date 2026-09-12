<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Documents\Models\Document;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationEvidence extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_evidence';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'evidence_number',
        'title',
        'description',
        'evidence_type',
        'source',
        'collected_by',
        'received_at',
        'file_path',
        'document_id',
        'sha256_hash',
        'confidentiality',
        'status',
        'is_relevant',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'is_relevant' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(EmployeeRelationEvidenceHistory::class, 'evidence_id');
    }
}
