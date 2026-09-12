<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationInterview extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_interviews';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'participant_id',
        'investigator_id',
        'scheduled_at',
        'started_at',
        'completed_at',
        'location',
        'format',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
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

    public function participant(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCaseParticipant::class, 'participant_id');
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(EmployeeRelationInterviewNote::class, 'interview_id');
    }
}
