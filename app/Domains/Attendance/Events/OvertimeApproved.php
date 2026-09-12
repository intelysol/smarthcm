<?php

namespace App\Domains\Attendance\Events;

use App\Domains\Attendance\Models\OvertimeRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OvertimeApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public OvertimeRequest $overtimeRequest) {}
}
