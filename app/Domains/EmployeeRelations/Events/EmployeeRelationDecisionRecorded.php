<?php

namespace App\Domains\EmployeeRelations\Events;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationDecision;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeRelationDecisionRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EmployeeRelationCase $case,
        public EmployeeRelationDecision $decision
    ) {}
}
