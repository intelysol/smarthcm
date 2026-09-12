<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EngagementCampaign;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementCampaignStarted
{
    use Dispatchable, SerializesModels;
    public function __construct(public EngagementCampaign $campaign) {}
}
