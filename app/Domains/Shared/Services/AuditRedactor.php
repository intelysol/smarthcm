<?php

namespace App\Domains\Shared\Services;

class AuditRedactor
{
    /** @param array<string,mixed>|null $data @return array<string,mixed>|null */
    public function redact(?array $data): ?array
    {
        if ($data === null) return null;
        foreach ($data as $key => $value) { if (preg_match('/password|token|secret|key|iban|account|national_id/i', (string) $key)) $data[$key] = '[REDACTED]'; elseif (is_array($value)) $data[$key] = $this->redact($value); }
        return $data;
    }
}
