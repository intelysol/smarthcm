<?php

namespace Tests\Unit\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningQuestion;
use App\Domains\Learning\Models\LearningQuestionOption;
use App\Domains\Learning\Services\LearningAssessmentScoringService;
use App\Domains\Learning\Services\LearningAssessmentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningAssessmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_scoring_engine_evaluates_single_and_multiple_choice(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $assessment = LearningAssessment::query()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Security Knowledge Check',
            'passing_percentage' => 75.0,
        ]);

        // Q1: Single choice (points: 10)
        $q1 = LearningQuestion::query()->create([
            'assessment_id' => $assessment->id,
            'question_type' => 'single_choice',
            'question_text' => 'What is 2FA?',
            'points' => 10,
        ]);
        $q1Opt1 = LearningQuestionOption::query()->create(['question_id' => $q1->id, 'option_text' => 'Two-Factor Authentication', 'is_correct' => true]);
        $q1Opt2 = LearningQuestionOption::query()->create(['question_id' => $q1->id, 'option_text' => 'Two Fast Animals', 'is_correct' => false]);

        // Q2: True/False (points: 10)
        $q2 = LearningQuestion::query()->create([
            'assessment_id' => $assessment->id,
            'question_type' => 'true_false',
            'question_text' => 'Passwords should never be shared.',
            'points' => 10,
        ]);
        $q2OptTrue = LearningQuestionOption::query()->create(['question_id' => $q2->id, 'option_text' => 'True', 'is_correct' => true]);
        $q2OptFalse = LearningQuestionOption::query()->create(['question_id' => $q2->id, 'option_text' => 'False', 'is_correct' => false]);

        $scoring = app(LearningAssessmentScoringService::class);

        // Test Full Pass (100%)
        $result = $scoring->gradeAssessment($assessment, [
            $q1->id => $q1Opt1->id,
            $q2->id => $q2OptTrue->id,
        ]);

        $this->assertEquals(20.0, $result['score_obtained']);
        $this->assertEquals(100.0, $result['score_percentage']);
        $this->assertTrue($result['passed']);

        // Test Partial Fail (50% < 75%)
        $resultFail = $scoring->gradeAssessment($assessment, [
            $q1->id => $q1Opt1->id,
            $q2->id => $q2OptFalse->id,
        ]);

        $this->assertEquals(10.0, $resultFail['score_obtained']);
        $this->assertEquals(50.0, $resultFail['score_percentage']);
        $this->assertFalse($resultFail['passed']);
    }

    public function test_attempt_submission_records_answers_and_grades_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-010',
            'employee_code' => 'EMP-010',
            'first_name' => 'Usman',
            'last_name' => 'Tariq',
            'joining_date' => now()->toDateString(),
        ]);

        $assessment = LearningAssessment::query()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Workplace Safety Exam',
            'passing_percentage' => 70.0,
            'max_attempts' => 2,
        ]);

        $q = LearningQuestion::query()->create([
            'assessment_id' => $assessment->id,
            'question_type' => 'single_choice',
            'question_text' => 'Where is emergency exit?',
            'points' => 10,
        ]);
        $optCorrect = LearningQuestionOption::query()->create(['question_id' => $q->id, 'option_text' => 'Follow Green Signs', 'is_correct' => true]);

        $service = app(LearningAssessmentService::class);

        $attempt = $service->startAttempt($user, $employee, $assessment);
        $this->assertEquals(1, $attempt->attempt_number);
        $this->assertEquals('in_progress', $attempt->status);

        $submitted = $service->submitAttempt($user, $attempt, [
            $q->id => $optCorrect->id,
        ]);

        $this->assertEquals('graded', $submitted->status);
        $this->assertTrue($submitted->passed);
        $this->assertEquals(100.0, (float) $submitted->score_percentage);
    }
}
