<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRelationCaseType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'employee_relation_case_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'category',
        'default_priority',
        'default_severity',
        'default_confidentiality',
        'is_anonymous_allowed',
        'is_self_service_allowed',
        'is_system',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous_allowed' => 'boolean',
            'is_self_service_allowed' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(EmployeeRelationCase::class, 'case_type_id');
    }
}
