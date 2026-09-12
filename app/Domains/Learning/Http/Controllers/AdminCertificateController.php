<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Resources\LearningCertificateResource;
use App\Domains\Learning\Services\LearningCertificateService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminCertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.certificate.view'), 403);

        $certificates = LearningCertificate::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->query('employee_id'), fn ($q, $e) => $q->where('employee_id', $e))
            ->when($request->query('course_id'), fn ($q, $c) => $q->where('course_id', $c))
            ->with(['employee', 'course', 'version'])
            ->paginate((int) $request->integer('per_page', 25));

        return LearningCertificateResource::collection($certificates)->response();
    }

    public function verify(Request $request, LearningCertificate $certificate, LearningCertificateService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.certificate.verify'), 403);
        abort_unless($certificate->tenant_id === $request->user()->tenant_id, 404);

        $certificate->update([
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'status' => 'active',
        ]);

        return LearningCertificateResource::make($certificate->fresh(['employee', 'course']))->response();
    }

    public function revoke(Request $request, LearningCertificate $certificate, LearningCertificateService $service): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('hcm.learning.certificate.manage'), 403);
        abort_unless($certificate->tenant_id === $request->user()->tenant_id, 404);

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $revoked = $service->revokeCertificate($request->user(), $certificate, $request->string('reason')->toString());

        return LearningCertificateResource::make($revoked)->response();
    }
}
