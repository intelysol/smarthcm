<?php

namespace App\Domains\Career\Enums;

enum SkillSource: string
{
    case Employee = 'employee';
    case Manager = 'manager';
    case Assessment = 'assessment';
    case Performance = 'performance';
    case Learning = 'learning';
    case Certification = 'certification';
    case Import = 'import';
    case System = 'system';
}
