<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementResponseAnswer;
use App\Domains\Engagement\Models\EngagementTextAnalysis;

class EngagementTextAnalysisService
{
    public function analyzeCampaignComments(EngagementCampaign $campaign): array
    {
        $answers = EngagementResponseAnswer::query()
            ->join('engagement_responses', 'engagement_response_answers.response_id', '=', 'engagement_responses.id')
            ->where('engagement_responses.campaign_id', $campaign->id)
            ->where('engagement_responses.response_status', 'submitted')
            ->whereNotNull('engagement_response_answers.text_value')
            ->where('engagement_response_answers.text_value', '!=', '')
            ->select('engagement_response_answers.*')
            ->get();

        if ($answers->isEmpty()) {
            return [];
        }

        $positiveKeywords = ['great', 'good', 'excellent', 'love', 'support', 'happy', 'appreciate', 'helpful', 'transparent', 'growth', 'collaborative'];
        $negativeKeywords = ['bad', 'poor', 'slow', 'frustrat', 'lack', 'unfair', 'confusing', 'overwork', 'burnout', 'difficult', 'micromanag'];

        $topicBuckets = [
            'Leadership & Direction' => ['leader', 'manager', 'exec', 'vision', 'direction', 'strategy'],
            'Work-Life Balance & Wellbeing' => ['workload', 'stress', 'hours', 'balance', 'flexible', 'overtime', 'remote'],
            'Career & Growth' => ['growth', 'career', 'promotion', 'learning', 'training', 'opportunity'],
            'Culture & Collaboration' => ['culture', 'team', 'respect', 'communication', 'transparent', 'value'],
            'Tools & Resources' => ['tool', 'system', 'software', 'resource', 'laptop', 'equipment'],
        ];

        $results = [];

        foreach ($topicBuckets as $topicName => $keywords) {
            $matchingComments = $answers->filter(function ($a) use ($keywords) {
                $text = strtolower((string) $a->text_value);
                foreach ($keywords as $kw) {
                    if (str_contains($text, $kw)) {
                        return true;
                    }
                }
                return false;
            });

            if ($matchingComments->isNotEmpty()) {
                $posCount = 0;
                $negCount = 0;

                foreach ($matchingComments as $mc) {
                    $txt = strtolower((string) $mc->text_value);
                    foreach ($positiveKeywords as $pkw) {
                        if (str_contains($txt, $pkw)) {
                            $posCount++;
                        }
                    }
                    foreach ($negativeKeywords as $nkw) {
                        if (str_contains($txt, $nkw)) {
                            $negCount++;
                        }
                    }
                }

                $sentiment = 'neutral';
                if ($posCount > $negCount) {
                    $sentiment = 'positive';
                } elseif ($negCount > $posCount) {
                    $sentiment = 'negative';
                }

                $confidence = round(min(1.0, ($posCount + $negCount + 1) / ($matchingComments->count() * 2 + 1)), 4);

                $record = EngagementTextAnalysis::query()->create([
                    'tenant_id' => $campaign->tenant_id,
                    'campaign_id' => $campaign->id,
                    'question_id' => null,
                    'topic' => $topicName,
                    'sentiment' => $sentiment,
                    'confidence' => $confidence,
                    'sample_count' => $matchingComments->count(),
                    'created_at' => now(),
                ]);

                $results[] = [
                    'topic' => $topicName,
                    'sentiment' => $sentiment,
                    'confidence' => $confidence,
                    'sample_count' => $matchingComments->count(),
                ];
            }
        }

        return $results;
    }
}
