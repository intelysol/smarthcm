<?php

namespace App\Domains\Benefits\Enums;

enum EnrollmentType: string
{
    case OPEN_ENROLLMENT = 'open_enrollment';
    case NEW_HIRE = 'new_hire';
    case LIFE_EVENT = 'life_event';
    case ADMIN_OVERRIDE = 'admin_override';
}
