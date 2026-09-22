<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantDelegation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_delegations';

    protected $fillable = [
        'tenant_id',
        'delegator_user_id',
        'delegate_user_id',
        'scope',
        'permissions',
        'reason',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'permissions' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function isCurrentlyActive(): bool
    {
        return $this->status === 'ACTIVE' && now()->between($this->starts_at, $this->ends_at);
    }
}
