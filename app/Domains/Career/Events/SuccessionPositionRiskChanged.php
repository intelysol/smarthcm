<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\SuccessionPosition;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuccessionPositionRiskChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SuccessionPosition $position) {}
}
