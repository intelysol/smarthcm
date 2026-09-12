<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\AttendancePeriod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendancePeriodLocked
{
    use Dispatchable, SerializesModels;

    public function __construct(public AttendancePeriod $period) {}
}
