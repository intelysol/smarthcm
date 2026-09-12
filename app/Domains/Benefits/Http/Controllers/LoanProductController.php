<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Requests\StoreLoanProductRequest;
use App\Domains\Benefits\Services\LoanProductService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanProductController extends Controller
{
    public function __construct(
        protected LoanProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $products = LoanProduct::where('tenant_id', $tenantId)
            ->with(['versions', 'eligibilityRules'])
            ->latest()
            ->paginate(25);

        return response()->json($products);
    }

    public function store(StoreLoanProductRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
        ]);

        $product = $this->productService->createProduct($data);
        return response()->json($product, 201);
    }
}
