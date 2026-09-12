<?php

namespace App\Domains\SelfService\Events;

use App\Domains\SelfService\Models\HrAnnouncement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public HrAnnouncement $announcement) {}
}
