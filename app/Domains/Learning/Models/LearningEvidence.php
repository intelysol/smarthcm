<?php

namespace App\Domains\Learning\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningEvidence extends Model
{
    use HasUuids;

    protected $table = 'hcm_learning_evidence';

    protected $fillable = [
        'tenant_id',
        'external_record_id',
        'evidence_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'document_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function externalRecord(): BelongsTo
    {
        return $this->belongsTo(LearningExternalRecord::class, 'external_record_id');
    }
}
