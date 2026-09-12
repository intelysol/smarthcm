<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitCategory extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_categories';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category_type',
        'description',
        'is_statutory',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_statutory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(BenefitPlan::class);
    }
}
