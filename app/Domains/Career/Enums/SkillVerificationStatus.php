<?php

namespace App\Domains\Career\Enums;

enum SkillVerificationStatus: string
{
    case Unverified = 'unverified';
    case SelfDeclared = 'self_declared';
    case ManagerVerified = 'manager_verified';
    case AssessmentVerified = 'assessment_verified';
    case SystemVerified = 'system_verified';
}
