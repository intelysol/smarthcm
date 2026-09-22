<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRateLimitPolicy extends Model
{
    use HasUuids;

    protected $table = 'api_rate_limit_policies';

    protected $fillable = [
        'api_product_id',
        'api_client_id',
        'requests_per_minute',
        'burst_limit',
    ];

    protected function casts(): array
    {
        return [
            'requests_per_minute' => 'integer',
            'burst_limit' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ApiProduct::class, 'api_product_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
