<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationHearingOutcome extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_hearing_outcomes';

    protected $fillable = [
        'tenant_id',
        'hearing_id',
        'case_id',
        'entered_by',
        'summary',
        'recommendations',
        'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationHearing::class, 'hearing_id');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function enteredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
