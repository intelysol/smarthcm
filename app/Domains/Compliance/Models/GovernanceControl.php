<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovernanceControl extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'governance_controls';

    protected $fillable = [
        'tenant_id',
        'framework_id',
        'code',
        'title',
        'objective',
        'control_type',
        'frequency',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function framework(): BelongsTo
    {
        return $this->belongsTo(GovernanceFramework::class, 'framework_id');
    }

    public function tests(): HasMany
    {
        return $this->hasMany(GovernanceControlTest::class, 'control_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(GovernanceFinding::class, 'control_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(GovernanceException::class, 'control_id');
    }
}
