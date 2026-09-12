<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationCaseReporter extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_case_reporters';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'reporter_type',
        'reporter_employee_id',
        'reporter_user_id',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'is_anonymous',
        'allow_followup',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'allow_followup' => 'boolean',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporter_employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }
}
