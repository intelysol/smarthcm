<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\RosterPeriod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RosterPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public RosterPeriod $period) {}
}
