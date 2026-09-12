<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CultureInitiativeAction extends EngagementModel
{
    protected $table = 'culture_initiative_actions';

    protected $fillable = [
        'tenant_id',
        'culture_initiative_id',
        'title',
        'description',
        'due_date',
        'status',
        'completion_percentage',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completion_percentage' => 'decimal:2',
        ];
    }

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(CultureInitiative::class, 'culture_initiative_id');
    }
}
