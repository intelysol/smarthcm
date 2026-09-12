<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantDomain extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'domain', 'type', 'is_primary', 'is_verified', 'verification_token', 'verified_at', 'ssl_status', 'status'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'is_verified' => 'boolean', 'verified_at' => 'datetime'];
    }
}
