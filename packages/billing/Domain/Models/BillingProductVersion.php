<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingProductVersion extends Model
{
    use HasUuids;

    protected $table = 'billing_product_versions';

    protected $fillable = [
        'id',
        'product_id',
        'version',
        'is_active',
        'release_notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(BillingProduct::class, 'product_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(BillingPlan::class, 'version_id');
    }
}
