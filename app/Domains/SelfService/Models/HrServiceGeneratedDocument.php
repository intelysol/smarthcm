<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceGeneratedDocument extends Model
{
    use HasUuids;

    protected $table = 'hr_service_generated_documents';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'hr_service_template_id',
        'employee_id',
        'document_number',
        'title',
        'rendered_content',
        'pdf_file_path',
        'status',
        'approved_by_user_id',
        'approved_at',
        'acknowledged_at',
        'acknowledged_ip',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HrServiceTemplate::class, 'hr_service_template_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
