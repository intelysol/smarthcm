<?php

namespace App\Models;

use App\Domains\Platform\Models\Role;
use App\Domains\Platform\Services\AuthorizationService;
use App\Domains\Platform\Models\UserPreference;
use App\Domains\Platform\Models\UserProfile;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\Tenant;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['tenant_id', 'name', 'username', 'email', 'phone', 'password', 'status', 'is_platform_admin', 'locale', 'timezone', 'avatar_path', 'mfa_enabled', 'created_by', 'updated_by', 'deleted_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_expires_at' => 'datetime',
            'locked_at' => 'datetime',
            'last_password_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'is_platform_admin' => 'boolean',
        ];
    }


    /**
     * @return BelongsTo<Tenant, User>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsToMany<Tenant> */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')->withPivot(['status', 'role_placeholder', 'joined_at'])->withTimestamps();
    }

    public function canAccessTenant(Tenant $tenant): bool
    {
        return $this->is_platform_admin || $this->tenants()->whereKey($tenant)->wherePivot('status', 'active')->exists() || (string) $this->tenant_id === (string) $tenant->getKey();
    }

    /**
     * @return BelongsToMany<Permission>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /** @return BelongsToMany<Role> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withPivot('assigned_by')->withTimestamps();
    }

    /** @return HasOne<UserProfile> */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /** @return HasOne<UserPreference> */
    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(\App\Domains\Employee\Models\Employee::class, 'user_id');
    }

    public function getEmployeeIdAttribute(): ?string
    {
        return $this->attributes['employee_id'] ?? $this->employee?->id;
    }

    public function hasPermission(string $permission): bool
    {
        return app(AuthorizationService::class)->can($this, $permission)
            || $this->permissions()->where('name', '*')->exists()
            || $this->roles()->whereHas('permissions', fn ($query) => $query->where('name', '*'))->exists();
    }
}
