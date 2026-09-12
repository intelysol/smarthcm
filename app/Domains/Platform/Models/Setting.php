<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'group', 'key', 'value', 'type', 'is_encrypted', 'created_by', 'updated_by', 'deleted_by'];

    protected function casts(): array
    {
        return ['value' => 'array', 'is_encrypted' => 'boolean'];
    }
}
