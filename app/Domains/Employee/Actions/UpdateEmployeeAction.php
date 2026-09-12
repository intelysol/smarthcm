<?php

namespace App\Domains\Employee\Actions;

use App\Domains\Employee\DTOs\EmployeeData;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Services\EmployeeService;
use App\Domains\Shared\Actions\BaseAction;

class UpdateEmployeeAction extends BaseAction
{
    public function __construct(private readonly EmployeeService $employees) {}

    public function execute(Employee $employee, EmployeeData $data): Employee
    {
        return $this->employees->update($employee, $data);
    }
}
