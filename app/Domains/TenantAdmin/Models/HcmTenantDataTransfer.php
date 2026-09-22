<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantDataTransfer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_data_transfers';

    protected $fillable = [
        'tenant_id',
        'transfer_type',
        'domain',
        'file_name',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'warnings_count',
        'error_report',
        'initiated_by_user_id',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
        'warnings_count' => 'integer',
        'error_report' => 'array',
    ];
}
