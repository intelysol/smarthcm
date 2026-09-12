<?php

namespace App\Domains\Learning\Enums;

enum ContentType: string
{
    case VIDEO = 'video';
    case DOCUMENT = 'document';
    case PRESENTATION = 'presentation';
    case AUDIO = 'audio';
    case ARTICLE = 'article';
    case EXTERNAL_LINK = 'external_link';
    case SCORM = 'SCORM';
    case INTERACTIVE = 'interactive';
}
