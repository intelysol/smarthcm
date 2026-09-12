<?php

namespace App\Domains\Organization\Services;

use App\Domains\Organization\Models\Position;
use DomainException;

class PositionControlService
{
    public function fill(Position $position, int $quantity = 1, bool $override = false): Position
    {
        if ($quantity < 1 || (! $override && $position->filled_headcount + $quantity > $position->headcount)) throw new DomainException('Position headcount limit exceeded.');
        $position->increment('filled_headcount', $quantity);
        $position->refresh();
        $position->update(['status' => $position->filled_headcount >= $position->headcount ? 'filled' : 'partially_filled', 'version' => $position->version + 1]);
        return $position->refresh();
    }
}
