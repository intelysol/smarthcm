<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialWellnessProgram extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'financial_wellness_programs';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'program_type',
        'description',
        'partner_organization',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(FinancialWellnessResource::class);
    }
}
