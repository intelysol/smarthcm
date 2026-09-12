<?php

namespace App\Domains\Employee\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeDocument;
use App\Domains\Employee\Services\EmployeeTimelineService;
use App\Domains\Employee\Support\EmployeeSectionRegistry;
use App\Domains\Shared\Services\ActivityLogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeSectionController extends Controller
{
    public function index(Request $request, EmployeeSectionRegistry $registry, string $employee, string $section): JsonResponse
    {
        $user = $request->user();
        $definition = $registry->get($section);
        abort_if($user === null || ! $user->hasPermission($definition->permission), 403);
        $record = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($employee);

        return response()->json($definition->modelClass::query()->where('employee_id', $record->id)->latest()->get());
    }

    public function store(Request $request, EmployeeSectionRegistry $registry, EmployeeTimelineService $timeline, ActivityLogService $activityLog, string $employee, string $section): JsonResponse
    {
        $user = $request->user();
        $definition = $registry->get($section);
        abort_if($user === null || ! $user->hasPermission($definition->permission), 403);

        $employeeRecord = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($employee);
        $validated = $request->validate($definition->rules);
        $record = DB::transaction(function () use ($definition, $validated, $employeeRecord, $user, $timeline, $activityLog, $section) {
            $attributes = $this->prepareAttributes($definition->key, $validated, $employeeRecord, $user->id);
            $record = $definition->modelClass::query()->create(array_merge($attributes, [
                'tenant_id' => $employeeRecord->tenant_id,
                'employee_id' => $employeeRecord->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));

            $timeline->record($employeeRecord, $section.'_added', str($section)->replace('-', ' ')->title().' added', null, $record->attributesToArray(), $user->id);
            $activityLog->record('created', $record, $user->id, null, $record->attributesToArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(Request $request, EmployeeSectionRegistry $registry, EmployeeTimelineService $timeline, ActivityLogService $activityLog, string $employee, string $section, string $id): JsonResponse
    {
        $user = $request->user();
        $definition = $registry->get($section);
        abort_if($user === null || ! $user->hasPermission($definition->permission), 403);
        $employeeRecord = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($employee);
        $record = $definition->modelClass::query()->where('employee_id', $employeeRecord->id)->findOrFail($id);
        $validated = $request->validate($this->partialRules($definition->rules));

        $record = DB::transaction(function () use ($definition, $validated, $employeeRecord, $record, $user, $timeline, $activityLog, $section) {
            $oldValues = $record->attributesToArray();
            $record->fill($this->prepareAttributes($definition->key, $validated, $employeeRecord, $user->id));
            $record->updated_by = $user->id;
            $record->save();
            $timeline->record($employeeRecord, $section.'_updated', str($section)->replace('-', ' ')->title().' updated', $oldValues, $record->attributesToArray(), $user->id);
            $activityLog->record('updated', $record, $user->id, $oldValues, $record->attributesToArray());

            return $record->refresh();
        });

        return response()->json(['data' => $record]);
    }

    public function destroy(Request $request, EmployeeSectionRegistry $registry, EmployeeTimelineService $timeline, ActivityLogService $activityLog, string $employee, string $section, string $id): JsonResponse
    {
        $user = $request->user();
        $definition = $registry->get($section);
        abort_if($user === null || ! $user->hasPermission($definition->permission), 403);
        $employeeRecord = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($employee);
        $record = $definition->modelClass::query()->where('employee_id', $employeeRecord->id)->findOrFail($id);
        DB::transaction(function () use ($record, $employeeRecord, $section, $timeline, $activityLog, $user): void {
            $oldValues = $record->attributesToArray();
            $record->forceFill(['deleted_by' => $user->id])->save();
            $record->delete();
            $timeline->record($employeeRecord, $section.'_deleted', str($section)->replace('-', ' ')->title().' deleted', $oldValues, null, $user->id);
            $activityLog->record('deleted', $record, $user->id, $oldValues, null);
        });

        return response()->json(['message' => 'Employee section record deleted successfully.']);
    }

    /** @param array<string, mixed> $attributes */
    private function prepareAttributes(string $section, array $attributes, Employee $employee, int $actorId): array
    {
        if ($section === 'bank-accounts' && ($attributes['is_primary'] ?? false)) {
            $employee->bankAccounts()->update(['is_primary' => false, 'updated_by' => $actorId]);
        }

        if ($section === 'documents' && ! isset($attributes['version'])) {
            $attributes['version'] = (int) EmployeeDocument::withTrashed()
                ->where('employee_id', $employee->id)
                ->where('document_type', $attributes['document_type'])
                ->where('title', $attributes['title'])
                ->max('version') + 1;
        }

        return $attributes;
    }

    /** @param array<string, array<int, mixed>> $rules */
    private function partialRules(array $rules): array
    {
        foreach ($rules as $field => $fieldRules) {
            array_unshift($fieldRules, 'sometimes');
            $rules[$field] = $fieldRules;
        }

        return $rules;
    }
}
