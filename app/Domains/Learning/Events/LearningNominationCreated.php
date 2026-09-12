<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningNomination;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningNominationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningNomination $nomination) {}
}
