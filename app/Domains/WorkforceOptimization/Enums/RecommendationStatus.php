<?php

namespace App\Domains\WorkforceOptimization\Enums;

enum RecommendationStatus: string
{
    case DETECTED = 'DETECTED';
    case ANALYZING = 'ANALYZING';
    case GENERATED = 'GENERATED';
    case REVIEW = 'REVIEW';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case EXECUTING = 'EXECUTING';
    case COMPLETED = 'COMPLETED';
    case MEASURED = 'MEASURED';
    case EXPIRED = 'EXPIRED';
}
