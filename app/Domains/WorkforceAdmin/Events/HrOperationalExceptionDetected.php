<?php

namespace App\Domains\WorkforceAdmin\Events;

use App\Domains\WorkforceAdmin\Models\OpsException;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HrOperationalExceptionDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OpsException $exception
    ) {}
}
