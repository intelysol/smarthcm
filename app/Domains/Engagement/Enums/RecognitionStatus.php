<?php

namespace App\Domains\Engagement\Enums;

enum RecognitionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Moderation = 'moderation';
    case Published = 'published';
    case Archived = 'archived';
}
