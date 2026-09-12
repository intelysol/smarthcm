<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceException;
use App\Domains\Attendance\Services\AttendanceExceptionEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceExceptionController extends Controller
{
    public function __construct(
        protected AttendanceExceptionEngine $exceptionEngine
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = AttendanceException::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['employee', 'session']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('exception_type')) {
            $query->where('exception_type', $request->query('exception_type'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        $exceptions = $query->latest('exception_date')->paginate(30);

        return response()->json($exceptions);
    }

    public function resolve(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'resolution_type' => ['required', 'string', 'in:justified,waived,penalized,adjusted,ignored'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $exception = AttendanceException::query()->where('tenant_id', $user->tenant_id)->findOrFail($id);

        $resolved = $this->exceptionEngine->resolveException(
            $exception,
            $request->input('resolution_type'),
            $request->input('notes'),
            $user->id
        );

        return response()->json([
            'message' => 'Attendance exception resolved.',
            'data' => $resolved,
        ]);
    }
}
