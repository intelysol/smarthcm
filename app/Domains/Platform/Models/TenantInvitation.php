<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantInvitation extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'email', 'token', 'status', 'role_placeholder', 'invited_by', 'expires_at', 'accepted_at', 'rejected_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime', 'rejected_at' => 'datetime'];
    }
}
