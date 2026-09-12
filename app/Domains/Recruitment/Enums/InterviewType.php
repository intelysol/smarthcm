<?php

namespace App\Domains\Recruitment\Enums;

enum InterviewType: string
{
    case PHONE = 'phone';
    case VIDEO = 'video';
    case TECHNICAL = 'technical';
    case PANEL = 'panel';
    case MANAGER = 'manager';
    case HR = 'hr';
    case FINAL = 'final';
}
