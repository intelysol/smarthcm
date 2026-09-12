<?php

namespace App\Domains\Shared\Models;

use App\Domains\Platform\Enums\TenantStatus;
use App\Domains\Organization\Models\Company;
use App\Models\User;
use Database\Factories\Domains\Shared\TenantFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'uuid',
        'tenant_code',
        'name',
        'legal_name',
        'slug',
        'status',
        'type',
        'timezone',
        'locale',
        'currency',
        'country_code',
        'primary_email',
        'primary_phone',
        'website',
        'logo',
        'is_active',
        'activated_at',
        'suspended_at',
        'version',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status' => TenantStatus::class,
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    /**
     * @return BelongsTo<Tenant, Tenant>
     */
    public function parentTenant(): BelongsTo
    {
        return $this->belongsTo(self::class, 'tenant_id');
    }

    /**
     * @return HasMany<Company>
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return BelongsToMany<User> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')->withPivot(['status', 'role_placeholder', 'joined_at'])->withTimestamps();
    }

    public function isAccessible(): bool
    {
        return $this->status instanceof TenantStatus ? $this->status->isAccessible() : $this->is_active;
    }
}
