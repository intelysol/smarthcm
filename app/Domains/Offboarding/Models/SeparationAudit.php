<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationAudit extends Model
{
    use HasUuids;

    protected $table = 'separation_audits';

    protected $fillable = [
        'tenant_id',
        'separation_request_id',
        'actor_id',
        'event_name',
        'old_state',
        'new_state',
        'reason',
        'ip_address',
    ];

    protected $casts = [
        'old_state' => 'array',
        'new_state' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SeparationRequest::class, 'separation_request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
