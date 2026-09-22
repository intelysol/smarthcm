<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceFinding extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'governance_findings';

    protected $fillable = [
        'tenant_id',
        'control_id',
        'title',
        'severity',
        'owner',
        'remediation_plan',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
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
