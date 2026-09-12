<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\AttendanceEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceEventNormalized
{
    use Dispatchable, SerializesModels;

    public function __construct(public AttendanceEvent $event) {}
}
