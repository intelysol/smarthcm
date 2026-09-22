<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiProduct extends Model
{
    use HasUuids;

    protected $table = 'api_products';

    protected $fillable = [
        'name',
        'version',
        'status',
        'openapi',
        'sunset_on',
    ];

    protected function casts(): array
    {
        return [
            'openapi' => 'array',
            'sunset_on' => 'date',
        ];
    }

    public function endpoints(): HasMany
    {
        return $this->hasMany(ApiEndpoint::class, 'api_product_id');
    }

    public function rateLimitPolicies(): HasMany
    {
        return $this->hasMany(ApiRateLimitPolicy::class, 'api_product_id');
    }

    public function changelogEntries(): HasMany
    {
        return $this->hasMany(ApiChangelogEntry::class, 'api_product_id');
    }
}
