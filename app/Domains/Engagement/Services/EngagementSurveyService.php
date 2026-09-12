<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Events\EngagementSurveyClosed;
use App\Domains\Engagement\Events\EngagementSurveyCreated;
use App\Domains\Engagement\Events\EngagementSurveyPublished;
use App\Domains\Engagement\Models\EngagementQuestionBank;
use App\Domains\Engagement\Models\EngagementQuestionRule;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyQuestion;
use App\Domains\Engagement\Models\EngagementSurveySection;
use App\Domains\Engagement\Models\EngagementSurveyTemplate;
use App\Domains\Engagement\Models\EngagementSurveyVersion;
use App\Domains\Events\Services\EventBus;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class EngagementSurveyService
{
    public function __construct(
        protected AuditService $audit,
        protected EventBus $events
    ) {}

    public function createSurvey(string $tenantId, array $data, ?int $userId = null): EngagementSurvey
    {
        return DB::transaction(function () use ($tenantId, $data, $userId) {
            $survey = EngagementSurvey::query()->create([
                'tenant_id' => $tenantId,
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'survey_type' => $data['survey_type'] ?? 'engagement',
                'instructions' => $data['instructions'] ?? null,
                'confidentiality_type' => $data['confidentiality_type'] ?? 'anonymous',
                'allow_multiple_responses' => $data['allow_multiple_responses'] ?? false,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'status' => 'draft',
                'version' => 1,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            EngagementSurveyCreated::dispatch($survey);

            $this->audit->record(
                $tenantId,
                'EngagementSurveyCreated',
                'create_survey',
                EngagementSurvey::class,
                (string) $survey->id,
                $userId,
                null,
                ['code' => $survey->code, 'title' => $survey->title]
            );

            return $survey;
        });
    }

    public function updateSurvey(EngagementSurvey $survey, array $data, ?int $userId = null): EngagementSurvey
    {
        return DB::transaction(function () use ($survey, $data, $userId) {
            $before = $survey->toArray();
            $survey->update([
                'title' => $data['title'] ?? $survey->title,
                'description' => $data['description'] ?? $survey->description,
                'survey_type' => $data['survey_type'] ?? $survey->survey_type,
                'instructions' => $data['instructions'] ?? $survey->instructions,
                'confidentiality_type' => $data['confidentiality_type'] ?? $survey->confidentiality_type,
                'allow_multiple_responses' => $data['allow_multiple_responses'] ?? $survey->allow_multiple_responses,
                'start_date' => $data['start_date'] ?? $survey->start_date,
                'end_date' => $data['end_date'] ?? $survey->end_date,
                'updated_by' => $userId,
            ]);

            $this->audit->record(
                (string) $survey->tenant_id,
                'EngagementSurveyUpdated',
                'update_survey',
                EngagementSurvey::class,
                (string) $survey->id,
                $userId,
                $before,
                $survey->fresh()->toArray()
            );

            return $survey->fresh();
        });
    }

    public function publishSurvey(EngagementSurvey $survey, ?int $userId = null): EngagementSurveyVersion
    {
        return DB::transaction(function () use ($survey, $userId) {
            $survey->load(['sections.questions', 'questions', 'rules']);

            $snapshot = [
                'survey' => $survey->only(['id', 'code', 'title', 'survey_type', 'confidentiality_type', 'instructions']),
                'sections' => $survey->sections->map(fn ($s) => $s->only(['id', 'title', 'description', 'sort_order']))->toArray(),
                'questions' => $survey->questions->map(fn ($q) => $q->only([
                    'id', 'section_id', 'question', 'description', 'question_type',
                    'category', 'dimension', 'scale_config', 'options', 'is_required', 'sort_order',
                ]))->toArray(),
                'rules' => $survey->rules->map(fn ($r) => $r->only([
                    'id', 'source_question_id', 'target_question_id', 'operator', 'value', 'action',
                ]))->toArray(),
            ];

            $version = EngagementSurveyVersion::query()->create([
                'tenant_id' => $survey->tenant_id,
                'survey_id' => $survey->id,
                'version_number' => $survey->version,
                'snapshot' => $snapshot,
                'published_at' => now(),
                'published_by' => $userId,
            ]);

            $survey->update([
                'status' => 'open',
            ]);

            EngagementSurveyPublished::dispatch($survey);

            $this->audit->record(
                (string) $survey->tenant_id,
                'EngagementSurveyPublished',
                'publish_survey',
                EngagementSurvey::class,
                (string) $survey->id,
                $userId,
                null,
                ['version_id' => $version->id, 'version_number' => $version->version_number]
            );

            return $version;
        });
    }

    public function closeSurvey(EngagementSurvey $survey, ?int $userId = null): EngagementSurvey
    {
        return DB::transaction(function () use ($survey, $userId) {
            $survey->update(['status' => 'closed']);

            EngagementSurveyClosed::dispatch($survey);

            $this->audit->record(
                (string) $survey->tenant_id,
                'EngagementSurveyClosed',
                'close_survey',
                EngagementSurvey::class,
                (string) $survey->id,
                $userId,
                null,
                ['status' => 'closed']
            );

            return $survey->fresh();
        });
    }

    public function addSection(
        EngagementSurvey $survey,
        string $title,
        ?string $description = null,
        int $sortOrder = 0
    ): EngagementSurveySection {
        return EngagementSurveySection::query()->create([
            'tenant_id' => $survey->tenant_id,
            'survey_id' => $survey->id,
            'title' => $title,
            'description' => $description,
            'sort_order' => $sortOrder,
        ]);
    }

    public function addQuestion(EngagementSurvey $survey, array $data): EngagementSurveyQuestion
    {
        return EngagementSurveyQuestion::query()->create([
            'tenant_id' => $survey->tenant_id,
            'survey_id' => $survey->id,
            'section_id' => $data['section_id'] ?? null,
            'bank_question_id' => $data['bank_question_id'] ?? null,
            'question' => $data['question'],
            'description' => $data['description'] ?? null,
            'question_type' => $data['question_type'],
            'category' => $data['category'] ?? 'engagement',
            'dimension' => $data['dimension'] ?? 'engagement',
            'scale_config' => $data['scale_config'] ?? null,
            'options' => $data['options'] ?? null,
            'is_required' => $data['is_required'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function addRule(
        EngagementSurvey $survey,
        string $sourceQuestionId,
        string $targetQuestionId,
        string $operator,
        string $value,
        string $action = 'show'
    ): EngagementQuestionRule {
        return EngagementQuestionRule::query()->create([
            'tenant_id' => $survey->tenant_id,
            'survey_id' => $survey->id,
            'source_question_id' => $sourceQuestionId,
            'target_question_id' => $targetQuestionId,
            'operator' => $operator,
            'value' => $value,
            'action' => $action,
        ]);
    }

    public function cloneSurvey(
        EngagementSurvey $sourceSurvey,
        string $newCode,
        string $newTitle,
        ?int $userId = null
    ): EngagementSurvey {
        return DB::transaction(function () use ($sourceSurvey, $newCode, $newTitle, $userId) {
            $cloned = EngagementSurvey::query()->create([
                'tenant_id' => $sourceSurvey->tenant_id,
                'code' => $newCode,
                'title' => $newTitle,
                'description' => $sourceSurvey->description,
                'survey_type' => $sourceSurvey->survey_type,
                'instructions' => $sourceSurvey->instructions,
                'confidentiality_type' => $sourceSurvey->confidentiality_type,
                'allow_multiple_responses' => $sourceSurvey->allow_multiple_responses,
                'status' => 'draft',
                'version' => 1,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $sectionMap = [];
            foreach ($sourceSurvey->sections as $sec) {
                $newSec = $this->addSection($cloned, $sec->title, $sec->description, $sec->sort_order);
                $sectionMap[$sec->id] = $newSec->id;
            }

            $questionMap = [];
            foreach ($sourceSurvey->questions as $q) {
                $newQ = $this->addQuestion($cloned, [
                    'section_id' => $q->section_id ? ($sectionMap[$q->section_id] ?? null) : null,
                    'bank_question_id' => $q->bank_question_id,
                    'question' => $q->question,
                    'description' => $q->description,
                    'question_type' => $q->question_type,
                    'category' => $q->category,
                    'dimension' => $q->dimension,
                    'scale_config' => $q->scale_config,
                    'options' => $q->options,
                    'is_required' => $q->is_required,
                    'sort_order' => $q->sort_order,
                ]);
                $questionMap[$q->id] = $newQ->id;
            }

            foreach ($sourceSurvey->rules as $r) {
                if (isset($questionMap[$r->source_question_id], $questionMap[$r->target_question_id])) {
                    $this->addRule(
                        $cloned,
                        $questionMap[$r->source_question_id],
                        $questionMap[$r->target_question_id],
                        $r->operator,
                        $r->value,
                        $r->action
                    );
                }
            }

            return $cloned;
        });
    }

    public function createFromTemplate(
        EngagementSurveyTemplate $template,
        string $tenantId,
        string $code,
        string $title,
        ?int $userId = null
    ): EngagementSurvey {
        return DB::transaction(function () use ($template, $tenantId, $code, $title, $userId) {
            $survey = $this->createSurvey($tenantId, [
                'code' => $code,
                'title' => $title,
                'description' => $template->description,
                'survey_type' => $template->survey_type,
            ], $userId);

            $structure = $template->structure;
            if (! empty($structure['questions']) && is_array($structure['questions'])) {
                foreach ($structure['questions'] as $idx => $qData) {
                    $this->addQuestion($survey, array_merge($qData, ['sort_order' => $idx + 1]));
                }
            }

            return $survey;
        });
    }
}
