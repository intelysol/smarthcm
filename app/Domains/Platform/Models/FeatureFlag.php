<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureFlag extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['tenant_id', 'key', 'name', 'description', 'is_enabled', 'targeting_rules'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'targeting_rules' => 'array'];
    }
}
