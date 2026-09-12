<?php

namespace App\Domains\Engagement\Enums;

enum QuestionDimension: string
{
    case Leadership = 'leadership';
    case Trust = 'trust';
    case Recognition = 'recognition';
    case Growth = 'growth';
    case Communication = 'communication';
    case Culture = 'culture';
    case Engagement = 'engagement';
    case Wellbeing = 'wellbeing';
    case Inclusion = 'inclusion';
    case Alignment = 'alignment';
}
