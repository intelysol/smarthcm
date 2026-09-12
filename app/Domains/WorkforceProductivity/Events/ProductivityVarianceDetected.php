<?php

namespace App\Domains\WorkforceProductivity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductivityVarianceDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly array $varianceData
    ) {}
}
