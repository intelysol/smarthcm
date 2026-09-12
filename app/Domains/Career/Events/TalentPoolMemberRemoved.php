<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\TalentPoolMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TalentPoolMemberRemoved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TalentPoolMember $member) {}
}
