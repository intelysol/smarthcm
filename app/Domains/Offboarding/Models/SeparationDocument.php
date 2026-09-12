<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationDocument extends Model
{
    use HasUuids;

    protected $table = 'separation_documents';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'document_type',
        'title',
        'file_path',
        'employee_accessible',
        'generated_at',
    ];

    protected $casts = [
        'employee_accessible' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }
}
