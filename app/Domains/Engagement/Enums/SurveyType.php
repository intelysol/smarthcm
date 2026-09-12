<?php

namespace App\Domains\Engagement\Enums;

enum SurveyType: string
{
    case Engagement = 'engagement';
    case Pulse = 'pulse';
    case Satisfaction = 'satisfaction';
    case Culture = 'culture';
    case Onboarding = 'onboarding';
    case Exit = 'exit';
    case ManagerFeedback = 'manager_feedback';
    case TrainingFeedback = 'training_feedback';
    case Custom = 'custom';
}
