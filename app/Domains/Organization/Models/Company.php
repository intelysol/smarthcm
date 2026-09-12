<?php

namespace App\Domains\Organization\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Factories\Domains\Organization\CompanyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'legal_name',
        'registration_number',
        'tax_number',
        'ntn',
        'strn',
        'industry',
        'company_size',
        'logo_path',
        'email',
        'phone',
        'mobile',
        'fax',
        'website',
        'timezone',
        'currency',
        'fiscal_year_start',
        'address',
        'country',
        'state',
        'city',
        'postal_code',
        'status',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }

    /**
     * @return BelongsTo<Tenant, Company>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<User, Company>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Branch>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * @return HasMany<BusinessUnit>
     */
    public function businessUnits(): HasMany
    {
        return $this->hasMany(BusinessUnit::class);
    }

    /**
     * @return HasMany<HolidayCalendar>
     */
    public function holidayCalendars(): HasMany
    {
        return $this->hasMany(HolidayCalendar::class);
    }
}
