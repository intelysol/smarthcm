<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaimDocument extends Model
{
    use HasUuids;

    protected $table = 'insurance_claim_documents';

    protected $fillable = [
        'tenant_id',
        'benefit_claim_id',
        'document_type',
        'title',
        'file_path',
        'mime_type',
        'file_size',
        'is_sensitive',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_sensitive' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(InsuranceClaim::class, 'benefit_claim_id');
    }
}
