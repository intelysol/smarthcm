<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\AttendanceAdjustment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceAdjustmentApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public AttendanceAdjustment $adjustment) {}
}
