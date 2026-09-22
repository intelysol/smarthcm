<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BillingUsageMeter extends Model
{
    use HasUuids;

    protected $table = 'billing_usage_meters';

    protected $fillable = [
        'id',
        'meter_key',
        'name',
        'aggregation_type',
        'reset_frequency',
        'description',
    ];
}
