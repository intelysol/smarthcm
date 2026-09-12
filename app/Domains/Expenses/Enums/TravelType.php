<?php

namespace App\Domains\Expenses\Enums;

enum TravelType: string
{
    case DOMESTIC = 'domestic';
    case INTERNATIONAL = 'international';
    case CLIENT_VISIT = 'client_visit';
    case TRAINING = 'training';
    case CONFERENCE = 'conference';
    case BUSINESS_DEVELOPMENT = 'business_development';
    case PROJECT = 'project';
    case EMERGENCY = 'emergency';
    case OTHER = 'other';
}
