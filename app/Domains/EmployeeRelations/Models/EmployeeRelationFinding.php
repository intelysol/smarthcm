<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationFinding extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_findings';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'allegation_id',
        'investigator_id',
        'finding',
        'confidence',
        'rationale',
        'evidence_summary',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
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

    public function allegation(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationAllegation::class, 'allegation_id');
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }
}
