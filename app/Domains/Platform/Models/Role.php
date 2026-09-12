<?php

namespace App\Domains\Platform\Models;

use App\Domains\Shared\Models\Permission;
use App\Models\User;
use Database\Factories\Domains\Platform\RoleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['tenant_id', 'uuid', 'name', 'code', 'label', 'description', 'type', 'status', 'priority', 'is_system', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'priority' => 'integer'];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('assigned_by')->withTimestamps();
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
