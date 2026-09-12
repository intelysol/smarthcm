<?php

declare(strict_types=1);

namespace App\Domains\Performance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyLevel extends Model
{
    use HasUuids;

    protected $fillable = [
        'competency_id',
        'level',
        'label',
        'description',
    ];

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }
}
