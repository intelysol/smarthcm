<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompensationStructure extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'compensation_structures';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'currency',
        'description',
        'is_active',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function structureComponents(): HasMany
    {
        return $this->hasMany(CompensationStructureComponent::class)->orderBy('sequence');
    }
}
