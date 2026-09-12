<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationInvestigation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_investigations';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'investigator_id',
        'scope',
        'start_date',
        'target_completion_date',
        'status',
        'completed_at',
        'summary_findings',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_completion_date' => 'date',
            'completed_at' => 'datetime',
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

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(EmployeeRelationInvestigationStep::class, 'investigation_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EmployeeRelationInvestigationQuestion::class, 'investigation_id');
    }
}
