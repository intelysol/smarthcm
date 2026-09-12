<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseReceipt;

class ReceiptService
{
    public function attachReceipt(Employee $employee, array $receiptData, ?string $claimLineId = null): ExpenseReceipt
    {
        $fileContent = $receiptData['content'] ?? ($receiptData['file_name'] . ($receiptData['file_size'] ?? 0));
        $hash = hash('sha256', $fileContent);

        return ExpenseReceipt::create([
            'tenant_id' => $employee->tenant_id,
            'expense_claim_line_id' => $claimLineId,
            'employee_id' => $employee->id,
            'file_path' => $receiptData['file_path'] ?? ('receipts/' . uniqid() . '.pdf'),
            'file_name' => $receiptData['file_name'] ?? 'receipt.pdf',
            'file_type' => $receiptData['file_type'] ?? 'application/pdf',
            'file_size' => $receiptData['file_size'] ?? 1024,
            'receipt_hash' => $hash,
            'merchant_extracted' => $receiptData['merchant_extracted'] ?? null,
            'invoice_number' => $receiptData['invoice_number'] ?? null,
            'extracted_amount' => $receiptData['extracted_amount'] ?? null,
            'extracted_currency' => $receiptData['extracted_currency'] ?? null,
            'extracted_tax' => $receiptData['extracted_tax'] ?? null,
            'ocr_payload' => $receiptData['ocr_payload'] ?? null,
            'is_verified_by_employee' => $receiptData['is_verified_by_employee'] ?? true,
            'status' => 'attached',
        ]);
    }

    public function isDuplicateReceipt(string $tenantId, string $receiptHash, ?string $currentReceiptId = null): bool
    {
        $query = ExpenseReceipt::query()
            ->where('tenant_id', $tenantId)
            ->where('receipt_hash', $receiptHash);

        if ($currentReceiptId) {
            $query->where('id', '!=', $currentReceiptId);
        }

        return $query->exists();
    }
}
