<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Learning\Services\LearningPublicVerificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function __construct(protected LearningPublicVerificationService $verificationService)
    {
    }

    public function verifyApi(string $code): JsonResponse
    {
        $result = $this->verificationService->verifyByCode($code);
        $status = $result['valid'] ? 200 : 404;
        return response()->json($result, $status);
    }

    public function verifyWeb(string $code): View
    {
        $result = $this->verificationService->verifyByCode($code);
        return view('learning.employee.certificate_verify', compact('result', 'code'));
    }
}
