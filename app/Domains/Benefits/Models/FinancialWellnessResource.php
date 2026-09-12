<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialWellnessResource extends Model
{
    use HasUuids;

    protected $table = 'financial_wellness_resources';

    protected $fillable = [
        'tenant_id',
        'financial_wellness_program_id',
        'title',
        'resource_type',
        'summary',
        'url_or_path',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(FinancialWellnessProgram::class, 'financial_wellness_program_id');
    }
}
