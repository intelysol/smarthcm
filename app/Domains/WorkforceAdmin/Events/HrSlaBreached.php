<?php

namespace App\Domains\WorkforceAdmin\Events;

use App\Domains\WorkforceAdmin\Models\OpsSlaInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HrSlaBreached
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OpsSlaInstance $slaInstance
    ) {}
}
