<?php

namespace App\Domains\EmployeeProfile\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfileChangeRequestItem extends Model
{
    use HasUuids;

    protected $table = 'employee_profile_change_request_items';

    protected $fillable = [
        'tenant_id',
        'change_request_id',
        'field_name',
        'old_value',
        'new_value',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function changeRequest(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfileChangeRequest::class, 'change_request_id');
    }
}
