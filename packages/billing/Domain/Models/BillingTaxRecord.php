<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BillingTaxRecord extends Model
{
    use HasUuids;

    protected $table = 'billing_tax_records';

    protected $fillable = [
        'id',
        'country_code',
        'region_code',
        'tax_name',
        'rate_percent',
        'is_compound',
        'is_active',
    ];

    protected $casts = [
        'rate_percent' => 'decimal:3',
        'is_compound' => 'boolean',
        'is_active' => 'boolean',
    ];
}
