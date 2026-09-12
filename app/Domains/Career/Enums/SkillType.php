<?php

namespace App\Domains\Career\Enums;

enum SkillType: string
{
    case Technical = 'technical';
    case Functional = 'functional';
    case SoftSkill = 'soft_skill';
    case Language = 'language';
    case Tool = 'tool';
    case Domain = 'domain';
    case Leadership = 'leadership';
}
