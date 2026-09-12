<?php

namespace App\Domains\Engagement\Enums;

enum QuestionCategory: string
{
    case Leadership = 'leadership';
    case Management = 'management';
    case Communication = 'communication';
    case Recognition = 'recognition';
    case Growth = 'growth';
    case Workload = 'workload';
    case Wellbeing = 'wellbeing';
    case Culture = 'culture';
    case Inclusion = 'inclusion';
    case Trust = 'trust';
    case Compensation = 'compensation';
    case Learning = 'learning';
    case Career = 'career';
    case Technology = 'technology';
    case WorkEnvironment = 'work_environment';
}
