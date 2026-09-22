<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class StructuredJsonFormatter extends NormalizerFormatter
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'secret',
        'token',
        'api_key',
        'apikey',
        'authorization',
        'access_token',
        'refresh_token',
        'private_key',
        'credit_card',
        'cvv',
        'ssn',
    ];

    public function format(LogRecord $record): string
    {
        $normalized = $this->normalizeRecord($record);
        $maskedContext = $this->maskSensitiveData($normalized['context'] ?? []);
        $maskedExtra = $this->maskSensitiveData($normalized['extra'] ?? []);

        $logData = [
            'timestamp' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'channel' => $record->channel,
            'request_id' => $maskedContext['request_id'] ?? $maskedExtra['request_id'] ?? null,
            'tenant_id' => $maskedContext['tenant_id'] ?? $maskedExtra['tenant_id'] ?? null,
            'user_id' => $maskedContext['user_id'] ?? $maskedExtra['user_id'] ?? null,
            'context' => $maskedContext,
            'extra' => $maskedExtra,
        ];

        return json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private function maskSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);
            foreach (self::SENSITIVE_KEYS as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $data[$key] = '***REDACTED***';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            }
        }

        return $data;
    }
}
