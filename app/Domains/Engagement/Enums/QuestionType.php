<?php

namespace App\Domains\Engagement\Enums;

enum QuestionType: string
{
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case Rating = 'rating';
    case Likert = 'likert';
    case Nps = 'nps';
    case Text = 'text';
    case LongText = 'long_text';
    case Numeric = 'numeric';
    case YesNo = 'yes_no';
    case Date = 'date';
}
