<?php

namespace App\Domains\Engagement\Enums;

enum SurveyConfidentialityType: string
{
    case Named = 'named';
    case Confidential = 'confidential';
    case Anonymous = 'anonymous';
}
