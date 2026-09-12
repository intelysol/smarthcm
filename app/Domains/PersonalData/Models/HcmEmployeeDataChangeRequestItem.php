<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeDataChangeRequestItem extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_data_change_request_items';

    protected $fillable = [
        'tenant_id',
        'change_request_id',
        'target_entity',
        'target_id',
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
        return $this->belongsTo(HcmEmployeeDataChangeRequest::class, 'change_request_id');
    }
}
