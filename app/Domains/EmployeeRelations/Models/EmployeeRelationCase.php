<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRelationCase extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'employee_relation_cases';

    protected $fillable = [
        'tenant_id',
        'case_number',
        'case_type_id',
        'subject_employee_id',
        'subject_type',
        'subject_name',
        'title',
        'summary',
        'priority',
        'severity',
        'status',
        'confidentiality_level',
        'incident_date',
        'incident_location',
        'opened_at',
        'target_resolution_date',
        'closed_at',
        'closed_by',
        'is_anonymous',
        'is_locked',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'opened_at' => 'datetime',
            'target_resolution_date' => 'date',
            'closed_at' => 'datetime',
            'is_anonymous' => 'boolean',
            'is_locked' => 'boolean',
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

    public function subjectEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'subject_employee_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reporters(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseReporter::class, 'case_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseToken::class, 'case_id');
    }

    public function triage(): HasOne
    {
        return $this->hasOne(EmployeeRelationCaseTriage::class, 'case_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseAssignment::class, 'case_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseParticipant::class, 'case_id');
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseConflict::class, 'case_id');
    }

    public function investigations(): HasMany
    {
        return $this->hasMany(EmployeeRelationInvestigation::class, 'case_id');
    }

    public function investigationSteps(): HasMany
    {
        return $this->hasMany(EmployeeRelationInvestigationStep::class, 'case_id');
    }

    public function investigationQuestions(): HasMany
    {
        return $this->hasMany(EmployeeRelationInvestigationQuestion::class, 'case_id');
    }

    public function allegations(): HasMany
    {
        return $this->hasMany(EmployeeRelationAllegation::class, 'case_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(EmployeeRelationStatement::class, 'case_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(EmployeeRelationInterview::class, 'case_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(EmployeeRelationEvidence::class, 'case_id');
    }

    public function caseNotes(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseNote::class, 'case_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseTask::class, 'case_id');
    }

    public function slas(): HasMany
    {
        return $this->hasMany(EmployeeRelationCaseSla::class, 'case_id');
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(EmployeeRelationHearing::class, 'case_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(EmployeeRelationFinding::class, 'case_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(EmployeeRelationDecision::class, 'case_id');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(EmployeeRelationCorrectiveAction::class, 'case_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(EmployeeRelationAppeal::class, 'case_id');
    }

    public function correspondence(): HasMany
    {
        return $this->hasMany(EmployeeRelationCorrespondence::class, 'case_id');
    }

    public function legalHolds(): HasMany
    {
        return $this->hasMany(EmployeeRelationLegalHold::class, 'case_id');
    }

    public function hasActiveLegalHold(): bool
    {
        return $this->legalHolds()->where('status', 'active')->exists();
    }
}
