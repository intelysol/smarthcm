<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EngagementRecognition;
use App\Domains\Engagement\Requests\RecognitionCreateRequest;
use App\Domains\Engagement\Resources\EngagementRecognitionResource;
use App\Domains\Engagement\Services\EngagementRecognitionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRecognitionController extends Controller
{
    public function __construct(
        protected EngagementRecognitionService $recognitionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $recognitions = EngagementRecognition::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['sender', 'recipient'])
            ->latest()
            ->paginate(30);

        return response()->json($recognitions);
    }

    public function store(RecognitionCreateRequest $request): JsonResponse
    {
        $sender = Employee::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $recipient = Employee::query()->findOrFail($request->input('recipient_employee_id'));

        $recognition = $this->recognitionService->createRecognition(
            $request->user()->tenant_id,
            $sender,
            $recipient,
            $request->validated()
        );

        return response()->json([
            'message' => 'Recognition posted successfully.',
            'recognition' => new EngagementRecognitionResource($recognition),
        ], 201);
    }

    public function moderate(Request $request, EngagementRecognition $recognition): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:published,archived,draft'],
        ]);

        $moderator = Employee::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->first();

        $updated = $this->recognitionService->moderateRecognition(
            $recognition,
            $request->input('status'),
            $moderator
        );

        return response()->json([
            'message' => 'Recognition post status updated.',
            'recognition' => new EngagementRecognitionResource($updated),
        ]);
    }
}
