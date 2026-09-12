<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrAnnouncement;
use App\Domains\SelfService\Requests\CreateAnnouncementRequest;
use App\Domains\SelfService\Services\AnnouncementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        protected AnnouncementService $announcementService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();

        if ($employee) {
            $announcements = $this->announcementService->getVisibleAnnouncementsForEmployee($employee);
        } else {
            $announcements = HrAnnouncement::where('tenant_id', $user->tenant_id)->orderBy('published_at', 'desc')->get();
        }

        if ($request->wantsJson()) {
            return response()->json($announcements);
        }

        return view('self-service.announcements.index', compact('announcements'));
    }

    public function store(CreateAnnouncementRequest $request): JsonResponse
    {
        $user = $request->user();
        $announcement = $this->announcementService->createAnnouncement(
            $request->validated(),
            $user,
            $request->input('audiences', [])
        );

        return response()->json([
            'message' => 'Announcement published successfully.',
            'data' => $announcement,
        ], 201);
    }

    public function acknowledge(Request $request, HrAnnouncement $announcement): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('tenant_id', $user->tenant_id)->firstOrFail();

        $ack = $this->announcementService->acknowledgeAnnouncement($announcement, $employee, $request->ip());

        return response()->json([
            'message' => 'Announcement acknowledged.',
            'data' => $ack,
        ]);
    }
}
