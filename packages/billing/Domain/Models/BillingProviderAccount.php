<?php

declare(strict_types=1);

namespace Flow\Packages\Billing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BillingProviderAccount extends Model
{
    use HasUuids;

    protected $table = 'billing_provider_accounts';

    protected $fillable = [
        'id',
        'provider_code',
        'name',
        'is_enabled',
        'is_sandbox',
        'config',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_sandbox' => 'boolean',
        'config' => 'array',
    ];
}
