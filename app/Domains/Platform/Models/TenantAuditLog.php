<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantAuditLog extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'actor_id', 'action', 'metadata', 'ip_address', 'occurred_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'occurred_at' => 'datetime'];
    }
}
