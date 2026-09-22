<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceControlTest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'governance_control_tests';

    protected $fillable = [
        'tenant_id',
        'control_id',
        'test_procedure',
        'tested_by',
        'result',
        'evidence_reference',
        'evidence_hash_sha256',
        'tested_at',
    ];

    protected function casts(): array
    {
        return [
            'tested_at' => 'datetime',
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
