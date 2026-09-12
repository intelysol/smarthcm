<?php

namespace App\Domains\Engagement\Contracts;

interface PrivacyPolicyProvider
{
    public function meetsMinimumGroupThreshold(int $responseCount, int $threshold = 5): bool;
    public function sanitizeFreeText(string $text): string;
}
