<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelActionImpact extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_impacts';

    protected $fillable = [
        'tenant_id',
        'personnel_action_request_id',
        'domain',
        'impact_type',
        'severity',
        'status',
        'message',
        'source',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionRequest::class, 'personnel_action_request_id');
    }
}
