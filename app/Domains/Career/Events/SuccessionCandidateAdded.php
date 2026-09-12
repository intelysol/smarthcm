<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\SuccessionCandidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuccessionCandidateAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SuccessionCandidate $candidate) {}
}
