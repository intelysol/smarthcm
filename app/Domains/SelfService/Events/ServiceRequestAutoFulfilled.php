<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceGeneratedDocument;
use App\Domains\SelfService\Models\HrServiceRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestAutoFulfilled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request,
        public HrServiceGeneratedDocument $document
    ) {}
}
