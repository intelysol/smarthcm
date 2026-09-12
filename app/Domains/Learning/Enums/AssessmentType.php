<?php

namespace App\Domains\Learning\Enums;

enum AssessmentType: string
{
    case QUIZ = 'quiz';
    case EXAM = 'exam';
    case KNOWLEDGE_CHECK = 'knowledge_check';
    case PRE_TEST = 'pre_test';
    case POST_TEST = 'post_test';
}
