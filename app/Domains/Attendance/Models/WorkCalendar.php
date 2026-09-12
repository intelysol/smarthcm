<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCalendar extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'work_calendars';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'location_id',
        'code',
        'name',
        'timezone',
        'description',
        'is_default',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(WorkCalendarDay::class)->orderBy('day_of_week');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(WorkCalendarException::class)->orderBy('exception_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
