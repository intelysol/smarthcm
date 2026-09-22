<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovernanceFramework extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'governance_frameworks';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'authority',
        'jurisdiction',
        'version',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function controls(): HasMany
    {
        return $this->hasMany(GovernanceControl::class, 'framework_id');
    }
}
