<?php

namespace App\Domains\Employee\Services;

use App\Domains\Employee\DTOs\EmployeeData;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Repositories\EmployeeRepository;
use App\Domains\Shared\Services\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeeTimelineService $timelines,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function list(string $tenantId, array $filters): LengthAwarePaginator
    {
        return $this->employees->paginate($tenantId, $filters);
    }

    public function find(string $tenantId, string $id): Employee
    {
        return $this->employees->findForTenant($tenantId, $id);
    }

    public function create(EmployeeData $data): Employee
    {
        return DB::transaction(function () use ($data): Employee {
            $this->ensureUnique($data->tenantId, $data->attributes);

            $employee = $this->employees->create(
                $data->toCreateArray($this->employees->nextEmployeeNumber($data->tenantId)),
            );

            $this->timelines->record($employee, 'created', 'Employee profile created', null, $employee->attributesToArray(), $data->actorId);
            $this->timelines->record($employee, 'joined', 'Employee joined', null, ['joining_date' => $employee->joining_date], $data->actorId);
            $this->activityLog->record('created', $employee, $data->actorId, null, $employee->attributesToArray());

            return $employee;
        });
    }

    public function update(Employee $employee, EmployeeData $data): Employee
    {
        return DB::transaction(function () use ($employee, $data): Employee {
            $this->ensureUnique($data->tenantId, $data->attributes, $employee->id);
            $oldValues = $employee->getOriginal();
            $record = $this->employees->update($employee, $data->toArray());

            foreach (['department_id' => 'department_changed', 'reporting_manager_id' => 'manager_changed', 'employment_status' => 'status_changed'] as $field => $event) {
                if (($oldValues[$field] ?? null) !== $record->{$field}) {
                    $this->timelines->record($record, $event, str($event)->replace('_', ' ')->title(), [$field => $oldValues[$field] ?? null], [$field => $record->{$field}], $data->actorId);
                }
            }

            $this->activityLog->record('updated', $record, $data->actorId, $oldValues, $record->attributesToArray());

            return $record;
        });
    }

    public function delete(Employee $employee, int $actorId): bool
    {
        $oldValues = $employee->attributesToArray();
        $employee->forceFill(['deleted_by' => $actorId])->save();
        $deleted = (bool) $employee->delete();
        $this->activityLog->record('deleted', $employee, $actorId, $oldValues, null);

        return $deleted;
    }

    private function ensureUnique(string $tenantId, array $attributes, ?string $ignoreId = null): void
    {
        foreach (['employee_code', 'national_id', 'passport_number', 'official_email'] as $column) {
            if (($attributes[$column] ?? null) === null) {
                continue;
            }

            $exists = Employee::query()
                ->where('tenant_id', $tenantId)
                ->where($column, $attributes[$column])
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([$column => "The {$column} has already been used."]);
            }
        }
    }
}
