<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationInterviewNote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_interview_notes';

    protected $fillable = [
        'tenant_id',
        'interview_id',
        'case_id',
        'author_id',
        'notes',
        'is_confidential',
        'visibility',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'is_confidential' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationInterview::class, 'interview_id');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
