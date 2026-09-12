<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Services\PersonnelActionImpactService;
use App\Domains\Lifecycle\Services\PersonnelActionSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionImpactController extends Controller
{
    public function __construct(
        protected PersonnelActionImpactService $impactService,
        protected PersonnelActionSecurityService $securityService
    ) {
    }

    public function analyze(Request $request, string $id): JsonResponse
    {
        $action = PersonnelActionRequest::with('changes')->findOrFail($id);
        $this->securityService->authorizeRequestAccess($request->user(), $action);

        $impacts = $this->impactService->analyzeImpact($action);
        return response()->json($impacts);
    }
}
