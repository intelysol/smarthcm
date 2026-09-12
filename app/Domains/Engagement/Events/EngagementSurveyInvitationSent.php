<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EngagementCampaignRecipient;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementSurveyInvitationSent
{
    use Dispatchable, SerializesModels;
    public function __construct(public EngagementCampaignRecipient $recipient) {}
}
