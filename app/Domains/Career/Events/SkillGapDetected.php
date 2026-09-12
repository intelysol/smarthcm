<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\CareerSkillGap;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SkillGapDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CareerSkillGap $gap) {}
}
