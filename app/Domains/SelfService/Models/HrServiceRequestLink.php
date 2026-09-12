<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceRequestLink extends Model
{
    use HasUuids;

    protected $table = 'hr_service_request_links';

    protected $fillable = [
        'tenant_id',
        'parent_request_id',
        'child_request_id',
        'link_type',
        'notes',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parentRequest(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'parent_request_id');
    }

    public function childRequest(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'child_request_id');
    }
}
