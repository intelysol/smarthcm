<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingProduct extends Model
{
    use HasUuids;

    protected $table = 'billing_products';

    protected $fillable = [
        'id',
        'name',
        'code',
        'description',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(BillingProductVersion::class, 'product_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(BillingPlan::class, 'product_id');
    }
}
