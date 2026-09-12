<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningQuestion;
use App\Domains\Learning\Models\LearningQuestionOption;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningAssessmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_assessment_start_hides_correct_answers_and_grades_submission(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-200',
            'employee_code' => 'EMP-200',
            'first_name' => 'Daniyal',
            'last_name' => 'Sheikh',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-EXAM-01',
            'title' => 'Data Privacy & GDPR',
            'delivery_type' => 'self_paced',
            'duration' => 2,
            'passing_score' => 70.0,
        ]);

        $assessment = LearningAssessment::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'title' => 'GDPR Final Exam',
            'passing_percentage' => 70.0,
            'max_attempts' => 3,
        ]);

        $q1 = LearningQuestion::query()->create([
            'assessment_id' => $assessment->id,
            'question_type' => 'single_choice',
            'question_text' => 'What is personal data under GDPR?',
            'points' => 10,
        ]);
        $q1Correct = LearningQuestionOption::query()->create(['question_id' => $q1->id, 'option_text' => 'Any info relating to identifiable person', 'is_correct' => true]);
        $q1Wrong = LearningQuestionOption::query()->create(['question_id' => $q1->id, 'option_text' => 'Only corporate tax records', 'is_correct' => false]);

        $enrollment = LearningEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'in_progress',
        ]);

        // 1. Start Attempt
        $startResponse = $this->actingAs($user)->postJson("/api/v1/hcm/me/learning/assessments/{$assessment->id}/attempts?enrollment_id={$enrollment->id}");
        $startResponse->assertStatus(201);

        $attemptId = $startResponse->json('data.attempt.id');
        $returnedQuestions = $startResponse->json('data.questions');

        // Verify Anti-Tamper: is_correct is NOT returned to student
        $this->assertArrayNotHasKey('is_correct', $returnedQuestions[0]['options'][0]);

        // 2. Submit Attempt
        $submitResponse = $this->actingAs($user)->postJson("/api/v1/hcm/me/learning/attempts/{$attemptId}/submit", [
            'answers' => [
                $q1->id => $q1Correct->id,
            ]
        ]);

        $submitResponse->assertStatus(200);
        $this->assertTrue($submitResponse->json('data.passed'));
        $this->assertEquals(100.0, $submitResponse->json('data.score_percentage'));

        $this->assertDatabaseHas('learning_assessment_attempts', [
            'id' => $attemptId,
            'status' => 'graded',
            'passed' => true,
        ]);
    }
}
