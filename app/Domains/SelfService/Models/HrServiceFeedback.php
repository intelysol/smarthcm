<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceFeedback extends Model
{
    use HasUuids;

    protected $table = 'hr_service_feedback';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'employee_id',
        'rating',
        'satisfaction_level',
        'comments',
        'timeliness_rating',
        'knowledge_rating',
        'helpfulness_rating',
    ];

    protected $casts = [
        'rating' => 'integer',
        'timeliness_rating' => 'integer',
        'knowledge_rating' => 'integer',
        'helpfulness_rating' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
