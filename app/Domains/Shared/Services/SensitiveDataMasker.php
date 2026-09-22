<?php

declare(strict_types=1);

namespace App\Domains\Shared\Services;

class SensitiveDataMasker
{
    /**
     * Mask a bank account or IBAN, preserving only the last 4 digits.
     * Example: "1234567890" -> "**** **** 7890"
     */
    public function maskBankAccount(?string $accountNumber): string
    {
        if (empty($accountNumber)) {
            return '';
        }

        $clean = preg_replace('/\s+/', '', $accountNumber);
        $len = strlen($clean);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        $lastFour = substr($clean, -4);
        return '**** **** ' . $lastFour;
    }

    /**
     * Mask a government identifier (SSN, National ID, CNIC).
     * Example: "123-45-6789" -> "***-**-6789"
     */
    public function maskGovernmentId(?string $id): string
    {
        if (empty($id)) {
            return '';
        }

        $len = strlen($id);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        $visibleCount = min(4, $len - 1);
        $maskedCount = $len - $visibleCount;

        return str_repeat('*', $maskedCount) . substr($id, -$visibleCount);
    }

    /**
     * Mask an API key or secret token.
     * Example: "sk_live_1234567890abcdef" -> "sk_live_...cdef"
     */
    public function maskApiKey(?string $key): string
    {
        if (empty($key)) {
            return '';
        }

        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        $prefix = substr($key, 0, min(8, (int) floor($len / 4)));
        $suffix = substr($key, -4);

        return $prefix . '...' . $suffix;
    }

    /**
     * Mask an email address.
     * Example: "john.doe@company.com" -> "j***e@company.com"
     */
    public function maskEmail(?string $email): string
    {
        if (empty($email) || ! str_contains($email, '@')) {
            return '***@***.***';
        }

        [$name, $domain] = explode('@', $email, 2);
        $nameLen = strlen($name);

        if ($nameLen <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', $nameLen - 2) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    /**
     * Mask compensation / salary for non-authorized viewers.
     */
    public function maskSalary(float|int|string|null $salary): string
    {
        return '*****';
    }

    /**
     * Recursively mask sensitive fields in arrays/payloads.
     */
    public function maskArray(array $data): array
    {
        foreach ($data as $key => $val) {
            $keyStr = strtolower((string) $key);
            if (is_array($val)) {
                $data[$key] = $this->maskArray($val);
            } elseif (is_string($val)) {
                if (str_contains($keyStr, 'account') || str_contains($keyStr, 'iban')) {
                    $data[$key] = $this->maskBankAccount($val);
                } elseif (str_contains($keyStr, 'ssn') || str_contains($keyStr, 'national_id') || str_contains($keyStr, 'tax_id')) {
                    $data[$key] = $this->maskGovernmentId($val);
                } elseif (str_contains($keyStr, 'key') || str_contains($keyStr, 'token') || str_contains($keyStr, 'secret')) {
                    $data[$key] = $this->maskApiKey($val);
                }
            }
        }

        return $data;
    }
}
