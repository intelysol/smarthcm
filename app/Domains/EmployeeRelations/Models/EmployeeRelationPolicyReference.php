<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationPolicyReference extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_policy_references';

    protected $fillable = [
        'tenant_id',
        'policy_code',
        'policy_name',
        'category',
        'section_clause',
        'description',
        'document_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function allegations(): HasMany
    {
        return $this->hasMany(EmployeeRelationAllegation::class, 'policy_reference_id');
    }
}
