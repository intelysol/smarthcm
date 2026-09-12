<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\CultureInitiative;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CultureInitiativeCompleted
{
    use Dispatchable, SerializesModels;
    public function __construct(public CultureInitiative $initiative) {}
}
