<?php

namespace App\Domains\Compensation\Models;

use App\Domains\Organization\Models\JobGrade;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompensationBand extends CompensationModel
{
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'job_grade_id',
        'currency',
        'minimum',
        'midpoint',
        'maximum',
        'location',
        'effective_from',
        'effective_to',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'minimum' => 'decimal:2',
            'midpoint' => 'decimal:2',
            'maximum' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'version' => 'integer',
        ];
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }
}
