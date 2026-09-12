<?php

namespace Tests\Unit\Engagement;

use App\Domains\Engagement\Services\EngagementPrivacyService;
use Tests\TestCase;

class EngagementPrivacyServiceTest extends TestCase
{
    public function test_minimum_group_threshold_suppression(): void
    {
        $privacy = new EngagementPrivacyService();

        // Sample size < 5 -> Suppressed
        $this->assertFalse($privacy->meetsMinimumGroupThreshold(4, 5));
        $this->assertFalse($privacy->canViewResult(3, 5));

        $suppressedOutput = $privacy->suppressIfInsufficient(['score' => 4.5], 4, 5);
        $this->assertTrue($suppressedOutput['suppressed']);
        $this->assertNull($suppressedOutput['data']);

        // Sample size >= 5 -> Allowed
        $this->assertTrue($privacy->meetsMinimumGroupThreshold(5, 5));
        $this->assertTrue($privacy->meetsMinimumGroupThreshold(12, 5));

        $allowedOutput = $privacy->suppressIfInsufficient(['score' => 4.5], 5, 5);
        $this->assertFalse($allowedOutput['suppressed']);
        $this->assertEquals(['score' => 4.5], $allowedOutput['data']);
    }

    public function test_free_text_sanitization_removes_personal_identifiers(): void
    {
        $privacy = new EngagementPrivacyService();

        $rawText = "Please contact me at john.doe@example.com or +1 555-123-4567 for details <script>alert(1)</script>.";
        $clean = $privacy->sanitizeFreeText($rawText);

        $this->assertStringNotContainsString('john.doe@example.com', $clean);
        $this->assertStringNotContainsString('+1 555-123-4567', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('[REDACTED_EMAIL]', $clean);
        $this->assertStringContainsString('[REDACTED_PHONE]', $clean);
    }
}
