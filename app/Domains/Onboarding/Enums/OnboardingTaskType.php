<?php

namespace App\Domains\Onboarding\Enums;

enum OnboardingTaskType: string
{
    case DOCUMENT = 'document';
    case TRAINING = 'training';
    case FORM = 'form';
    case IT_PROVISIONING = 'it_provisioning';
    case EQUIPMENT = 'equipment';
    case MANAGER_CHECK = 'manager_check';
    case POLICY = 'policy';
    case BUDDY_MEET = 'buddy_meet';
    case GENERAL = 'general';
}
