<?php

namespace App\Domains\Employee\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeCustomFieldDefinition;
use App\Domains\Employee\Models\EmployeeCustomFieldValue;
use App\Domains\Employee\Requests\EmployeeCustomFieldRequest;
use App\Domains\Shared\Services\ActivityLogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeCustomFieldController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.view'), 403);

        return response()->json(['data' => EmployeeCustomFieldDefinition::query()
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('label')
            ->get()]);
    }

    public function store(EmployeeCustomFieldRequest $request, ActivityLogService $activityLog): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $definition = EmployeeCustomFieldDefinition::query()->create(array_merge($request->validated(), [
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]));
        $activityLog->record('created', $definition, $user->id, null, $definition->attributesToArray());

        return response()->json(['data' => $definition], 201);
    }

    public function update(EmployeeCustomFieldRequest $request, ActivityLogService $activityLog, string $definition): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $record = EmployeeCustomFieldDefinition::query()->where('tenant_id', $user->tenant_id)->findOrFail($definition);
        $oldValues = $record->attributesToArray();
        $record->fill($request->validated());
        $record->updated_by = $user->id;
        $record->save();
        $activityLog->record('updated', $record, $user->id, $oldValues, $record->attributesToArray());

        return response()->json(['data' => $record]);
    }

    public function destroy(Request $request, ActivityLogService $activityLog, string $definition): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.update'), 403);
        $record = EmployeeCustomFieldDefinition::query()->where('tenant_id', $user->tenant_id)->findOrFail($definition);
        $oldValues = $record->attributesToArray();
        $record->forceFill(['deleted_by' => $user->id])->save();
        $record->delete();
        $activityLog->record('deleted', $record, $user->id, $oldValues, null);

        return response()->json(['message' => 'Custom field deleted successfully.']);
    }

    public function setValue(Request $request, ActivityLogService $activityLog, string $employee, string $definition): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.update'), 403);
        $request->validate(['value' => ['nullable']]);
        $employeeRecord = Employee::query()->where('tenant_id', $user->tenant_id)->findOrFail($employee);
        $definitionRecord = EmployeeCustomFieldDefinition::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->findOrFail($definition);

        $record = DB::transaction(function () use ($employeeRecord, $definitionRecord, $request, $user, $activityLog) {
            $record = EmployeeCustomFieldValue::withTrashed()->firstOrNew([
                'employee_id' => $employeeRecord->id,
                'field_definition_id' => $definitionRecord->id,
            ]);
            $oldValues = $record->exists ? $record->attributesToArray() : null;
            if ($record->trashed()) {
                $record->restore();
            }
            $record->fill([
                'tenant_id' => $employeeRecord->tenant_id,
                'value' => $this->normalizeValue($definitionRecord->field_type, $request->input('value')),
                'created_by' => $record->created_by ?? $user->id,
                'updated_by' => $user->id,
                'deleted_by' => null,
            ]);
            $record->save();
            $activityLog->record($oldValues === null ? 'created' : 'updated', $record, $user->id, $oldValues, $record->attributesToArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }

    private function normalizeValue(string $fieldType, mixed $value): array
    {
        if ($fieldType === 'multi_select') {
            abort_unless(is_array($value), 422, 'This field requires an array value.');

            return array_values($value);
        }

        if ($fieldType === 'boolean') {
            return [(bool) $value];
        }

        return [$value];
    }
}
