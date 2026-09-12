<?php

namespace App\Domains\WorkforceProductivity\Events;

use App\Domains\WorkforceProductivity\Models\HcmProductivitySnapshot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductivitySnapshotGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly HcmProductivitySnapshot $snapshot
    ) {}
}
