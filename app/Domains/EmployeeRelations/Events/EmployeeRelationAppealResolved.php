<?php

namespace App\Domains\EmployeeRelations\Events;

use App\Domains\EmployeeRelations\Models\EmployeeRelationAppeal;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeRelationAppealResolved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EmployeeRelationCase $case,
        public EmployeeRelationAppeal $appeal
    ) {}
}
