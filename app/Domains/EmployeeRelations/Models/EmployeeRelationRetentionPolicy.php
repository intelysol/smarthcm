<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationRetentionPolicy extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_retention_policies';

    protected $fillable = [
        'tenant_id',
        'name',
        'case_type_id',
        'severity',
        'retention_years',
        'legal_basis',
        'auto_dispose_eligible',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'retention_years' => 'integer',
            'auto_dispose_eligible' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function caseType(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCaseType::class, 'case_type_id');
    }
}
