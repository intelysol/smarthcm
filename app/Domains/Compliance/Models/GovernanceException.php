<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceException extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'governance_exceptions';

    protected $fillable = [
        'tenant_id',
        'control_id',
        'reason',
        'business_justification',
        'risk_level',
        'compensating_control',
        'approved_by',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(GovernanceControl::class, 'control_id');
    }
}
