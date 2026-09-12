<?php

namespace App\Domains\Compensation\Models;

use App\Domains\Documents\Models\Document;
use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TotalRewardsStatement extends CompensationModel
{
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'year',
        'components',
        'total_employer_investment',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'components' => 'array',
            'total_employer_investment' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
