<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\LoanProduct;
use App\Domains\Benefits\Models\LoanProductVersion;
use Illuminate\Support\Facades\DB;

class LoanProductService
{
    public function createProduct(array $data): LoanProduct
    {
        return DB::transaction(function () use ($data) {
            $product = LoanProduct::create($data);

            $product->versions()->create([
                'tenant_id' => $product->tenant_id,
                'version_number' => 1,
                'effective_from' => now()->toDateString(),
                'interest_rate_annual' => $product->interest_rate_annual,
                'interest_method' => $product->interest_method,
                'maximum_amount' => $product->maximum_amount,
                'is_active' => true,
            ]);

            return $product->fresh(['versions', 'eligibilityRules']);
        });
    }

    public function createProductVersion(LoanProduct $product, array $data): LoanProductVersion
    {
        return DB::transaction(function () use ($product, $data) {
            $newVersionNumber = $product->versions()->max('version_number') + 1;

            $product->versions()->update(['is_active' => false]);

            $version = $product->versions()->create(array_merge($data, [
                'tenant_id' => $product->tenant_id,
                'version_number' => $newVersionNumber,
                'is_active' => true,
            ]));

            $product->update([
                'version' => $newVersionNumber,
                'interest_rate_annual' => $data['interest_rate_annual'] ?? $product->interest_rate_annual,
                'interest_method' => $data['interest_method'] ?? $product->interest_method,
                'maximum_amount' => $data['maximum_amount'] ?? $product->maximum_amount,
            ]);

            return $version;
        });
    }
}
