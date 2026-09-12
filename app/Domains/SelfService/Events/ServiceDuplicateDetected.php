<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ServiceDuplicateDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request,
        public Collection $potentialDuplicates
    ) {}
}
