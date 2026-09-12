<?php

namespace App\Domains\WorkforceAdmin\Events;

use App\Domains\WorkforceAdmin\Models\OpsDataQualityResult;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HrDataQualityIssueDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OpsDataQualityResult $result
    ) {}
}
