<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\TalentPool;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TalentPoolCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TalentPool $pool) {}
}
