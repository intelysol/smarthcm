<?php

namespace App\Domains\Api\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiChangelogEntry extends Model
{
    use HasUuids;

    protected $table = 'api_changelog_entries';

    protected $fillable = [
        'api_product_id',
        'version',
        'change_type',
        'summary',
        'details',
        'release_date',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'release_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ApiProduct::class, 'api_product_id');
    }
}
