<?php

namespace App\Domains\Recruitment\Enums;

enum ApplicationStatus: string
{
    case NEW = 'new';
    case SCREENING = 'screening';
    case SHORTLISTED = 'shortlisted';
    case INTERVIEW = 'interview';
    case ASSESSMENT = 'assessment';
    case OFFER = 'offer';
    case HIRED = 'hired';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case ON_HOLD = 'on_hold';
}
