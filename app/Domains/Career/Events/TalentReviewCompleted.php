<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\TalentReviewSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TalentReviewCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TalentReviewSession $session) {}
}
