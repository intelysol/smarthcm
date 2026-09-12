<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Contracts\PrivacyPolicyProvider;

class EngagementPrivacyService implements PrivacyPolicyProvider
{
    public function meetsMinimumGroupThreshold(int $responseCount, int $threshold = 5): bool
    {
        return $responseCount >= $threshold;
    }

    public function sanitizeFreeText(string $text): string
    {
        // Strip HTML, trim whitespace, and mask potential emails or phone numbers to preserve respondent anonymity
        $clean = strip_tags(trim($text));
        $clean = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[REDACTED_EMAIL]', $clean);
        $clean = preg_replace('/(\+?[0-9]{1,3}[-.\s]?)?(\(?\d{3}\)?[-.\s]?)?\d{3}[-.\s]?\d{4}/', '[REDACTED_PHONE]', $clean);

        return (string) $clean;
    }

    public function canViewResult(int $count, int $threshold = 5): bool
    {
        return $this->meetsMinimumGroupThreshold($count, $threshold);
    }

    public function suppressIfInsufficient(array $data, int $count, int $threshold = 5): array
    {
        if (! $this->meetsMinimumGroupThreshold($count, $threshold)) {
            return [
                'suppressed' => true,
                'reason' => "Response count ({$count}) is below the minimum privacy threshold of {$threshold}.",
                'response_count' => $count,
                'data' => null,
            ];
        }

        return [
            'suppressed' => false,
            'response_count' => $count,
            'data' => $data,
        ];
    }
}
