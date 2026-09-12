<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'venue_type', 'name', 'location', 'capacity', 'meeting_url_reference', 'meeting_provider', 'status'])]
class LearningVenue extends LearningModel
{
    /** @return HasMany<LearningSession> */
    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'venue_id');
    }
}
