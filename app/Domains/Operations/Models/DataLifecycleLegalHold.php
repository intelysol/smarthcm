<?php

declare(strict_types=1);

namespace App\Domains\Operations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataLifecycleLegalHold extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'data_lifecycle_legal_holds';

    protected $fillable = [
        'tenant_id',
        'hold_reference',
        'scope_type',
        'scope_id',
        'data_classes',
        'reason',
        'created_by',
        'effective_from',
        'effective_until',
        'status',
        'released_by',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'data_classes' => 'array',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
