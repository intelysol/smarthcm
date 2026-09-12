<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\AttendanceException;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceExceptionRaised
{
    use Dispatchable, SerializesModels;

    public function __construct(public AttendanceException $exception) {}
}
