<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationStatementVersion extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $table = 'employee_relation_statement_versions';

    protected $fillable = [
        'tenant_id',
        'statement_id',
        'version_number',
        'content',
        'change_reason',
        'submitted_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function statement(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationStatement::class, 'statement_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
