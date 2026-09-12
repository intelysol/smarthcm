<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\AttendanceSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceSessionProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(public AttendanceSession $session) {}
}
