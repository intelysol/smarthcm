<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class TenantConfiguration extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'configuration'];

    protected function casts(): array
    {
        return ['configuration' => 'array'];
    }
}
