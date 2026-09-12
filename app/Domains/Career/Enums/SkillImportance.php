<?php

namespace App\Domains\Career\Enums;

enum SkillImportance: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}
