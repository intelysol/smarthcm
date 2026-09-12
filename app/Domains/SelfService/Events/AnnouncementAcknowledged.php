<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrAnnouncementAcknowledgement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementAcknowledged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public HrAnnouncementAcknowledgement $acknowledgement) {}
}
