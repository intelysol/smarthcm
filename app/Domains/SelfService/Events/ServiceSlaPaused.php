<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceSlaPaused
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request,
        public string $reason
    ) {}
}
