<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonnelActionType extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'personnel_action_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'description',
        'requires_approval',
        'requires_acknowledgement',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'requires_acknowledgement' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(PersonnelActionRequest::class, 'action_type_id');
    }
}
