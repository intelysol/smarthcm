<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrServiceRequestDocument;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestDocumentAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public HrServiceRequestDocument $document) {}
}
