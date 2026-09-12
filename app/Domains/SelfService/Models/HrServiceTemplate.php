<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrServiceTemplate extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hr_service_templates';

    protected $fillable = [
        'tenant_id',
        'hr_service_definition_id',
        'code',
        'name',
        'description',
        'template_type',
        'template_body',
        'placeholders',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(HrServiceDefinition::class, 'hr_service_definition_id');
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(HrServiceGeneratedDocument::class, 'hr_service_template_id');
    }
}
