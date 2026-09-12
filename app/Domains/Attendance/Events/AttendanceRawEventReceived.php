<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\AttendanceRawEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRawEventReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public AttendanceRawEvent $rawEvent) {}
}
