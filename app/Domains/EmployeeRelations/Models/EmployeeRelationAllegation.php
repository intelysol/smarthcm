<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationAllegation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_allegations';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'allegation_number',
        'title',
        'description',
        'policy_reference_id',
        'incident_date',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function policyReference(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationPolicyReference::class, 'policy_reference_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(EmployeeRelationFinding::class, 'allegation_id');
    }
}
