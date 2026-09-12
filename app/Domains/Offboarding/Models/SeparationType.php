<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeparationType extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'separation_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'notice_days_default',
        'requires_clearance',
        'requires_exit_interview',
        'is_active',
    ];

    protected $casts = [
        'notice_days_default' => 'integer',
        'requires_clearance' => 'boolean',
        'requires_exit_interview' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(SeparationRequest::class, 'separation_type_id');
    }
}
