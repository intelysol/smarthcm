<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HrServiceVersion extends Model
{
    use HasUuids;

    protected $table = 'hr_service_versions';

    protected $fillable = [
        'tenant_id',
        'hr_service_definition_id',
        'version_number',
        'effective_from',
        'effective_to',
        'change_summary',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(HrServiceDefinition::class, 'hr_service_definition_id');
    }

    public function formDefinition(): HasOne
    {
        return $this->hasOne(HrServiceFormDefinition::class, 'hr_service_version_id');
    }
}
