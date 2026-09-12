<?php

namespace App\Domains\Learning\Services;

use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningQuestion;
use App\Domains\Learning\Models\LearningQuestionOption;

class LearningAssessmentScoringService
{
    /**
     * Score submitted answers against questions and calculate total score.
     *
     * @param array<string, mixed> $submittedAnswers [question_id => answer_value]
     * @return array{
     *   total_points: float,
     *   score_obtained: float,
     *   score_percentage: float,
     *   passed: bool,
     *   graded_answers: list<array{
     *     question_id: string,
     *     is_correct: bool,
     *     points_awarded: float,
     *     selected_option_id: ?string,
     *     selected_option_ids: ?array,
     *     text_answer: ?string,
     *     numeric_answer: ?float
     *   }>
     * }
     */
    public function gradeAssessment(LearningAssessment $assessment, array $submittedAnswers): array
    {
        $questions = $assessment->questions()->with('options')->get();
        $totalPossiblePoints = 0.0;
        $totalObtainedPoints = 0.0;
        $gradedAnswers = [];

        foreach ($questions as $question) {
            $qPoints = (float) $question->points;
            $totalPossiblePoints += $qPoints;
            $userAnswer = $submittedAnswers[$question->id] ?? null;

            $result = $this->evaluateQuestion($question, $userAnswer);

            if ($result['is_correct']) {
                $totalObtainedPoints += $qPoints;
                $result['points_awarded'] = $qPoints;
            } else {
                $result['points_awarded'] = 0.0;
            }

            $result['question_id'] = $question->id;
            $gradedAnswers[] = $result;
        }

        $percentage = $totalPossiblePoints > 0
            ? round(($totalObtainedPoints / $totalPossiblePoints) * 100, 2)
            : 100.0;

        $passed = $percentage >= (float) $assessment->passing_percentage;

        return [
            'total_points' => $totalPossiblePoints,
            'score_obtained' => $totalObtainedPoints,
            'score_percentage' => $percentage,
            'passed' => $passed,
            'graded_answers' => $gradedAnswers,
        ];
    }

    /**
     * @return array{
     *   is_correct: bool,
     *   points_awarded: float,
     *   selected_option_id: ?string,
     *   selected_option_ids: ?array,
     *   text_answer: ?string,
     *   numeric_answer: ?float
     * }
     */
    private function evaluateQuestion(LearningQuestion $question, mixed $userAnswer): array
    {
        $selectedOptionId = null;
        $selectedOptionIds = null;
        $textAnswer = null;
        $numericAnswer = null;
        $isCorrect = false;

        switch ($question->question_type) {
            case 'single_choice':
            case 'true_false':
                $selectedOptionId = is_string($userAnswer) ? $userAnswer : null;
                if ($selectedOptionId) {
                    $option = $question->options->firstWhere('id', $selectedOptionId);
                    $isCorrect = $option ? (bool) $option->is_correct : false;
                }
                break;

            case 'multiple_choice':
                $selectedOptionIds = is_array($userAnswer) ? array_values($userAnswer) : [];
                $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->values()->all();
                sort($selectedOptionIds);
                sort($correctOptionIds);
                $isCorrect = ($selectedOptionIds === $correctOptionIds);
                break;

            case 'numeric':
                $numericAnswer = is_numeric($userAnswer) ? (float) $userAnswer : null;
                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($numericAnswer !== null && $correctOption) {
                    $expected = (float) $correctOption->option_text;
                    $isCorrect = abs($numericAnswer - $expected) < 0.0001;
                }
                break;

            case 'short_answer':
                $textAnswer = is_string($userAnswer) ? trim($userAnswer) : '';
                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($correctOption) {
                    $isCorrect = strcasecmp($textAnswer, trim($correctOption->option_text)) === 0;
                }
                break;

            case 'rating':
                $numericAnswer = is_numeric($userAnswer) ? (float) $userAnswer : null;
                $isCorrect = true; // Feedback / rating is always credited
                break;
        }

        return [
            'is_correct' => $isCorrect,
            'points_awarded' => 0.0,
            'selected_option_id' => $selectedOptionId,
            'selected_option_ids' => $selectedOptionIds,
            'text_answer' => $textAnswer,
            'numeric_answer' => $numericAnswer,
        ];
    }
}
