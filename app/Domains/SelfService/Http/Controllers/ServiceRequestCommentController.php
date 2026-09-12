<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Requests\AddRequestCommentRequest;
use App\Domains\SelfService\Services\RequestCommunicationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ServiceRequestCommentController extends Controller
{
    public function __construct(
        protected RequestCommunicationService $communicationService
    ) {}

    public function store(AddRequestCommentRequest $request, HrServiceRequest $hrServiceRequest): JsonResponse
    {
        $user = $request->user();
        $message = $request->input('message');
        $type = $request->input('comment_type', 'public');
        $attachments = $request->input('attachments');

        $comment = $this->communicationService->addComment($hrServiceRequest, $user, $message, $type, $attachments);

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => $comment,
        ], 201);
    }
}
