<?php

namespace App\Domains\Employee\Http\Controllers;

use App\Domains\Employee\Actions\CreateEmployeeAction;
use App\Domains\Employee\Actions\UpdateEmployeeAction;
use App\Domains\Employee\DTOs\EmployeeData;
use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Requests\EmployeeRequest;
use App\Domains\Employee\Resources\EmployeeResource;
use App\Domains\Employee\Services\EmployeeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(Request $request, EmployeeService $employees): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.view'), 403);
        Gate::authorize('viewAny', Employee::class);

        return response()->json(EmployeeResource::collection(
            $employees->list((string) $user->tenant_id, $request->query()),
        )->response()->getData(true));
    }

    public function store(EmployeeRequest $request, CreateEmployeeAction $action): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);
        Gate::authorize('create', Employee::class);

        return EmployeeResource::make(
            $action->execute(EmployeeData::fromArray($request->validated(), (string) $user->tenant_id, $user->id)),
        )->response()->setStatusCode(201);
    }

    public function show(Request $request, EmployeeService $employees, string $employee): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.view'), 403);

        $record = $employees->find((string) $user->tenant_id, $employee);
        Gate::authorize('view', $record);

        return EmployeeResource::make($record)->response();
    }

    public function update(EmployeeRequest $request, EmployeeService $employees, UpdateEmployeeAction $action, string $employee): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $record = $employees->find((string) $user->tenant_id, $employee);
        Gate::authorize('update', $record);

        return EmployeeResource::make(
            $action->execute($record, EmployeeData::fromArray($request->validated(), (string) $user->tenant_id, $user->id)),
        )->response();
    }

    public function destroy(Request $request, EmployeeService $employees, string $employee): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null || ! $user->hasPermission('employee.delete'), 403);

        $record = $employees->find((string) $user->tenant_id, $employee);
        Gate::authorize('delete', $record);
        $employees->delete($record, $user->id);

        return response()->json(['message' => 'Employee deleted successfully.']);
    }
}
