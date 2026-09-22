<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceAttestation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'governance_attestations';

    protected $fillable = [
        'tenant_id',
        'subject_type',
        'statement',
        'attestor',
        'version',
        'signature_hash',
        'attested_at',
    ];

    protected function casts(): array
    {
        return [
            'attested_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
