<?php

declare(strict_types=1);

namespace App\Domains\Operations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataLifecyclePolicy extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'data_lifecycle_policies';

    protected $fillable = [
        'tenant_id',
        'domain',
        'data_class',
        'classification',
        'active_days',
        'retention_days',
        'archive_strategy',
        'legal_hold_supported',
        'is_system_locked',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'active_days' => 'integer',
            'retention_days' => 'integer',
            'legal_hold_supported' => 'boolean',
            'is_system_locked' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
