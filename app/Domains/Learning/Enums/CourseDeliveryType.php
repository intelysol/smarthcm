<?php

namespace App\Domains\Learning\Enums;

enum CourseDeliveryType: string
{
    case SELF_PACED = 'self_paced';
    case CLASSROOM = 'classroom';
    case VIRTUAL = 'virtual';
    case BLENDED = 'blended';
    case EXTERNAL = 'external';
}
