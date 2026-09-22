<?php

declare(strict_types=1);

namespace App\Domains\PublicWebsite\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicLead extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'public_leads';

    protected $fillable = [
        'type',
        'name',
        'company',
        'work_email',
        'phone',
        'country',
        'organization_size',
        'hcm_requirements',
        'message',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
