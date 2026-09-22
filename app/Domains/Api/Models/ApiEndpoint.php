<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiEndpoint extends Model
{
    use HasUuids;

    protected $table = 'api_endpoints';

    protected $fillable = [
        'api_product_id',
        'method',
        'path',
        'resource',
        'required_scopes',
        'field_policies',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'required_scopes' => 'array',
            'field_policies' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ApiProduct::class, 'api_product_id');
    }
}
