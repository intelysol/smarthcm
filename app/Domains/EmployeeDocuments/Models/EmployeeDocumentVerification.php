<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentVerification extends Model
{
    use HasUuids;

    protected $table = 'employee_document_verifications';

    protected $fillable = [
        'tenant_id',
        'employee_document_id',
        'reviewer_id',
        'decision',
        'reason',
        'version',
        'comments',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
