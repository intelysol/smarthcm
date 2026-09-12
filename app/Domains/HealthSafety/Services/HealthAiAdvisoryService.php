<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;

class HealthAiAdvisoryService
{
    /**
     * Analyze incident description to suggest category, severity, OSHA recordability, and CAPA.
     * STRICT ARCHITECTURAL RULE: All outputs are advisory-only (is_advisory => true).
     * Cannot autonomously make legal/medical determinations.
     */
    public function analyzeIncident(string $description, ?string $incidentType = null): array
    {
        $lower = strtolower($description);

        // Heuristic advisory detection
        $suggestedSeverity = 'minor';
        $suggestedOsha = false;
        $suggestedCategory = $incidentType ?? 'hazard_unsafe_condition';
        $riskFactors = [];
        $recommendedCapa = [];

        if (str_contains($lower, 'fracture') || str_contains($lower, 'broken') || str_contains($lower, 'amput') || str_contains($lower, 'hospital')) {
            $suggestedSeverity = 'critical';
            $suggestedOsha = true;
            $riskFactors[] = 'Severe acute trauma detected';
            $recommendedCapa[] = [
                'type' => 'corrective',
                'hierarchy' => 'engineering_controls',
                'action' => 'Implement physical machine guard / interlock boundary to eliminate pinch point',
            ];
        } elseif (str_contains($lower, 'blood') || str_contains($lower, 'cut') || str_contains($lower, 'laceration') || str_contains($lower, 'suture') || str_contains($lower, 'stitch')) {
            $suggestedSeverity = 'moderate';
            $suggestedOsha = true;
            $riskFactors[] = 'Open wound requiring medical treatment';
            $recommendedCapa[] = [
                'type' => 'corrective',
                'hierarchy' => 'ppe',
                'action' => 'Mandate ANSI Cut-Level A4 or higher cut-resistant gloves for handling sharp materials',
            ];
        } elseif (str_contains($lower, 'spill') || str_contains($lower, 'chemical') || str_contains($lower, 'fume') || str_contains($lower, 'inhalation')) {
            $suggestedCategory = 'exposure';
            $suggestedSeverity = 'moderate';
            $riskFactors[] = 'Hazardous material or airborne contaminant release';
            $recommendedCapa[] = [
                'type' => 'corrective',
                'hierarchy' => 'engineering_controls',
                'action' => 'Inspect local exhaust ventilation and verify containment seals',
            ];
        } elseif (str_contains($lower, 'slip') || str_contains($lower, 'trip') || str_contains($lower, 'fall') || str_contains($lower, 'wet floor')) {
            $suggestedCategory = 'near_miss';
            $suggestedSeverity = 'minor';
            $riskFactors[] = 'Walking-working surface irregularity';
            $recommendedCapa[] = [
                'type' => 'preventive',
                'hierarchy' => 'housekeeping',
                'action' => 'Apply high-traction floor coating and establish immediate wet-floor sign deployment protocol',
            ];
        }

        // Always include an administrative root-cause action
        $recommendedCapa[] = [
            'type' => 'preventive',
            'hierarchy' => 'administrative_controls',
            'action' => 'Conduct toolbox talk refresher with affected team and review standard operating procedure',
        ];

        return [
            'is_advisory' => true,
            'disclaimer' => 'AI-generated recommendations are assistive and advisory only. All formal occupational safety and medical fitness decisions require qualified human EHS and medical practitioner review.',
            'suggested_category' => $suggestedCategory,
            'suggested_severity' => $suggestedSeverity,
            'suggested_osha_recordable' => $suggestedOsha,
            'identified_risk_factors' => $riskFactors,
            'recommended_root_cause_methods' => [
                '5-Why Root Cause Analysis',
                'Ishikawa / Fishbone Diagram',
                'Bow-Tie Risk Model',
            ],
            'suggested_actions' => $recommendedCapa,
        ];
    }

    /**
     * Provide advisory ergonomic or return-to-work suggestions based on operational limitations.
     */
    public function adviseAccommodations(string $restrictionDescription): array
    {
        $lower = strtolower($restrictionDescription);
        $suggestions = [];

        if (str_contains($lower, 'lift') || str_contains($lower, 'weight') || str_contains($lower, 'lbs') || str_contains($lower, 'kg')) {
            $suggestions[] = [
                'type' => 'equipment',
                'title' => 'Hydraulic Scissor Lift Table & Material Cart',
                'description' => 'Provide mechanical lifting aid to prevent manual handling above designated weight threshold.',
            ];
            $suggestions[] = [
                'type' => 'job_restructuring',
                'title' => 'Buddy Lifting & Two-Person Lift Protocol',
                'description' => 'Reassign individual heavy lifts to team lifts or divide cargo containers.',
            ];
        }

        if (str_contains($lower, 'stand') || str_contains($lower, 'prolonged standing')) {
            $suggestions[] = [
                'type' => 'ergonomic_aid',
                'title' => 'Anti-Fatigue Matting & Sit-Stand Stool',
                'description' => 'Install cushioned anti-fatigue floor mat and sit-stand perch stool at workstation.',
            ];
        }

        if (str_contains($lower, 'screen') || str_contains($lower, 'eye') || str_contains($lower, 'vision') || str_contains($lower, 'light')) {
            $suggestions[] = [
                'type' => 'workplace_modification',
                'title' => 'Anti-Glare Display Filters & Task Lighting',
                'description' => 'Adjust workstation illumination and introduce 20-20-20 micro-break schedule.',
            ];
        }

        if (empty($suggestions)) {
            $suggestions[] = [
                'type' => 'schedule_adjustment',
                'title' => 'Flexible Breaks & Phased Work Schedule',
                'description' => 'Permit additional self-paced micro-breaks during shift to prevent cumulative fatigue.',
            ];
        }

        return [
            'is_advisory' => true,
            'disclaimer' => 'Workplace accommodation advisory is based solely on non-clinical operational job limitations.',
            'recommended_accommodations' => $suggestions,
        ];
    }
}
