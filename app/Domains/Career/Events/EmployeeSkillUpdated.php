<?php

namespace App\Domains\Career\Events;

use App\Domains\Career\Models\EmployeeSkill;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeSkillUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly EmployeeSkill $employeeSkill) {}
}
