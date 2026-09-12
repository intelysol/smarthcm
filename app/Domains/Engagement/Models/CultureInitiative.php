<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CultureInitiative extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'culture_initiatives';

    protected $fillable = [
        'tenant_id',
        'code',
        'title',
        'description',
        'category',
        'owner_id',
        'start_date',
        'end_date',
        'status',
        'employees_reached',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'employees_reached' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CultureInitiativeAction::class, 'culture_initiative_id');
    }
}
