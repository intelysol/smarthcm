<?php

namespace App\Domains\Onboarding\Enums;

enum OnboardingCaseStatus: string
{
    case DRAFT = 'draft';
    case PREBOARDING = 'preboarding';
    case READY = 'ready';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
