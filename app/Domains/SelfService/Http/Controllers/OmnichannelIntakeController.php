<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Services\OmnichannelIntakeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OmnichannelIntakeController extends Controller
{
    public function __construct(
        protected OmnichannelIntakeService $intakeService
    ) {}

    public function inboundMessage(string $channel, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_email' => 'nullable|email',
            'sender_phone' => 'nullable|string',
            'sender_name' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'external_id' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        $createdRequest = $this->intakeService->ingestMessage($tenantId, $channel, $validated);

        return response()->json([
            'status' => 'ingested',
            'request_number' => $createdRequest->request_number,
            'request_id' => $createdRequest->id,
        ], 201);
    }
}
