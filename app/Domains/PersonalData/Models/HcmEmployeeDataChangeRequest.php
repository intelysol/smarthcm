<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmEmployeeDataChangeRequest extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_data_change_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'request_number',
        'category',
        'status',
        'effective_date',
        'reason',
        'supporting_document_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HcmEmployeeDataChangeRequestItem::class, 'change_request_id');
    }
}
