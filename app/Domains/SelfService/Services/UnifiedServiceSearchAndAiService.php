<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;

class UnifiedServiceSearchAndAiService
{
    /**
     * Single-box unified search resolving natural language intent across Knowledge Articles and Service Catalog.
     */
    public function searchIntent(string $tenantId, string $query): array
    {
        $normalized = strtolower(trim($query));

        $stopWords = ['how', 'do', 'i', 'to', 'the', 'a', 'an', 'in', 'on', 'for', 'my', 'of', 'and', 'is', 'what', 'can'];
        $tokens = array_values(array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', ' ', $normalized)), fn($w) => strlen($w) > 2 && !in_array($w, $stopWords)));
        if (empty($tokens)) {
            $tokens = [$normalized];
        }

        // 1. Search Knowledge Articles (Deflection)
        $articles = HrKnowledgeArticle::where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->where(function ($q) use ($normalized, $tokens) {
                $q->where('title', 'LIKE', "%{$normalized}%")
                    ->orWhere('summary', 'LIKE', "%{$normalized}%")
                    ->orWhere('content', 'LIKE', "%{$normalized}%");
                foreach ($tokens as $token) {
                    $q->orWhere('title', 'LIKE', "%{$token}%")
                        ->orWhere('summary', 'LIKE', "%{$token}%")
                        ->orWhere('content', 'LIKE', "%{$token}%");
                }
            })
            ->latest('views_count')
            ->take(4)
            ->get();

        // 2. Search Service Catalog
        $services = HrServiceDefinition::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($q) use ($normalized, $tokens) {
                $q->where('name', 'LIKE', "%{$normalized}%")
                    ->orWhere('description', 'LIKE', "%{$normalized}%")
                    ->orWhere('service_code', 'LIKE', "%{$normalized}%");
                foreach ($tokens as $token) {
                    $q->orWhere('name', 'LIKE', "%{$token}%")
                        ->orWhere('description', 'LIKE', "%{$token}%");
                }
            })
            ->with(['category'])
            ->take(4)
            ->get();

        // 3. Classify Intent
        $intent = 'general_inquiry';
        if (str_contains($normalized, 'bank') || str_contains($normalized, 'account')) {
            $intent = 'bank_details_change';
        } elseif (str_contains($normalized, 'salary') || str_contains($normalized, 'certificate') || str_contains($normalized, 'letter')) {
            $intent = 'document_request';
        } elseif (str_contains($normalized, 'leave') || str_contains($normalized, 'vacation')) {
            $intent = 'leave_inquiry';
        } elseif (str_contains($normalized, 'benefit') || str_contains($normalized, 'insurance')) {
            $intent = 'benefits_inquiry';
        } elseif (str_contains($normalized, 'harass') || str_contains($normalized, 'complaint') || str_contains($normalized, 'concern')) {
            $intent = 'employee_relations_case';
        }

        return [
            'query' => $query,
            'detected_intent' => $intent,
            'knowledge_articles' => $articles,
            'recommended_services' => $services,
            'deflection_suggested' => $articles->isNotEmpty(),
            'ai_assistance' => [
                'is_advisory_only' => true,
                'suggested_next_step' => $articles->isNotEmpty()
                    ? 'Review the recommended knowledge articles before opening a ticket.'
                    : 'Select a service definition to launch your pre-populated request form.',
            ],
        ];
    }

    /**
     * Generate advisory summary and response draft for agents.
     * Guardrail: never autonomously approves or resolves cases.
     */
    public function generateAgentAdvisory(HrServiceRequest $request): array
    {
        return [
            'is_advisory_only' => true,
            'request_number' => $request->request_number,
            'case_summary' => "Employee {$request->employee?->first_name} requested '{$request->subject}' under {$request->service?->name}. Priority: {$request->priority}.",
            'suggested_category' => $request->service?->category?->name ?? 'General HR',
            'suggested_response_draft' => "Dear {$request->employee?->first_name}, thank you for reaching out regarding {$request->subject}. Our HR Shared Services team is reviewing your details.",
            'potential_duplicate_detected' => false,
            'compliance_risk_flag' => $request->confidentiality_level === 'restricted',
        ];
    }
}
