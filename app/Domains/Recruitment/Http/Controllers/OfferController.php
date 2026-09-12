<?php

namespace App\Domains\Recruitment\Http\Controllers;

use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Recruitment\Services\OfferManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(protected OfferManagementService $offerService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|uuid',
            'base_salary' => 'required|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'employment_type' => 'nullable|string|max:30',
            'benefits_summary' => 'nullable|array',
        ]);

        $application = HcmRecruitmentApplication::findOrFail($validated['application_id']);
        $offer = $this->offerService->createOffer($application, $validated, $request->user()->id);

        return response()->json($offer, 201);
    }

    public function show(string $id): JsonResponse
    {
        $offer = HcmRecruitmentOffer::with(['versions', 'approvals', 'application.candidate'])->findOrFail($id);
        return response()->json($offer);
    }

    public function updateVersion(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'base_salary' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'change_rationale' => 'required|string|max:255',
        ]);

        $offer = HcmRecruitmentOffer::findOrFail($id);
        $updated = $this->offerService->createNewOfferVersion(
            $offer,
            $validated,
            $validated['change_rationale'],
            $request->user()->id
        );

        return response()->json($updated);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $offer = HcmRecruitmentOffer::findOrFail($id);
        $approved = $this->offerService->approveOffer(
            $offer,
            $request->user()->id,
            $request->input('role', 'finance'),
            $request->input('comments')
        );

        return response()->json($approved);
    }

    public function send(string $id): JsonResponse
    {
        $offer = HcmRecruitmentOffer::findOrFail($id);
        $sent = $this->offerService->sendOfferToCandidate($offer);
        return response()->json($sent);
    }

    public function accept(string $id): JsonResponse
    {
        $offer = HcmRecruitmentOffer::findOrFail($id);
        $accepted = $this->offerService->acceptOffer($offer);
        return response()->json($accepted);
    }
}
