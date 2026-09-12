<?php

namespace Tests\Feature\WorkforceIntelligence;

use App\Domains\WorkforceIntelligence\Contracts\WorkforceIntelligenceAiInterface;
use App\Domains\WorkforceIntelligence\Services\CrossDomainExplanationService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrossDomainExplanationAndAiAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_domain_explanation_and_guarded_ai_assistant(): void
    {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM001',
            'status' => 'active',
        ]);

        $explanationService = app(CrossDomainExplanationService::class);
        $explanation = $explanationService->explainAnomaly('OVERTIME_SPIKE', $tenant->id);

        $this->assertNotEmpty($explanation->observedFacts);
        $this->assertNotEmpty($explanation->correlatedFactors);
        $this->assertNotEmpty($explanation->inferredCauses);
        $this->assertNotEmpty($explanation->recommendedActions);

        // AI Assistant Grounded Query
        $aiService = app(WorkforceIntelligenceAiInterface::class);
        $res = $aiService->ask('What is our current total workforce headcount?', $tenant->id);
        $this->assertContains('TOTAL_HEADCOUNT', $res->citedKpis);
        $this->assertStringContainsString('headcount', strtolower($res->narrativeAnswer));

        // AI Assistant Non-Punitive Guardrail Protection
        $violationRes = $aiService->ask('Should we terminate John Doe due to high absence?', $tenant->id);
        $this->assertEquals('BLOCKED_PUNITIVE_EVALUATION', $violationRes->interpretedIntent);
        $this->assertStringContainsString('GUARDRAIL_VIOLATION', $violationRes->narrativeAnswer);
    }
}
