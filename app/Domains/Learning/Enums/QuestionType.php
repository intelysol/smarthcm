<?php

namespace App\Domains\Learning\Enums;

enum QuestionType: string
{
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case SHORT_ANSWER = 'short_answer';
    case NUMERIC = 'numeric';
    case RATING = 'rating';
}
