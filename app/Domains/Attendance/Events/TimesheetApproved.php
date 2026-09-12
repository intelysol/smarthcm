<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\Timesheet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimesheetApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public Timesheet $timesheet) {}
}
