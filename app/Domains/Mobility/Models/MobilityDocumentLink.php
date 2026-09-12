<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Employee\Models\EmployeeDocument;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityDocumentLink extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_document_links';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'document_type',
        'document_id',
        'verification_status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'document_id');
    }
}
