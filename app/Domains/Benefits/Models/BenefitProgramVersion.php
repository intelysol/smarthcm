<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitProgramVersion extends Model
{
    use HasUuids;

    protected $table = 'benefit_program_versions';

    protected $fillable = [
        'tenant_id',
        'benefit_program_id',
        'version_number',
        'effective_from',
        'effective_to',
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

    public function program(): BelongsTo
    {
        return $this->belongsTo(BenefitProgram::class, 'benefit_program_id');
    }
}
