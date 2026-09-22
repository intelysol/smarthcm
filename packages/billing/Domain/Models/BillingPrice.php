<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Flow\Packages\Billing\Domain\Enums\PricingModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPrice extends Model
{
    use HasUuids;

    protected $table = 'billing_prices';

    protected $fillable = [
        'id',
        'plan_id',
        'component_key',
        'pricing_model',
        'unit_name',
        'unit_price',
        'min_quantity',
        'max_quantity',
        'included_quantity',
        'overage_price',
        'currency',
        'tiers_config',
    ];

    protected $casts = [
        'pricing_model' => PricingModel::class,
        'unit_price' => 'decimal:4',
        'overage_price' => 'decimal:4',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'included_quantity' => 'integer',
        'tiers_config' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class, 'plan_id');
    }
}
