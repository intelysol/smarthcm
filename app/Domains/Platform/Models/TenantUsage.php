<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantUsage extends Model
{
    use HasUuids;

    protected $table = 'tenant_usage';
    protected $fillable = ['tenant_id', 'metrics'];

    protected function casts(): array
    {
        return ['metrics' => 'array'];
    }
}
