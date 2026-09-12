<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Services\PersonnelActionReversalService;
use App\Domains\Lifecycle\Services\PersonnelActionSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionReversalController extends Controller
{
    public function __construct(
        protected PersonnelActionReversalService $reversalService,
        protected PersonnelActionSecurityService $securityService
    ) {
    }

    public function reverse(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $action = PersonnelActionRequest::with('changes')->findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $action);

        $reversal = $this->reversalService->reverseExecutedAction(
            $action,
            $request->user(),
            $request->input('reason')
        );

        return response()->json($reversal, 201);
    }
}
